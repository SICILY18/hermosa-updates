<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * Get all payments with optional filtering
     */
    public function index(Request $request)
    {
        try {
            $paymentsResult = $this->supabase->getPayments();
            
            if (!$paymentsResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch payments'
                ], 500);
            }

            $payments = $paymentsResult['data'];

            // Apply filtering if needed
            if ($request->has('accountType') && $request->accountType !== 'All') {
                $payments = array_filter($payments, function($payment) use ($request) {
                    return strtolower($payment['customer_type'] ?? '') === strtolower($request->accountType);
                });
            }

            return response()->json([
                'success' => true,
                'data' => array_values($payments)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching payments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payments'
            ], 500);
        }
    }

    /**
     * Approve a payment
     */
    public function approve($id)
    {
        try {
            // Get payment details
            $paymentResult = $this->supabase->getById(config('supabase.tables.payments'), $id);

            if (!$paymentResult['success'] || !$paymentResult['data']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            $payment = $paymentResult['data'];

            // Update payment status
            $updateResult = $this->supabase->update(
                config('supabase.tables.payments'),
                [
                    'status' => 'Approved',
                    'approved_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString()
                ],
                [
                    ['column' => 'id', 'operator' => 'eq', 'value' => $id]
                ]
            );

            if (!$updateResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve payment'
                ], 500);
            }

            // If this was a partial payment, update the bill's remaining balance
            if ($payment['payment_type'] === 'Partial') {
                $billUpdateResult = $this->supabase->update(
                    config('supabase.tables.bills'),
                    [
                        'remaining_balance' => $payment['remaining_balance'],
                        'status' => $payment['remaining_balance'] <= 0 ? 'Paid' : 'Partial',
                        'updated_at' => now()->toISOString()
                    ],
                    [
                        ['column' => 'id', 'operator' => 'eq', 'value' => $payment['bill_id']]
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment approved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving payment'
            ], 500);
        }
    }

    /**
     * Store a new payment from mobile app
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'customer_id' => 'required|integer',
            'bill_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'payment_type' => 'required|in:Full,Partial',
            'payment_method' => 'required|string',
            'account_number' => 'required|string',
            'meter_number' => 'required|string',
            'proof_of_payment' => 'required|image|max:2048', // Max 2MB
        ]);

        try {
            // Verify customer details using query method
            $customerResult = $this->supabase->query(
                config('supabase.tables.customers'),
                '*',
                [
                    ['column' => 'id', 'operator' => 'eq', 'value' => $validated['customer_id']],
                    ['column' => 'account_number', 'operator' => 'eq', 'value' => $validated['account_number']],
                    ['column' => 'meter_number', 'operator' => 'eq', 'value' => $validated['meter_number']]
                ]
            );

            if (!$customerResult['success'] || empty($customerResult['data'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed',
                    'errors' => ['verification' => 'Customer details do not match our records']
                ], 400);
            }

            // Get the bill
            $billResult = $this->supabase->getById(config('supabase.tables.bills'), $validated['bill_id']);

            if (!$billResult['success'] || !$billResult['data']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bill not found'
                ], 404);
            }

            $bill = $billResult['data'];

            // Validate payment amount
            if ($validated['payment_type'] === 'Full' && $validated['amount'] != $bill['total_amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Full payment amount must match the bill total'
                ], 400);
            }

            if ($validated['payment_type'] === 'Partial' && $validated['amount'] >= $bill['total_amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Partial payment amount must be less than the bill total'
                ], 400);
            }

            // Store proof of payment (you may need to implement file storage with Supabase Storage)
            $proofPath = $request->file('proof_of_payment')->store('payment_proofs', 'public');

            // Create payment record
            $paymentData = [
                'user_id' => $validated['user_id'],
                'customer_id' => $validated['customer_id'],
                'bill_id' => $validated['bill_id'],
                'amount' => $validated['amount'],
                'payment_type' => $validated['payment_type'],
                'payment_method' => $validated['payment_method'],
                'account_number' => $validated['account_number'],
                'meter_number' => $validated['meter_number'],
                'proof_of_payment' => $proofPath,
                'status' => 'Pending',
                'remaining_balance' => $validated['payment_type'] === 'Partial' 
                    ? $bill['total_amount'] - $validated['amount']
                    : 0,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString()
            ];

            $result = $this->supabase->insert(config('supabase.tables.payments'), $paymentData);

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'payment' => $result['data'] ?? null
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error storing payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error storing payment: ' . $e->getMessage()
            ], 500);
        }
    }
} 