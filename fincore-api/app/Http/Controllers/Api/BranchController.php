<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class BranchController extends Controller
{
    
    public function index(Request $request)
    {
        try {
            $query = Branch::query();
            $isFiltered = false;

            // Filter by branch_id
            if ($request->has('branch_id')) {
                $query->where('branch_id', $request->branch_id);
                $isFiltered = true;
            }

            // Filter by branch_name (supports partial match)
            if ($request->has('branch_name')) {
                $query->where('branch_name', 'LIKE', '%' . $request->branch_name . '%');
                $isFiltered = true;
            }

            // Filter by location
            if ($request->has('location')) {
                $query->where('location', 'LIKE', '%' . $request->location . '%');
                $isFiltered = true;
            }

            $branches = $query->get();

            // Different message based on whether filters were applied
            $message = $isFiltered 
                ? 'Branch filter applied successfully' 
                : 'Branch list fetched successfully';

            return response()->json([
                'status' => 'success',
                'status_code' => 2000,
                'message' => $message,
                'data' => $branches,
                'count' => $branches->count()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching branches', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'message' => 'Failed to retrieve branches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'branch_id' => 'required|string|unique:branches,branch_id',
                'branch_name' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'staff_ids' => 'nullable|array',
                'staff_ids.*' => 'string|max:50',
            ]);

            // Create branch
            $branch = Branch::create($validated);

            // Log successful creation
            Log::info('Branch created successfully', [
                'branch_id' => $branch->id,
                'branch_name' => $branch->branch_name,
                'created_by' => auth()->id() ?? 'system'
            ]);

            return response()->json([
                'status' => 'success',
                'status_code' => 2010,
                'message' => 'Branch created successfully',
                'data' => $branch
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Check if it's a duplicate branch_id error
            $errors = $e->errors();
            
            if (isset($errors['branch_id'])) {
                foreach ($errors['branch_id'] as $error) {
                    if (str_contains($error, 'has already been taken')) {
                        return response()->json([
                            'status' => 'error',
                            'status_code' => 4090,
                            'message' => 'Branch already exists',
                            'error' => 'A branch with this ID already exists in the system',
                            'errors' => $errors
                        ], 409);
                    }
                }
            }

            // Other validation errors
            return response()->json([
                'status' => 'error',
                'status_code' => 422,
                'message' => 'Validation failed',
                'errors' => $errors
            ], 422);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database duplicate entry (fallback)
            if ($e->getCode() == 23000) {
                Log::warning('Duplicate branch attempt', [
                    'data' => $request->all(),
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'status' => 'error',
                    'status_code' => 4090,
                    'message' => 'Branch already exists',
                    'error' => 'A branch with this ID already exists in the system'
                ], 409);
            }

            // Other database errors
            Log::error('Database error creating branch', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'message' => 'Failed to create branch',
                'error' => 'Database error occurred'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Unexpected error creating branch', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'message' => 'Failed to create branch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

  
    public function show($id)
    {
        try {
            $branch = Branch::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'status_code' => 2000,
                'message' => 'Branch details fetched successfully',
                'data' => $branch
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Branch not found', [
                'branch_id' => $id
            ]);

            return response()->json([
                'status' => 'error',
                'status_code' => 404,
                'message' => 'Branch not found',
                'error' => 'No branch exists with the specified ID'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error fetching branch', [
                'branch_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'message' => 'Failed to retrieve branch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    public function update(Request $request, $id)
    {
        try {
            $branch = Branch::findOrFail($id);

            // Store old data for logging
            $oldData = $branch->toArray();

            // Validate input
            $validated = $request->validate([
                'branch_id' => [
                    'sometimes',
                    'required',
                    'string',
                    Rule::unique('branches', 'branch_id')->ignore($branch->id)
                ],
                'branch_name' => 'sometimes|required|string|max:255',
                'location' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'staff_ids' => 'nullable|array',
                'staff_ids.*' => 'string|max:50',
            ]);

            // Update branch
            $branch->update($validated);

            // Log update
            Log::info('Branch updated successfully', [
                'branch_id' => $id,
                'old_data' => $oldData,
                'new_data' => $branch->fresh()->toArray(),
                'updated_by' => auth()->id() ?? 'system'
            ]);

            return response()->json([
                'status' => 'success',
                'status_code' => 2000,
                'message' => 'Branch updated successfully',
                'data' => $branch->fresh()
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Branch not found for update', [
                'branch_id' => $id
            ]);

            return response()->json([
                'status' => 'error',
                'status_code' => 404,
                'message' => 'Branch not found',
                'error' => 'No branch exists with the specified ID'
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            
            // Check for duplicate branch_id error
            if (isset($errors['branch_id'])) {
                foreach ($errors['branch_id'] as $error) {
                    if (str_contains($error, 'has already been taken')) {
                        return response()->json([
                            'status' => 'error',
                            'status_code' => 4090,
                            'message' => 'Branch already exists',
                            'error' => 'Another branch with this ID already exists',
                            'errors' => $errors
                        ], 409);
                    }
                }
            }

            // Other validation errors
            return response()->json([
                'status' => 'error',
                'status_code' => 422,
                'message' => 'Validation failed',
                'errors' => $errors
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error updating branch', [
                'branch_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'message' => 'Failed to update branch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    public function destroy($id)
    {
        try {
            $branch = Branch::findOrFail($id);
            
            // Store branch data before deletion for logging
            $deletedData = $branch->toArray();
            
            // Delete branch
            $branch->delete();

            // Log deletion
            Log::info('Branch deleted successfully', [
                'branch_id' => $id,
                'deleted_data' => $deletedData,
                'deleted_by' => auth()->id() ?? 'system'
            ]);

            return response()->json([
                'status' => 'success',
                'status_code' => 2000,
                'message' => 'Branch deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Branch not found for deletion', [
                'branch_id' => $id
            ]);

            return response()->json([
                'status' => 'error',
                'status_code' => 404,
                'message' => 'Branch not found',
                'error' => 'No branch exists with the specified ID'
            ], 404);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle foreign key constraint errors
            if ($e->getCode() == 23000) {
                Log::warning('Cannot delete branch due to constraints', [
                    'branch_id' => $id,
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'status' => 'error',
                    'status_code' => 409,
                    'message' => 'Cannot delete branch',
                    'error' => 'This branch is referenced by other records and cannot be deleted'
                ], 409);
            }

            Log::error('Database error deleting branch', [
                'branch_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'message' => 'Failed to delete branch',
                'error' => 'Database error occurred'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error deleting branch', [
                'branch_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'message' => 'Failed to delete branch',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}