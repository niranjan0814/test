<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    /**
     * Create a new Staff (Admin only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email_id' => 'required|email|unique:staffs,email_id',
            'account_status' => 'required|string',
            'contact_no' => 'required|string',
            'full_name' => 'required|string',
            'name_with_initial' => 'required|string',
            'address' => 'required|string',
            'nic' => 'required|string',
            'work_info' => 'required|json',
            'age' => 'required|integer',
            'profile_image' => 'required|string',
            'gender' => 'required|string',
        ]);

        // Auto-generate Staff ID (ST + 4 digits)
        $latestStaff = Staff::where('staff_id', 'LIKE', 'ST%')
                           ->orderByRaw('LENGTH(staff_id) DESC')
                           ->orderBy('staff_id', 'desc')
                           ->first();

        if ($latestStaff) {
            $lastNumber = intval(substr($latestStaff->staff_id, 2));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Format: ST0001, ST0002, ...
        $newStaffId = 'ST' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        // Use transaction to ensure both tables are updated together
        DB::beginTransaction();
        try {
            // Create staff record
            $staff = Staff::create([
                'staff_id' => $newStaffId,
                'email_id' => $validated['email_id'],
                'account_status' => $validated['account_status'],
                'contact_no' => $validated['contact_no'],
                'full_name' => $validated['full_name'],
                'name_with_initial' => $validated['name_with_initial'],
                'address' => $validated['address'],
                'nic' => $validated['nic'],
                'work_info' => $validated['work_info'],
                'age' => $validated['age'],
                'profile_image' => $validated['profile_image'],
                'gender' => $validated['gender'],
            ]);

            // Create corresponding user record
            $user = User::create([
                'user_name' => $newStaffId, // staff_id becomes user_name
                'email' => $validated['email_id'], // email_id becomes email
                'password' => 'default123', // Default password, staff should change it
                'role' => null, // Staff users have no role
                'digital_signature' => Hash::make($newStaffId),
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Staff created successfully',
                'data' => [
                    'staff' => $staff,
                    'user' => $user
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create staff: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all staff members.
     */
    public function index()
    {
        $staffs = Staff::all();
        return response()->json([
            'status' => 'success',
            'data' => $staffs
        ]);
    }

    /**
     * Update staff details.
     */
    public function update(Request $request, $staff_id)
    {
        $staff = Staff::findOrFail($staff_id);

        $validated = $request->validate([
            'email_id' => 'nullable|email|unique:staffs,email_id,' . $staff_id . ',staff_id',
            'account_status' => 'nullable|string',
            'contact_no' => 'nullable|string',
            'full_name' => 'nullable|string',
            'name_with_initial' => 'nullable|string',
            'address' => 'nullable|string',
            'nic' => 'nullable|string',
            'work_info' => 'nullable|json',
            'age' => 'nullable|integer',
            'profile_image' => 'nullable|string',
            'gender' => 'nullable|string',
            'monthly_target_amount' => 'nullable|numeric',
            'monthly_target_count' => 'nullable|integer',
            'basic_salary' => 'nullable|numeric',
            'branch_id' => 'nullable|integer',
            'center_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            // Update staff
            $staff->update($validated);

            // Update corresponding user if email changed
            if (isset($validated['email_id'])) {
                $user = User::where('user_name', $staff_id)->first();
                if ($user) {
                    $user->email = $validated['email_id'];
                    $user->save();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Staff updated successfully',
                'data' => $staff
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update staff: ' . $e->getMessage()
            ], 500);
        }
    }
}
