<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Center;
use Illuminate\Validation\Rule;

class CenterController extends Controller
{
    /**
     * Display a listing of centers with optional filtering.
     */
    public function index(Request $request)
    {
        try {
            $query = Center::with(['branch', 'staff']);

            // Filter by CSU_id
            if ($request->has('CSU_id')) {
                $query->where('CSU_id', $request->CSU_id);
            }

            // Filter by center_name (supports partial match)
            if ($request->has('center_name')) {
                $query->where('center_name', 'LIKE', '%' . $request->center_name . '%');
            }

            // Filter by branch_id
            if ($request->has('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            // Filter by staff_id
            if ($request->has('staff_id')) {
                $query->where('staff_id', $request->staff_id);
            }

            // Filter by location
            if ($request->has('location')) {
                $query->where('location', 'LIKE', '%' . $request->location . '%');
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $centers = $query->get();

            return response()->json([
                'status' => 'success',
                'data' => $centers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve centers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created center.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'CSU_id' => 'nullable|string|unique:centers,CSU_id',
                'open_days' => 'nullable|array',
                'branch_id' => 'required|exists:branches,branch_id',
                'staff_id' => 'required|string|exists:staffs,staff_id',
                'center_name' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'group_count' => 'nullable|integer|min:0',
                'status' => 'nullable|string|in:active,inactive',
            ]);

            $center = Center::create($validated);
            $center->load(['branch', 'staff']);

            return response()->json([
                'status' => 'success',
                'message' => 'Center created successfully',
                'data' => $center
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create center',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified center.
     */
    public function show($id)
    {
        try {
            $center = Center::with(['branch', 'staff'])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $center
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Center not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve center',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified center.
     */
    public function update(Request $request, $id)
    {
        try {
            $center = Center::findOrFail($id);

            $validated = $request->validate([
                'CSU_id' => [
                    'nullable',
                    'string',
                    Rule::unique('centers', 'CSU_id')->ignore($center->id)
                ],
                'open_days' => 'nullable|array',
                'branch_id' => 'sometimes|required|exists:branches,id',
                'staff_id' => 'sometimes|required|string|exists:staffs,staff_id',
                'center_name' => 'sometimes|required|string|max:255',
                'location' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'group_count' => 'nullable|integer|min:0',
                'status' => 'nullable|string|in:active,inactive',
            ]);

            $center->update($validated);
            $center->load(['branch', 'staff']);

            return response()->json([
                'status' => 'success',
                'message' => 'Center updated successfully',
                'data' => $center
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Center not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update center',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pending()
{
    $centers = Center::where('status', 'inactive')
        ->with(['branch', 'staff'])
        ->get();

    return response()->json([
        'status' => 'success',
        'status_code' => 2000,
        'message' => 'Center approval request fetched',
        'data' => $centers
    ], 200);
}


public function approve($id)
{
    $center = Center::findOrFail($id);

    if ($center->status === 'active') {
        return response()->json([
            'status' => 'error',
            'status_code' => 400,
            'message' => 'Center is already active'
        ], 400);
    }

    $center->update(['status' => 'active']);

    return response()->json([
        'status' => 'success',
        'status_code' => 2000,
        'message' => 'Center approved successfully',
        'data' => $center
    ], 200);
}


    /**
     * Remove the specified center.
     */
    public function destroy($id)
    {
        try {
            $center = Center::findOrFail($id);
            $center->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Center deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Center not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete center',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
