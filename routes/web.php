<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AdminAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes
Route::get('/', function () {
    return Inertia::render('AdminLogin');
});

Route::post('/api/admin-login', [AdminAuthController::class, 'login']);
Route::get('/api/check-auth', [AdminAuthController::class, 'checkAuth']);

// Protected routes
Route::middleware(['admin.auth'])->prefix('admin')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    
    Route::get('/dashboard', function () {
        return Inertia::render('AdminDashboard');
    })->name('admin.dashboard');

    Route::get('/profile', function () {
        return Inertia::render('Profile', [
            'auth' => [
                'user' => session('staff_data'),
            ],
        ]);
    })->name('admin.profile');

    Route::get('/accounts', function () {
        return Inertia::render('Accounts');
    })->name('admin.accounts');

    Route::get('/announcement', function () {
        return Inertia::render('Announcement');
    })->name('admin.announcement');

    Route::get('/tickets', function () {
        return Inertia::render('Tickets');
    })->name('admin.tickets');

    Route::get('/reports', function () {
        return Inertia::render('Reports');
    })->name('admin.reports');

    Route::get('/payment', function () {
        return Inertia::render('Payment');
    })->name('admin.payment');

    Route::get('/rate-management', function () {
        return Inertia::render('RateManagement');
    })->name('admin.rateManagement');
});

Route::middleware(['admin.auth'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return Inertia::render('AdminDashboard');
    })->name('admin.dashboard');

    // Bill Handler Dashboard
    Route::get('/bill-handler/dashboard', function () {
        return Inertia::render('BillHandlerDashboard');
    })->name('bill-handler.dashboard');

    // Bill Handler Tickets (sync with admin tickets)
    Route::get('/bill-handler/tickets', function () {
        return Inertia::render('Tickets');
    })->name('bill-handler.tickets');

    // Bill Handler Billing
    Route::get('/bill-handler/billing', function () {
        return Inertia::render('BillHandlerBilling');
    })->name('bill-handler.billing');

    // Bill Handler Customers
    Route::get('/bill-handler/customers', function () {
        return Inertia::render('BillHandlerCustomers');
    })->name('bill-handler.customers');
});

