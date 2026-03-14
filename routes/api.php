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

    // Logout
    Route::post('logout', [AuthController::class, 'logout']);

    // ===== ADMIN ROUTES =====
    Route::prefix('admin')->group(function () {
        // Create a new internal user linked to an employee
        Route::post('create-user', [AuthController::class, 'createUser']);
        
        // Unlock a locked user
        Route::post('unlock-user', [AuthController::class, 'unlockUser']);
    });
});


use Illuminate\Support\Facades\Mail;

Route::get('/test-email', function() {
    try {
        Mail::raw('This is a test email from your Laravel API', function($message) {
            $message->to('luswenyomafenyeho@gmail.com') // Use YOUR email here
                    ->subject('Test Email from ' . config('app.name'));
        });
        return response()->json([
            'success' => true, 
            'message' => 'Test email sent successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false, 
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});