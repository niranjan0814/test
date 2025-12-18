<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\AuthController;

Route::get('/health', function () {
    return response()->json(['status' => 'API is healthy', 'service' => 'Laravel']);
});


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Super Admin Routes
    Route::middleware('is_super_admin')->group(function () {
        Route::post('/admins', [\App\Http\Controllers\Api\AdminController::class, 'store']);
        Route::get('/admins', [\App\Http\Controllers\Api\AdminController::class, 'index']);
        Route::put('/admins/{id}', [\App\Http\Controllers\Api\AdminController::class, 'update']);
    });

    // Admin Routes (both admin and super_admin can access)
    Route::middleware('is_admin')->group(function () {
        Route::post('/staffs', [\App\Http\Controllers\Api\StaffController::class, 'store']);
        Route::get('/staffs', [\App\Http\Controllers\Api\StaffController::class, 'index']);
        Route::put('/staffs/{staff_id}', [\App\Http\Controllers\Api\StaffController::class, 'update']);
    });
});
