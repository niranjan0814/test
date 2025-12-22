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
                'statusCode' => 4010,
                'message' => 'Invalid username or password'
            ], 401);
        }

        // 2. Check if account is active

        if (!$user->is_active) {
            // Check if blocked by Super Admin (Admin role, failed attempts < 3)
            // This implies manual block rather than auto-lockout
            if ($user->role === 'admin' && $user->failed_login_attempts < 3) {
                 return response()->json([
                     'statusCode' => 4230,
                     'message' => 'Super Admin blocked your account. contact please'
                 ], 423);
            }

            return response()->json([
                'statusCode' => 4230,
                'message' => 'Account locked due to multiple failed attempts'
            ], 423);
        }

        // 3. Verify Password
        if (!Hash::check($request->password, $user->password)) {
            // Increment failed attempts
            $user->increment('failed_login_attempts');

            // Check thresholds
            if ($user->failed_login_attempts >= 3) {
                $user->update(['is_active' => false]);
                return response()->json([
                    'statusCode' => 4230,
                    'message' => 'Account locked due to multiple failed attempts'
                ], 423);
            }

            if ($user->failed_login_attempts == 2) {
                return response()->json([
                    'statusCode' => 4010,
                    'message' => 'Invalid credentials. You have only one attempt more'
                ], 401);
            }

            return response()->json([
                'statusCode' => 4010,
                'message' => 'Invalid username or password'
            ], 401);
        }

        // 4. Success: Reset failed attempts logic
        $user->update(['failed_login_attempts' => 0]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'statusCode' => 2000,
            'message' => 'Login successful',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]
        ]);
    }

    public function logout(Request $request)
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'statusCode' => 4010,
                'message' => 'Session expired. Please login again'
            ], 401);
        }

        // Delete the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'statusCode' => 2000,
            'message' => 'Logout successful'
        ], 200);
    }

    /**
     * Get authenticated user profile
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'statusCode' => 4010,
                'message' => 'Session expired. Please login again'
            ], 401);
        }

        $user = $request->user();

        // Check if user profile exists
        if (!$user) {
            return response()->json([
                'statusCode' => 4040,
                'message' => 'User profile not found'
            ], 404);
        }

        return response()->json([
            'statusCode' => 2000,
            'message' => 'Profile details fetched successfully',
            'data' => [
                'user' => $user
            ]
        ], 200);
    }
}
