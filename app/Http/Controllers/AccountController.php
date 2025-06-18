<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function createStaffAccount(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,meter handler,bill handler',
            'address' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'email' => 'required|email|max:255'
        ]);

        try {
            // Create user in Supabase Auth
            $authResult = $this->supabase->signUp(
                $request->email,
                $request->password,
                [
                    'name' => $request->name,
                    'role' => $request->role
                ]
            );

            // Create staff record in Supabase database (continue even if auth creation fails for existing users)
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
                'message' => 'Staff account created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create staff account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createCustomerAccount(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'customer_type' => 'required|in:residential,commercial,government',
                'account_number' => 'required|string|unique:customers',
                'address' => 'required|string',
                'contact_number' => 'required|string',
                'meter_number' => 'required|string|unique:customers'
            ]);

            DB::beginTransaction();

            // Create user record
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            // Create customer record
            $customer = Customer::create([
                'user_id' => $user->id,
                'customer_type' => $request->customer_type,
                'account_number' => $request->account_number,
                'address' => $request->address,
                'contact_number' => $request->contact_number,
                'meter_number' => $request->meter_number
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer account created successfully',
                'data' => [
                    'user' => $user,
                    'customer' => $customer
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Customer account creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listAccounts(Request $request)
    {
        try {
            $type = $request->query('type', 'all');
            $search = $request->query('search', '');

            Log::info('Fetching accounts', ['type' => $type, 'search' => $search]);

            if ($type === 'customer') {
                // Query for customer accounts
                $conditions = [];
                if ($search) {
                    // Note: Supabase REST API doesn't support OR queries directly through our helper
                    // We'll get all customers and filter in PHP for now
                }

                $customersResult = $this->supabase->getCustomers();
                
                if (!$customersResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to fetch customers'
                    ], 500);
                }

                $customerAccounts = collect($customersResult['data']);

                // Apply search filter
                if ($search) {
                    $customerAccounts = $customerAccounts->filter(function($customer) use ($search) {
                        return str_contains(strtolower($customer['full_name'] ?? ''), strtolower($search)) ||
                               str_contains(strtolower($customer['username'] ?? ''), strtolower($search)) ||
                               str_contains(strtolower($customer['email'] ?? ''), strtolower($search)) ||
                               str_contains(strtolower($customer['account_number'] ?? ''), strtolower($search));
                    });
                }

                Log::info('Customer accounts found', ['count' => $customerAccounts->count()]);

                // Format customer accounts
                $formattedCustomerAccounts = $customerAccounts->map(function($customer) {
                    return [
                        'id' => $customer['id'],
                        'name' => $customer['full_name'] ?? '',
                        'username' => $customer['username'] ?? '',
                        'email' => $customer['email'] ?? '',
                        'customer_type' => 'N/A', // Not available in current table
                        'contact_number' => $customer['phone_number'] ?? '',
                        'address' => 'N/A', // Not available in current table
                        'account_number' => $customer['account_number'] ?? '',
                        'meter_number' => 'N/A', // Not available in current table
                        'type' => 'customer'
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data' => [
                        'data' => $formattedCustomerAccounts->values(),
                        'total' => $formattedCustomerAccounts->count()
                    ]
                ]);
            }

            if ($type === 'all') {
                // Get both staff and customer accounts
                $staffResult = $this->supabase->query(config('supabase.tables.staff'));
                $customersResult = $this->supabase->getCustomers();

                if (!$staffResult['success'] || !$customersResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to fetch accounts'
                    ], 500);
                }

                $staffAccounts = collect($staffResult['data']);
                $customerAccounts = collect($customersResult['data']);

                // Apply search filter
                if ($search) {
                    $staffAccounts = $staffAccounts->filter(function($staff) use ($search) {
                        return str_contains(strtolower($staff['name']), strtolower($search)) ||
                               str_contains(strtolower($staff['username']), strtolower($search)) ||
                               str_contains(strtolower($staff['email']), strtolower($search));
                    });

                    $customerAccounts = $customerAccounts->filter(function($customer) use ($search) {
                        return str_contains(strtolower($customer['full_name'] ?? ''), strtolower($search)) ||
                               str_contains(strtolower($customer['username'] ?? ''), strtolower($search)) ||
                               str_contains(strtolower($customer['email'] ?? ''), strtolower($search)) ||
                               str_contains(strtolower($customer['account_number'] ?? ''), strtolower($search));
                    });
                }

                // Format staff accounts
                $formattedStaffAccounts = $staffAccounts->map(function($staff) {
                    return [
                        'id' => $staff['id'],
                        'name' => $staff['name'],
                        'username' => $staff['username'],
                        'email' => $staff['email'],
                        'role' => $staff['role'],
                        'contact_number' => $staff['contact_number'],
                        'address' => $staff['address'],
                        'type' => 'staff'
                    ];
                });

                // Format customer accounts
                $formattedCustomerAccounts = $customerAccounts->map(function($customer) {
                    return [
                        'id' => $customer['id'],
                        'name' => $customer['full_name'] ?? '',
                        'username' => $customer['username'] ?? '',
                        'email' => $customer['email'] ?? '',
                        'customer_type' => 'N/A', // Not available in current table
                        'contact_number' => $customer['phone_number'] ?? '',
                        'address' => 'N/A', // Not available in current table
                        'account_number' => $customer['account_number'] ?? '',
                        'meter_number' => 'N/A', // Not available in current table
                        'type' => 'customer'
                    ];
                });

                // Merge both collections
                $allAccounts = $formattedStaffAccounts->concat($formattedCustomerAccounts);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'data' => $allAccounts->values(),
                        'total' => $allAccounts->count()
                    ]
                ]);
            }

            // Query for staff accounts only
            $staffResult = $this->supabase->query(config('supabase.tables.staff'));
            
            if (!$staffResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch staff accounts'
                ], 500);
            }

            $staffAccounts = collect($staffResult['data']);

            // Apply search and role filters
            if ($search) {
                $staffAccounts = $staffAccounts->filter(function($staff) use ($search) {
                    return str_contains(strtolower($staff['name']), strtolower($search)) ||
                           str_contains(strtolower($staff['username']), strtolower($search)) ||
                           str_contains(strtolower($staff['email']), strtolower($search));
                });
            }

            // Filter by role if not 'all'
            if ($type !== 'all') {
                $staffAccounts = $staffAccounts->filter(function($staff) use ($type) {
                    return $staff['role'] === $type;
                });
            }

            Log::info('Staff accounts found', ['count' => $staffAccounts->count()]);

            // Format the response
            $formattedAccounts = $staffAccounts->map(function($staff) {
                return [
                    'id' => $staff['id'],
                    'name' => $staff['name'],
                    'username' => $staff['username'],
                    'email' => $staff['email'],
                    'role' => $staff['role'],
                    'contact_number' => $staff['contact_number'],
                    'address' => $staff['address'],
                    'type' => 'staff'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $formattedAccounts->values(),
                    'total' => $formattedAccounts->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Account listing failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to list accounts: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteStaff($id)
    {
        try {
            $staffResult = $this->supabase->getById(config('supabase.tables.staff'), $id);
            
            if (!$staffResult['success'] || !$staffResult['data']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff account not found'
                ], 404);
            }

            $deleteResult = $this->supabase->delete(
                config('supabase.tables.staff'),
                [
                    ['column' => 'id', 'operator' => 'eq', 'value' => $id]
                ]
            );

            if (!$deleteResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete staff account'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Staff account deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Staff deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete staff account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStaff(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255',
                'role' => 'required|in:admin,meter handler,bill handler',
                'address' => 'required|string|max:255',
                'contact_number' => 'required|string|max:20',
                'email' => 'required|email|max:255'
            ]);

            $staffResult = $this->supabase->getById(config('supabase.tables.staff'), $id);
            
            if (!$staffResult['success'] || !$staffResult['data']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff account not found'
                ], 404);
            }

            $updateData = [
                'name' => $request->name,
                'username' => $request->username,
                'role' => $request->role,
                'address' => $request->address,
                'contact_number' => $request->contact_number,
                'email' => $request->email,
                'updated_at' => now()->toISOString()
            ];

            $updateResult = $this->supabase->update(
                config('supabase.tables.staff'),
                $updateData,
                [
                    ['column' => 'id', 'operator' => 'eq', 'value' => $id]
                ]
            );

            if (!$updateResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update staff account'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Staff account updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Staff update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update staff account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createCustomer(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:100',
                'password' => 'required|min:8|max:255',
                'customer_type' => 'required|in:residential,commercial,government|max:20',
                'account_number' => 'required|string|max:9',
                'meter_number' => 'required|string|max:20',
                'contact_number' => 'required|string|max:15',
                'address' => 'required|string'
            ]);

            // Generate username from email (everything before @)
            $username = explode('@', $validated['email'])[0];
            $username = substr($username, 0, 50);

            // Create customer in Supabase Auth
            $authResult = $this->supabase->signUp(
                $validated['email'],
                $validated['password'],
                [
                    'full_name' => $validated['name'],
                    'customer_type' => $validated['customer_type']
                ]
            );

            // Create customer record - mapping to actual Supabase table structure
            $customerData = [
                'full_name' => $validated['name'],
                'first_name' => explode(' ', $validated['name'])[0],
                'last_name' => isset(explode(' ', $validated['name'])[1]) ? implode(' ', array_slice(explode(' ', $validated['name']), 1)) : '',
                'username' => $username,
                'password' => Hash::make($validated['password']),
                'customer_type' => $validated['customer_type'],
                'address' => $validated['address'],
                'phone_number' => $validated['contact_number'],
                'email' => $validated['email'],
                'account_number' => $validated['account_number'],
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString()
            ];

            $customerResult = $this->supabase->createCustomer($customerData);

            if (!$customerResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create customer: ' . $customerResult['error']
                ], 500);
            }

            Log::info('New customer created', ['customer' => $customerResult['data']]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => $customerResult['data']
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateCustomer(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255',
                'customer_type' => 'required|in:residential,commercial,government',
                'address' => 'required|string|max:255',
                'contact_number' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'account_number' => 'required|string|max:20',
                'meter_number' => 'required|string|max:20'
            ]);

            $customerResult = $this->supabase->getCustomer($id);
            
            if (!$customerResult['success'] || !$customerResult['data']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer account not found'
                ], 404);
            }

            // Update data - mapping to actual Supabase table structure
            $updateData = [
                'full_name' => $request->name,
                'first_name' => explode(' ', $request->name)[0],
                'last_name' => isset(explode(' ', $request->name)[1]) ? implode(' ', array_slice(explode(' ', $request->name), 1)) : '',
                'username' => $request->username,
                'customer_type' => $request->customer_type,
                'address' => $request->address,
                'phone_number' => $request->contact_number,
                'email' => $request->email,
                'account_number' => $request->account_number,
                'updated_at' => now()->toISOString()
            ];

            $updateResult = $this->supabase->updateCustomer($id, $updateData);

            if (!$updateResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update customer account'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Customer account updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Customer update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteCustomer($id)
    {
        try {
            $customerResult = $this->supabase->getCustomer($id);
            
            if (!$customerResult['success'] || !$customerResult['data']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer account not found'
                ], 404);
            }

            $deleteResult = $this->supabase->deleteCustomer($id);

            if (!$deleteResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete customer account'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Customer account deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Customer deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer account: ' . $e->getMessage()
            ], 500);
        }
    }
} 