<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Log;

class AnnouncementController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function index()
    {
        try {
            $result = $this->supabase->getAllAnnouncements();

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch announcements'
                ], 500);
            }

            // Filter active announcements
            $activeAnnouncements = array_filter($result['data'], function($announcement) {
                return ($announcement['status'] ?? '') === 'active';
            });

            // Sort by created_at descending
            usort($activeAnnouncements, function($a, $b) {
                return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
            });

            return response()->json([
                'success' => true,
                'data' => array_values($activeAnnouncements)
            ]);
        } catch (\Exception $e) {
            Log::error('Announcement fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch announcements: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'content' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            // Get staff info from session
            $staff = session('staff_data');
            
            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'User session not found'
                ], 401);
            }

            $announcementData = [
                'title' => $request->title,
                'body' => $request->content,
                'status' => 'active',
                'staff_id' => $staff['id'],
                'posted_by' => $staff['name'],
                'published_at' => $request->start_date,
                'expired_at' => $request->end_date,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ];

            $result = $this->supabase->createAnnouncement($announcementData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create announcement: ' . ($result['error'] ?? 'Unknown error')
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Announcement created successfully',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Announcement creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create announcement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'content' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $updateData = [
                'title' => $request->title,
                'body' => $request->content,
                'published_at' => $request->start_date,
                'expired_at' => $request->end_date,
                'updated_at' => now()->toISOString(),
            ];

            $result = $this->supabase->updateAnnouncement($id, $updateData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update announcement'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Announcement update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update announcement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $updateData = [
                'status' => 'inactive',
                'updated_at' => now()->toISOString()
            ];

            $result = $this->supabase->updateAnnouncement($id, $updateData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete announcement'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Announcement deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Announcement deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete announcement: ' . $e->getMessage()
            ], 500);
        }
    }
} 