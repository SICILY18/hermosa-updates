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
Route::get('/test-supabase', function () {
    try {
        $supabase = app(\App\Services\SupabaseService::class);
        
        // Test staff query
        $staffResult = $supabase->getAllStaff();
        
        return response()->json([
            'supabase_connected' => true,
            'staff_query_success' => $staffResult['success'],
            'staff_count' => $staffResult['success'] ? count($staffResult['data']) : 0,
            'database_config' => [
                'host' => config('database.connections.pgsql.host'),
                'database' => config('database.connections.pgsql.database'),
                'connection' => config('database.default')
            ],
            'supabase_config' => [
                'url' => config('supabase.url'),
                'tables' => config('supabase.tables')
            ]
        ]);
    } catch (Exception $e) {
        return response()->json([
            'supabase_connected' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

require __DIR__.'/auth.php';
