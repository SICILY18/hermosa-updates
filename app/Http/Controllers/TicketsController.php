<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TicketsController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * Get all tickets
     */
    public function index()
    {
        try {
            $result = $this->supabase->getAllTickets();

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch tickets'
                ], 500);
            }

            // Sort by created_at descending
            $tickets = $result['data'];
            
            // Format image URLs for all tickets
            foreach ($tickets as &$ticket) {
                if (isset($ticket['image_url']) && $ticket['image_url']) {
                    // If it's already a full URL, keep it as is
                    if (!str_starts_with($ticket['image_url'], 'http')) {
                        // Convert to full URL if it's a relative path
                        if (str_starts_with($ticket['image_url'], '/storage/')) {
                            $ticket['image_url'] = url($ticket['image_url']);
                        } else {
                            // Handle Laravel storage path format
                            $ticket['image_url'] = url(Storage::url($ticket['image_url']));
                        }
                    }
                }
            }
            
            usort($tickets, function($a, $b) {
                return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
            });

            return response()->json([
                'success' => true,
                'data' => $tickets
            ]);
        } catch (\Exception $e) {
            Log::error('Ticket fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tickets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new ticket (for customer submission)
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string',
            'category' => 'required|string|in:Technical,Non-Technical',
            'subcategory' => 'required|string',
            'description' => 'required|string',
            'image' => 'nullable|image|max:2048' // 2MB max
        ]);

        try {
            $imageUrl = null;
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('ticket-images', 'public');
                
                // Generate full URL for the image
                $imageUrl = url(Storage::url($imagePath));
                
                Log::info('Image uploaded', [
                    'path' => $imagePath,
                    'url' => $imageUrl
                ]);
            }

            // Get customer information
            $customersResult = $this->supabase->getCustomers();
            $customer = null;
            if ($customersResult['success']) {
                foreach ($customersResult['data'] as $c) {
                    if ($c['account_number'] === $request->account_number) {
                        $customer = $c;
                        break;
                    }
                }
            }

            // Generate unique ticket reference
            $ticketReference = $this->generateTicketReference();

            // Generate subject based on category and subcategory
            $subject = $request->category . ' - ' . $request->subcategory;

            // Initial remarks history
            $remarksHistory = json_encode([]);

            $ticketData = [
                'ticket_reference' => $ticketReference,
                'account_number' => $request->account_number,
                'customer_name' => $customer ? ($customer['full_name'] ?? $customer['name'] ?? '') : '',
                'customer_id' => $customer ? $customer['id'] : null,
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'subject' => $subject,
                'description' => $request->description,
                'status' => 'open',
                'priority' => 'Medium', // Default priority
                'ticket_remarks' => '',
                'admin_response' => null,
                'image_url' => $imageUrl,
                'remarks_history' => $remarksHistory,
                'admin_remarks' => null,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ];

            $result = $this->supabase->createTicket($ticketData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create ticket: ' . ($result['error'] ?? 'Unknown error')
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ticket submitted successfully',
                'data' => $result['data'],
                'ticket_reference' => $ticketReference
            ]);
        } catch (\Exception $e) {
            Log::error('Ticket creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a ticket by reference (for admin/staff)
     */
    public function updateByReference(Request $request, $ticketReference)
    {
        $request->validate([
            'status' => 'sometimes|in:open,pending,resolved,closed',
            'ticket_remarks' => 'sometimes|string',
            'priority' => 'sometimes|in:Low,Medium,High,Urgent'
        ]);

        try {
            Log::info('Updating ticket: ' . $ticketReference, [
                'request_data' => $request->all()
            ]);

            // Find ticket by ticket_reference
            $ticketsResult = $this->supabase->getAllTickets();
            
            if (!$ticketsResult['success']) {
                Log::error('Failed to fetch tickets for update', [
                    'error' => $ticketsResult['error'] ?? 'Unknown error'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch tickets'
                ], 500);
            }

            $ticket = null;
            foreach ($ticketsResult['data'] as $t) {
                if ($t['ticket_reference'] === $ticketReference) {
                    $ticket = $t;
                    break;
                }
            }

            if (!$ticket) {
                Log::error('Ticket not found for update', [
                    'ticket_reference' => $ticketReference
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not found'
                ], 404);
            }
            
            Log::info('Found ticket for update', [
                'ticket_id' => $ticket['id'],
                'current_status' => $ticket['status'] ?? 'unknown'
            ]);

            // Get staff info from session
            $staff = session('staff_data');
            $userName = $staff ? $staff['name'] : 'Admin User';

            // Update remarks history if new remarks provided
            $remarksHistory = [];
            if (isset($ticket['remarks_history']) && $ticket['remarks_history']) {
                $remarksHistory = is_string($ticket['remarks_history']) 
                    ? json_decode($ticket['remarks_history'], true) 
                    : $ticket['remarks_history'];
            }
            
            // Ensure remarks history is an array
            if (!is_array($remarksHistory)) {
                $remarksHistory = [];
            }

            // Add new remark if provided
            if ($request->has('ticket_remarks') && !empty($request->ticket_remarks)) {
                $remarksHistory[] = [
                    'id' => count($remarksHistory) + 1,
                    'user' => $userName,
                    'remarks' => $request->ticket_remarks,
                    'timestamp' => now()->toISOString()
                ];
            }

            $updateData = [
                'updated_at' => now()->toISOString(),
                'remarks_history' => json_encode($remarksHistory)
            ];

            // Add other fields if provided
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
            }
            
            if ($request->has('ticket_remarks')) {
                $updateData['ticket_remarks'] = $request->ticket_remarks;
            }
            
            if ($request->has('priority')) {
                $updateData['priority'] = $request->priority;
            }

            Log::info('Updating ticket with data', [
                'ticket_id' => $ticket['id'],
                'update_data' => $updateData
            ]);

            $result = $this->supabase->updateTicket($ticket['id'], $updateData);

            if (!$result['success']) {
                Log::error('Supabase update failed', [
                    'ticket_id' => $ticket['id'],
                    'error' => $result['error'] ?? 'Unknown error',
                    'response' => $result
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update ticket: ' . ($result['error'] ?? 'Unknown error')
                ], 500);
            }

            Log::info('Ticket updated successfully', [
                'ticket_id' => $ticket['id'],
                'ticket_reference' => $ticketReference
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket updated successfully',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Ticket update exception: ' . $e->getMessage(), [
                'ticket_reference' => $ticketReference,
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a ticket (for admin/staff) - Legacy method using database ID
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'sometimes|in:open,pending,resolved,closed',
            'ticket_remarks' => 'sometimes|string',
            'priority' => 'sometimes|in:Low,Medium,High,Urgent'
        ]);

        try {
            // Get current ticket data
            $currentTicket = $this->supabase->getById(config('supabase.tables.tickets'), $id);
            
            if (!$currentTicket['success'] || !$currentTicket['data']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not found'
                ], 404);
            }

            $ticket = $currentTicket['data'];
            
            // Get staff info from session
            $staff = session('staff_data');
            $userName = $staff ? $staff['name'] : 'Admin User';

            // Update remarks history if new remarks provided
            $remarksHistory = [];
            if (isset($ticket['remarks_history']) && $ticket['remarks_history']) {
                $remarksHistory = is_string($ticket['remarks_history']) 
                    ? json_decode($ticket['remarks_history'], true) 
                    : $ticket['remarks_history'];
            }

            // Add new remark if provided
            if ($request->has('ticket_remarks') && !empty($request->ticket_remarks)) {
                $remarksHistory[] = [
                    'id' => count($remarksHistory) + 1,
                    'user' => $userName,
                    'remarks' => $request->ticket_remarks,
                    'timestamp' => now()->toISOString()
                ];
            }

            $updateData = [
                'updated_at' => now()->toISOString(),
                'remarks_history' => json_encode($remarksHistory)
            ];

            // Add other fields if provided
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
            }
            
            if ($request->has('ticket_remarks')) {
                $updateData['ticket_remarks'] = $request->ticket_remarks;
            }
            
            if ($request->has('priority')) {
                $updateData['priority'] = $request->priority;
            }

            $result = $this->supabase->updateTicket($id, $updateData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update ticket'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ticket updated successfully',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Ticket update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific ticket
     */
    public function show($id)
    {
        try {
            $result = $this->supabase->getById(config('supabase.tables.tickets'), $id);

            if (!$result['success'] || !$result['data']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not found'
                ], 404);
            }

            $ticket = $result['data'];
            
            // Format image URL if exists
            if (isset($ticket['image_url']) && $ticket['image_url']) {
                // If it's already a full URL, keep it as is
                if (!str_starts_with($ticket['image_url'], 'http')) {
                    // Convert to full URL if it's a relative path
                    if (str_starts_with($ticket['image_url'], '/storage/')) {
                        $ticket['image_url'] = url($ticket['image_url']);
                    } else {
                        // Handle Laravel storage path format
                        $ticket['image_url'] = url(Storage::url($ticket['image_url']));
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $ticket
            ]);
        } catch (\Exception $e) {
            Log::error('Ticket fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a ticket (soft delete by changing status)
     */
    public function destroy($id)
    {
        try {
            $updateData = [
                'status' => 'deleted',
                'updated_at' => now()->toISOString()
            ];

            $result = $this->supabase->updateTicket($id, $updateData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete ticket'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ticket deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Ticket deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customers for account number dropdown
     */
    public function getCustomers()
    {
        try {
            $result = $this->supabase->getCustomers();

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch customers'
                ], 500);
            }

            // Format for dropdown
            $customers = collect($result['data'])->map(function($customer) {
                return [
                    'id' => $customer['id'],
                    'account_number' => $customer['account_number'] ?? '',
                    'name' => $customer['full_name'] ?? $customer['name'] ?? '',
                    'formatted_account' => $this->formatAccountNumber($customer['account_number'] ?? '')
                ];
            })->filter(function($customer) {
                return !empty($customer['account_number']);
            });

            return response()->json([
                'success' => true,
                'data' => $customers
            ]);
        } catch (\Exception $e) {
            Log::error('Customer fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ticket categories and subcategories
     */
    public function getCategories()
    {
        $categories = [
            'Technical' => [
                'Water Pressure',
                'Water Quality',
                'Water Meter Request for Inspection',
                'Report Leak',
                'Restoration of Water Line'
            ],
            'Non-Technical' => [
                'No Billing Received',
                'Billing Concern',
                'Reclassification of Account',
                'Others'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Generate unique ticket reference
     */
    private function generateTicketReference()
    {
        $timestamp = round(microtime(true) * 1000); // milliseconds
        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        return 'TKT-' . $timestamp . '-' . $random;
    }

    /**
     * Format account number for display
     */
    private function formatAccountNumber($accountNumber)
    {
        if (empty($accountNumber)) return '';
        
        // Remove any existing formatting
        $clean = preg_replace('/[^0-9]/', '', $accountNumber);
        
        // Format as 5119-0120-0173
        if (strlen($clean) >= 12) {
            return substr($clean, 0, 4) . '-' . substr($clean, 4, 4) . '-' . substr($clean, 8, 4);
        }
        
        return $accountNumber;
    }
} 