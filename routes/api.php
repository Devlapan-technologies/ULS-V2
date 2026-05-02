<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes for the internal tool with JWT authentication.
| - Public routes: login
| - Protected routes: change password, logout
| - Admin routes: create user, unlock user
|
*/

// ===== PUBLIC ROUTES =====
Route::post('login', [AuthController::class, 'login']);

// Get current authenticated user info
    Route::get('user', [AuthController::class, 'me']);

// ===== PROTECTED ROUTES =====
Route::middleware(['auth:api'])->group(function () {
    // Change own password
    Route::post('change-password', [AuthController::class, 'changePassword']);

    Route::post('admin/lock-user', [AuthController::class, 'lockUser']);
    // Logout
    Route::post('logout', [AuthController::class, 'logout']);

    // 👇 ADD THIS: Get all users (for Access Control)
    Route::get('users', [AuthController::class, 'getAllUsers']);

    // ===== ADMIN ROUTES =====
    Route::prefix('admin')->group(function () {
        // Create a new internal user linked to an employee
        Route::post('create-user', [AuthController::class, 'createUser']);
        
        // Unlock a locked user
        Route::post('unlock-user', [AuthController::class, 'unlockUser']);
    });
});


