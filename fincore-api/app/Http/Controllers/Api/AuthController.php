<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', [
            'except' => ['login']
        ]);
    }

    /**
     * Login using username OR email
     * Lock account after 3 failed attempts
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            // Find by username OR email
            $user = User::where('user_name', $request->login)
                ->orWhere('email', $request->login)
                ->first();

            if (!$user) {
                return $this->errorResponse(4010, 'Invalid username or password', 401);
            }

            // Manual admin block
            if (!$user->is_active || $user->is_locked) {
                return $this->errorResponse(
                    4230,
                    'Account is blocked. Please contact administrator',
                    423
                );
            }

            // Password validation
            if (!Hash::check($request->password, $user->password)) {
                $user->increment('failed_login_attempts');

                if ($user->failed_login_attempts >= 3) {
                    $user->update([
                        'is_active' => false,
                        'is_locked' => true,
                    ]);

                    return $this->errorResponse(
                        4230,
                        'Account locked due to multiple failed attempts',
                        423
                    );
                }

                return $this->errorResponse(
                    4010,
                    'Invalid credentials. You have only ' . (3 - $user->failed_login_attempts) . ' attempt(s) left',
                    401
                );
            }

            // Successful login
        $user->update([
            'failed_login_attempts' => 0,
            'is_locked' => false,
        ]);

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Load roles and permissions
        $user->load(['roles.permissions', 'permissions']);

        // Get role and permission data
        $roles = $user->roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'level' => $role->level,
                'hierarchy' => $role->hierarchy,
            ];
        });

        $permissions = $user->getAllPermissions()->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $permission->display_name,
                'module' => $permission->module,
            ];
        });

        return response()->json([
            'statusCode' => 2000,
            'message' => 'Login successful',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'roles' => $roles,
                'permissions' => $permissions,
            ]
        ], 200);

        } catch (\Exception $e) {
            Log::error('Login failed', [
                'login' => $request->login,
                'ip' => $request->ip(),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(5000, 'Authentication failed', 500);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse(4010, 'Session expired. Please login again', 401);
        }

        // Load roles and permissions
        $user->load(['roles.permissions', 'permissions']);

        // Get role and permission data
        $roles = $user->roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'level' => $role->level,
                'hierarchy' => $role->hierarchy,
            ];
        });

        $permissions = $user->getAllPermissions()->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $permission->display_name,
                'module' => $permission->module,
            ];
        });

        return response()->json([
            'statusCode' => 2000,
            'message' => 'Profile fetched successfully',
            'data' => [
                'user' => $user,
                'roles' => $roles,
                'permissions' => $permissions,
            ]
        ], 200);
    }

    /**
     * Logout (revoke token)
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse(4010, 'Session expired. Please login again', 401);
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            'statusCode' => 2000,
            'message' => 'Logout successful'
        ], 200);
    }

    /**
     * Check single permission
     */
    public function checkPermission(Request $request)
    {
        $request->validate([
            'permission' => 'required|string'
        ]);

        $user = Auth::user();

        return response()->json([
            'statusCode' => 2000,
            'message' => 'Permission checked',
            'data' => [
                'permission' => $request->permission,
                'has_permission' => $user->hasPermissionTo($request->permission),
            ]
        ]);
    }

    /**
     * Check multiple permissions
     */
    public function checkAnyPermission(Request $request)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string'
        ]);

        $user = Auth::user();

        $matched = collect($request->permissions)
            ->filter(fn ($p) => $user->hasPermissionTo($p))
            ->values();

        return response()->json([
            'statusCode' => 2000,
            'message' => 'Permissions checked',
            'data' => [
                'has_any_permission' => $matched->isNotEmpty(),
                'matched_permissions' => $matched,
            ]
        ]);
    }

    /**
     * Unified error response helper
     */
    private function errorResponse(int $code, string $message, int $httpCode)
    {
        return response()->json([
            'statusCode' => $code,
            'message' => $message,
        ], $httpCode);
    }
}
