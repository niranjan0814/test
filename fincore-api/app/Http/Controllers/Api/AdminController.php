<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Create a new Admin user (Super Admin only).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        // Auto-generate User Name (AD + 4 digits)
        $latestAdmin = User::where('user_name', 'LIKE', 'AD%')
                           ->orderByRaw('LENGTH(user_name) DESC') // Ensure standard length sorting
                           ->orderBy('user_name', 'desc')
                           ->first();

        if ($latestAdmin) {
            // Extract the number part (assuming ADxxxx format)
            $lastNumber = intval(substr($latestAdmin->user_name, 2));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Format: AD0001, AD0002, ...
        $newUserName = 'AD' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        $admin = User::create([
            'user_name' => $newUserName,
            'email' => $validated['email'],
            'password' => $validated['password'], // Casts to hashed in Model
            'role' => 'admin',
            'digital_signature' => Hash::make($newUserName), // Auto-generated from user_name
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Admin created successfully',
            'data' => $admin
        ], 201);
    }

    /**
     * List all admins (Optional, for management UI).
     */
    public function index()
    {
        $admins = User::where('role', 'admin')->get();
        return response()->json([
            'status' => 'success',
            'data' => $admins
        ]);
    }

    /**
     * Toggle Admin Status (Active/Inactive).
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = User::where('role', 'admin')->findOrFail($id);

        $user->is_active = !$user->is_active;
        if ($user->is_active) {
            $user->failed_login_attempts = 0; // Reset failures on reactivation
        }
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'status' => 'success',
            'message' => "Admin user {$status} successfully",
            'data' => [
                'id' => $user->id,
                'user_name' => $user->user_name,
                'is_active' => $user->is_active
            ]
        ]);
    }
}