// Test route for Supabase connection
Route::get('/test-supabase', function() {
    try {
        $supabase = app(App\Services\SupabaseService::class);
        
        // Test basic connection
        $ticketsResult = $supabase->getAllTickets();
        
        // Test customers
        $customersResult = $supabase->getCustomers();
        
        return response()->json([
            'supabase_connected' => true,
            'tickets_result' => $ticketsResult,
            'tickets_count' => $ticketsResult['success'] ? count($ticketsResult['data']) : 0,
            'customers_result' => $customersResult,
            'customers_count' => $customersResult['success'] ? count($customersResult['data']) : 0,
            'supabase_config' => [
                'url' => config('supabase.url'),
                'table_name' => config('supabase.tables.tickets'),
                'has_service_key' => !empty(config('supabase.service_role_key')),
                'has_anon_key' => !empty(config('supabase.anon_key')),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'supabase_connected' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Debug route for ticket creation
Route::post('/test-ticket-creation', function(Illuminate\Http\Request $request) {
    try {
        $supabase = app(App\Services\SupabaseService::class);
        
        $testTicketData = [
            'ticket_reference' => 'TEST-' . time(),
            'account_number' => '123456789012',
            'customer_name' => 'Test Customer',
            'customer_id' => 1,
            'category' => 'Technical',
            'subcategory' => 'Water Pressure',
            'subject' => 'Technical - Water Pressure',
            'description' => 'Test ticket for debugging',
            'status' => 'open',
            'priority' => 'Medium',
            'ticket_remarks' => '',
            'admin_response' => null,
            'image_url' => null,
            'remarks_history' => json_encode([]),
            'admin_remarks' => null,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];
        
        $result = $supabase->createTicket($testTicketData);
        
        return response()->json([
            'success' => $result['success'],
            'data' => $result,
            'test_data' => $testTicketData
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Debug authentication route
Route::get('/debug/auth-status', function() {
    $staff = session('staff_data');
    return response()->json([
        'authenticated' => !empty($staff),
        'staff_data' => $staff,
        'session_id' => session()->getId(),
        'has_admin_auth_middleware' => true
    ]);
});

// Debug route to test ticket access with auth
Route::middleware(['admin.auth'])->get('/debug/test-tickets', function() {
    try {
        $supabase = app(App\Services\SupabaseService::class);
        $result = $supabase->getAllTickets();
        
        return response()->json([
            'authenticated' => true,
            'tickets_result' => $result,
            'tickets_count' => $result['success'] ? count($result['data']) : 0,
            'staff_session' => session('staff_data')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'authenticated' => true,
            'error' => $e->getMessage(),
            'staff_session' => session('staff_data')
        ]);
    }
});

// Debug routes for testing
Route::get('/debug/test-ticket-update/{ticketReference}', function($ticketReference) {
    try {
        $controller = new \App\Http\Controllers\TicketsController(new \App\Services\SupabaseService());
        
        // Create a test request
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'status' => 'pending',
            'ticket_remarks' => 'Test update from debug route'
        ]);
        
        $response = $controller->updateByReference($request, $ticketReference);
        
        return response()->json([
            'debug' => true,
            'ticket_reference' => $ticketReference,
            'response' => $response->getData()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'debug' => true,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

Route::get('/debug/check-images', function() {
    $controller = new \App\Http\Controllers\TicketsController(new \App\Services\SupabaseService());
    $tickets = $controller->index();
    $ticketData = $tickets->getData();
    
    $imageInfo = [];
    if ($ticketData->success) {
        foreach ($ticketData->data as $ticket) {
            if (isset($ticket->image_url) && $ticket->image_url) {
                $imageInfo[] = [
                    'ticket_reference' => $ticket->ticket_reference,
                    'image_url' => $ticket->image_url,
                    'url_accessible' => @get_headers($ticket->image_url) ? true : false
                ];
            }
        }
    }
    
    return response()->json([
        'debug' => true,
        'images' => $imageInfo,
        'storage_path' => storage_path('app/public'),
        'public_path' => public_path('storage'),
        'storage_link_exists' => is_link(public_path('storage'))
    ]);
});

// Comprehensive debug route to test all aspects
Route::get('/debug/ticket-system-test', function() {
    try {
        $output = [];
        
        // Test 1: Check if SupabaseService is working
        $output['test_1_supabase_service'] = 'Testing Supabase Service...';
        try {
            $supabase = new \App\Services\SupabaseService();
            $result = $supabase->getAllTickets();
            $output['test_1_result'] = [
                'success' => $result['success'],
                'ticket_count' => $result['success'] ? count($result['data']) : 0,
                'error' => $result['error'] ?? null
            ];
        } catch (\Exception $e) {
            $output['test_1_result'] = ['error' => $e->getMessage()];
        }
        
        // Test 2: Check session data
        $output['test_2_session'] = [
            'has_staff_data' => session()->has('staff_data'),
            'staff_data' => session('staff_data'),
            'session_id' => session()->getId()
        ];
        
        // Test 3: Test ticket controller instantiation
        $output['test_3_controller'] = 'Testing TicketsController...';
        try {
            $controller = new \App\Http\Controllers\TicketsController($supabase);
            $output['test_3_result'] = 'Controller created successfully';
        } catch (\Exception $e) {
            $output['test_3_result'] = ['error' => $e->getMessage()];
        }
        
        // Test 4: Test finding a specific ticket
        $output['test_4_find_ticket'] = 'Testing ticket lookup...';
        try {
            $ticketsResult = $supabase->getAllTickets();
            if ($ticketsResult['success'] && !empty($ticketsResult['data'])) {
                $testTicket = $ticketsResult['data'][0];
                $output['test_4_result'] = [
                    'found_ticket' => true,
                    'ticket_reference' => $testTicket['ticket_reference'] ?? 'N/A',
                    'ticket_id' => $testTicket['id'] ?? 'N/A',
                    'current_status' => $testTicket['status'] ?? 'N/A'
                ];
            } else {
                $output['test_4_result'] = ['found_ticket' => false, 'error' => 'No tickets found'];
            }
        } catch (\Exception $e) {
            $output['test_4_result'] = ['error' => $e->getMessage()];
        }
        
        // Test 5: Test update data preparation
        $output['test_5_update_data'] = 'Testing update data preparation...';
        try {
            $updateData = [
                'status' => 'pending',
                'ticket_remarks' => 'Test update from debug',
                'updated_at' => now()->toISOString(),
                'remarks_history' => json_encode([])
            ];
            $output['test_5_result'] = [
                'update_data' => $updateData,
                'json_valid' => json_last_error() === JSON_ERROR_NONE
            ];
        } catch (\Exception $e) {
            $output['test_5_result'] = ['error' => $e->getMessage()];
        }
        
        // Test 6: Check middleware
        $output['test_6_middleware'] = [
            'middleware_applied' => request()->route() ? request()->route()->middleware() : [],
            'user_authenticated' => auth()->check(),
            'auth_guard' => auth()->getDefaultDriver()
        ];
        
        return response()->json($output);
        
    } catch (\Exception $e) {
        return response()->json([
            'debug_error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Simple direct ticket update test (bypasses middleware)
Route::get('/debug/direct-update-test/{ticketReference}', function($ticketReference) {
    try {
        $supabase = new \App\Services\SupabaseService();
        
        // Get all tickets first
        $ticketsResult = $supabase->getAllTickets();
        
        if (!$ticketsResult['success']) {
            return response()->json([
                'error' => 'Failed to fetch tickets',
                'details' => $ticketsResult
            ]);
        }
        
        // Find the specific ticket
        $ticket = null;
        foreach ($ticketsResult['data'] as $t) {
            if ($t['ticket_reference'] === $ticketReference) {
                $ticket = $t;
                break;
            }
        }
        
        if (!$ticket) {
            return response()->json([
                'error' => 'Ticket not found',
                'ticket_reference' => $ticketReference,
                'available_tickets' => array_column($ticketsResult['data'], 'ticket_reference')
            ]);
        }
        
        // Try to update the ticket
        $updateData = [
            'status' => 'pending',
            'ticket_remarks' => 'Direct update test - ' . now(),
            'updated_at' => now()->toISOString(),
            'remarks_history' => json_encode([
                [
                    'id' => 1,
                    'user' => 'Debug Test',
                    'remarks' => 'Direct update test - ' . now(),
                    'timestamp' => now()->toISOString()
                ]
            ])
        ];
        
        $updateResult = $supabase->updateTicket($ticket['id'], $updateData);
        
        return response()->json([
            'ticket_found' => true,
            'ticket_id' => $ticket['id'],
            'original_status' => $ticket['status'] ?? 'unknown',
            'update_data' => $updateData,
            'update_result' => $updateResult
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

require __DIR__.'/auth.php';
