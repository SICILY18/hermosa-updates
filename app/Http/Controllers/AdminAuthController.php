<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string'
            ]);

            // Get staff information from Supabase database by username
            $staffResult = $this->supabase->getStaffByUsername($request->username);
            
            if (!$staffResult['success'] || !$staffResult['data']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials - user not found'
                ], 401);
            }

            $staff = $staffResult['data'];

            // Check if user has allowed role
            if (!in_array($staff['role'], ['admin', 'bill handler'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to access this system'
                ], 403);
            }

            // Verify password
            $passwordValid = false;
            
            // Special case for superadmin (if still using plain text password in migration)
            if ($request->username === 'superadmin' && $request->password === 'superadmin123') {
                $passwordValid = true;
            } else {
                // For all other users, verify against hashed password
                if (isset($staff['password']) && !empty($staff['password'])) {
                    $passwordValid = Hash::check($request->password, $staff['password']);
                }
            }

            if (!$passwordValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Don't use Laravel's users table at all - just use sessions
            session([
                'admin_authenticated' => true,
                'staff_data' => $staff,
                'access_token' => 'local_session_' . time(),
                'user_id' => $staff['id'],
                'username' => $staff['username'],
                'role' => $staff['role']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful!',
                'access_token' => session('access_token'),
                'user' => [
                    'id' => $staff['id'],
                    'name' => $staff['name'],
                    'username' => $staff['username'],
                    'role' => $staff['role'],
                    'email' => $staff['email']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkAuth()
    {
        if (session('admin_authenticated') && session('staff_data')) {
            $staff = session('staff_data');
            
            return response()->json([
                'authenticated' => true,
                'user' => [
                    'id' => $staff['id'],
                    'name' => $staff['name'],
                    'username' => $staff['username'],
                    'role' => $staff['role'],
                    'email' => $staff['email']
                ],
                'staff_data' => $staff,
                'access_token' => session('access_token')
            ]);
        }
        return response()->json(['authenticated' => false], 401);
    }

    public function logout(Request $request)
    {
        try {
            // Clear all session data
            Session::flush();
            
            // Invalidate and regenerate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createStaff(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255',
                'password' => 'required|string|min:8',
                'role' => 'required|in:admin,bill handler,meter handler',
                'address' => 'required|string|max:255',
                'contact_number' => 'required|string|max:20',
                'email' => 'required|email|max:255'
            ]);

            // Create staff record in Supabase database
            $staffData = [
                'name' => $request->name,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'address' => $request->address,
                'contact_number' => $request->contact_number,
                'email' => $request->email,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString()
            ];

            $staffResult = $this->supabase->createStaff($staffData);

            if (!$staffResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create staff record: ' . $staffResult['error']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Staff account created successfully',
                'staff' => $staffResult['data']
            ], 201);

        } catch (\Exception $e) {
            Log::error('Create staff error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDashboardStats()
    {
        try {
            // Get customer count from Supabase
            $customersResult = $this->supabase->getCustomers();
            
            if (!$customersResult['success']) {
                Log::error('Failed to fetch customers for dashboard stats');
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch dashboard data'
                ], 500);
            }

            $customers = collect($customersResult['data']);
            $customerCount = $customers->count();

            // Log for debugging
            Log::info('Dashboard stats - Customer count: ' . $customerCount);
            Log::info('Dashboard stats - Sample customer data:', $customers->take(1)->toArray());

            // Now that customer_type is properly stored, we can filter by type
            $stats = [
                'total_customers' => $customerCount,
                'active_customers' => $customerCount, // For now, assume all are active
                'residential_customers' => $customers->where('customer_type', 'residential')->count(),
                'commercial_customers' => $customers->where('customer_type', 'commercial')->count(),
                'government_customers' => $customers->where('customer_type', 'government')->count(),
            ];

            Log::info('Dashboard stats response:', $stats);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}

