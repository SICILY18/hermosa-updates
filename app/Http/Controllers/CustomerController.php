<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\SupabaseService;

class CustomerController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'customer_type' => 'required|in:residential,commercial,government',
            'address' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'account_number' => 'required|string|max:20',
            'meter_number' => 'required|string|size:9',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create customer in Supabase Auth (optional, for customer portal access)
            $authResult = $this->supabase->signUp(
                $request->email,
                $request->password,
                [
                    'name' => $request->name,
                    'customer_type' => $request->customer_type
                ]
            );

            // Create customer record in Supabase database
            $customerData = [
                'name' => $request->name,
                'username' => $request->username,
                'customer_type' => $request->customer_type,
                'address' => $request->address,
                'contact_number' => $request->contact_number,
                'email' => $request->email,
                'account_number' => $request->account_number,
                'meter_number' => $request->meter_number,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ];

            $result = $this->supabase->insert(config('supabase.tables.customers'), $customerData);

            return response()->json([
                'success' => true,
                'message' => 'Customer account created successfully',
                'data' => $result['data'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating customer account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $customersResult = $this->supabase->getCustomers();
            
            if (!$customersResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch customers'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $customersResult['data']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching customers: ' . $e->getMessage()
            ], 500);
        }
    }
} 