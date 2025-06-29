<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BillHandlerController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TicketsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::post('/admin-login', [AdminAuthController::class, 'login']);
Route::post('/admin-logout', [AdminAuthController::class, 'logout']);
Route::get('/check-auth', [AdminAuthController::class, 'checkAuth']);
Route::post('/create-staff', [AdminAuthController::class, 'createStaff']);

// Protected routes - using our custom admin auth middleware
Route::middleware(['admin.auth'])->group(function () {
    Route::get('/user', function (Request $request) {
        return session('staff_data');
    });

    // Dashboard Routes
    Route::get('/dashboard/stats', [AdminAuthController::class, 'getDashboardStats']);

    // Admin Profile Routes
    Route::get('/admin/profile', [AdminProfileController::class, 'show']);
    Route::post('/admin/profile/update', [AdminProfileController::class, 'update']);

    // Account Management Routes
    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountController::class, 'listAccounts']);
        Route::post('/staff', [AccountController::class, 'createStaffAccount']);
        Route::put('/staff/{id}', [AccountController::class, 'updateStaff']);
        Route::delete('/staff/{id}', [AccountController::class, 'deleteStaff']);
        Route::post('/customer', [AccountController::class, 'createCustomerAccount']);
        Route::post('/customer', [AccountController::class, 'createCustomer']);
        Route::put('/customer/{id}', [AccountController::class, 'updateCustomer']);
        Route::delete('/customer/{id}', [AccountController::class, 'deleteCustomer']);
        Route::post('/accounts/customer', [CustomerController::class, 'store']);
    });

    // Bill Handler Routes
    Route::prefix('bill-handler')->group(function () {
        Route::get('/bill-handler-dashboard', [BillHandlerController::class, 'BillHandlerDashboard']);
        Route::get('/customers', [BillHandlerController::class, 'getCustomers']);
    });

    // Rate Management Routes
    Route::get('/rates', [RateController::class, 'index']);
    Route::post('/rates', [RateController::class, 'store']);
    Route::put('/rates/{id}', [RateController::class, 'update']);
    Route::delete('/rates/{id}', [RateController::class, 'destroy']);

    // Announcement Routes
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::post('/announcements', [AnnouncementController::class, 'store']);
    Route::put('/announcements/{id}', [AnnouncementController::class, 'update']);
    Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);

    // Ticket Routes
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketsController::class, 'index']);
        Route::post('/', [TicketsController::class, 'store']);
        Route::get('/customers', [TicketsController::class, 'getCustomers']);
        Route::get('/categories', [TicketsController::class, 'getCategories']);
        Route::get('/{id}', [TicketsController::class, 'show']);
        Route::put('/{id}', [TicketsController::class, 'update']);
        Route::put('/ref/{ticketReference}', [TicketsController::class, 'updateByReference']);
        Route::delete('/{id}', [TicketsController::class, 'destroy']);
    });

    // Payment Routes
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::post('/{id}/approve', [PaymentController::class, 'approve']);
    });
});

// Temporary debug route
Route::get('/debug/check-admin', function() {
    $admins = DB::table('admin')->get();
    return response()->json([
        'admins' => $admins,
        'count' => $admins->count()
    ]);
});

// Public Customer Ticket Routes (no authentication required)
Route::prefix('public')->group(function () {
    Route::get('/tickets/customers', [TicketsController::class, 'getCustomers']);
    Route::get('/tickets/categories', [TicketsController::class, 'getCategories']);
    Route::post('/tickets', [TicketsController::class, 'store']);
});
