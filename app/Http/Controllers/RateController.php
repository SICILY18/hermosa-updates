<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RateController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_type' => 'required|string|in:commercial,residential,government',
            'minimum_charge' => 'required|numeric|min:0',
            'rate_per_cu_m' => 'required|numeric|min:0',
        ]);

        try {
            $rateData = [
                'customer_type' => Str::lower($request->customer_type),
                'minimum_charge' => $request->minimum_charge,
                'rate_per_cu_m' => $request->rate_per_cu_m,
                'effective_date' => now()->format('Y-m-d'),
                'status' => 'active',
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ];

            $result = $this->supabase->createRate($rateData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add rate: ' . ($result['error'] ?? 'Unknown error')
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rate added successfully',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Rate creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add rate: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $result = $this->supabase->getAllRates();
            
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch rates'
                ], 500);
            }

            // Filter active rates
            $activeRates = array_filter($result['data'] ?? [], function($rate) {
                return ($rate['status'] ?? '') === 'active';
            });
            
            // Return the rates directly (not wrapped in data object)
            return response()->json(array_values($activeRates));
        } catch (\Exception $e) {
            Log::error('Rate fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rates: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'minimum_charge' => 'required|numeric|min:0',
            'rate_per_cu_m' => 'required|numeric|min:0',
        ]);

        try {
            $updateData = [
                'minimum_charge' => $request->minimum_charge,
                'rate_per_cu_m' => $request->rate_per_cu_m,
                'updated_at' => now()->toISOString(),
            ];

            $result = $this->supabase->updateRate($id, $updateData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update rate'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rate updated successfully',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Rate update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update rate: ' . $e->getMessage()
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

            $result = $this->supabase->updateRate($id, $updateData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete rate'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rate deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Rate deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rate: ' . $e->getMessage()
            ], 500);
        }
    }
} 