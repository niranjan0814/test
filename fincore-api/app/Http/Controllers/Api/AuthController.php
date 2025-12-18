<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // Can be user_name or email
            'password' => 'required|string',
        ]);

        // Try to find user by user_name OR email
        $user = User::where('user_name', $request->login)
                    ->orWhere('email', $request->login)
                    ->first();

        // 1. Check if user exists
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid username or password'
            ], 401);
        }

        // 2. Check if account is active
        if (!$user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account has been disabled. Please contact support.'
            ], 403);
        }

        // 3. Verify Password
        if (!Hash::check($request->password, $user->password)) {
            // Increment failed attempts
            $user->increment('failed_login_attempts');

            // Check thresholds
            if ($user->failed_login_attempts >= 3) {
                $user->update(['is_active' => false]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account disabled due to multiple failed login attempts'
                ], 403);
            }

            if ($user->failed_login_attempts == 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials. You have only one attempt more'
                ], 401);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid username or password'
            ], 401);
        }

        // 4. Success: Reset failed attempts logic
        $user->update(['failed_login_attempts' => 0]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }
}
