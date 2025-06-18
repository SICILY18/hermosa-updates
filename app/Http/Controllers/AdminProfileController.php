<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\SupabaseService;

class AdminProfileController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function show()
    {
        try {
            // Get staff data from session
            $staffData = session('staff_data');
            
            if (!$staffData) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get fresh staff data from Supabase
            $staffResult = $this->supabase->getById(config('supabase.tables.staff'), $staffData['id']);
            
            if (!$staffResult['success'] || !$staffResult['data']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff data not found'
                ], 404);
            }

            $staff = $staffResult['data'];

            return response()->json([
                'admin_id' => $staff['id'],
                'name' => $staff['name'],
                'address' => $staff['address'] ?? '',
                'contact' => $staff['contact_number'] ?? '',
                'email' => $staff['email'] ?? '',
                'role' => $staff['role'],
                'profile_picture' => isset($staff['profile_picture']) && $staff['profile_picture'] ? url('storage/' . $staff['profile_picture']) : null
            ]);

        } catch (\Exception $e) {
            Log::error('Profile show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load profile data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'nullable|string|max:255',
                'contact' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,gif|max:2048'
            ]);

            // Get staff data from session
            $staffData = session('staff_data');
            
            if (!$staffData) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get current staff data from Supabase
            $staffResult = $this->supabase->getById(config('supabase.tables.staff'), $staffData['id']);
            
            if (!$staffResult['success'] || !$staffResult['data']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff data not found'
                ], 404);
            }

            $currentStaff = $staffResult['data'];

            $updateData = [
                'name' => $request->name,
                'address' => $request->address,
                'contact_number' => $request->contact,
                'email' => $request->email,
                'updated_at' => now()->toISOString()
            ];

            if ($request->hasFile('profile_picture')) {
                try {
                    $file = $request->file('profile_picture');
                    
                    // Ensure the storage directory exists
                    $storage_path = storage_path('app/public/profile-pictures');
                    if (!file_exists($storage_path)) {
                        mkdir($storage_path, 0755, true);
                    }

                    // Delete old profile picture if exists
                    if (isset($currentStaff['profile_picture']) && $currentStaff['profile_picture']) {
                        $old_path = storage_path('app/public/' . $currentStaff['profile_picture']);
                        if (file_exists($old_path)) {
                            unlink($old_path);
                        }
                    }

                    // Generate unique filename
                    $filename = 'profile_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    
                    // Store new profile picture
                    $path = $file->storeAs('profile-pictures', $filename, 'public');
                    if (!$path) {
                        throw new \Exception('Failed to store the file');
                    }
                    
                    $updateData['profile_picture'] = $path;
                } catch (\Exception $e) {
                    Log::error('Profile picture upload failed: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload profile picture: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Update staff data in Supabase
            $updateResult = $this->supabase->update(
                config('supabase.tables.staff'),
                $updateData,
                [
                    ['column' => 'id', 'operator' => 'eq', 'value' => $staffData['id']]
                ]
            );

            if (!$updateResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update profile data'
                ], 500);
            }

            // Update session data with new information
            $updatedStaffData = array_merge($staffData, $updateData);
            session(['staff_data' => $updatedStaffData]);

            // Get the updated record to return the profile picture URL
            $updatedStaffResult = $this->supabase->getById(config('supabase.tables.staff'), $staffData['id']);
            $updatedStaff = $updatedStaffResult['data'];

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'profile_picture' => isset($updatedStaff['profile_picture']) && $updatedStaff['profile_picture'] ? url('storage/' . $updatedStaff['profile_picture']) : null
            ]);

        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }
} 