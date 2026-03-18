<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UnlockUserRequest; // Add this line
use App\Models\User;
use App\Services\UserService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoginAlert;
use App\Mail\PasswordChangedAlert;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // ===== LOGIN =====
// In app/Http/Controllers/AuthController.php

public function login(LoginRequest $request)
{
    $data = $request->validated();

    $result = $this->userService->login($data['email'], $data['password']);

    if (!$result['success']) {
        return response()->json(['message' => $result['message']], 401);
    }

    $token = JWTAuth::fromUser($result['user']);

    // 👇 ADD THIS: Send login alert email
    try {
        Mail::to($result['user']->email)->send(new LoginAlert($result['user'], $request->ip()));
        Log::info('Login alert sent to: ' . $result['user']->email);
    } catch (\Exception $e) {
        // Log error but don't stop login
        Log::error('Failed to send login email: ' . $e->getMessage());
    }

    // Return response WITH cookie
    return response()->json([
        'message' => $result['message'],
        'user' => [
            'id' => $result['user']->id,
            'email' => $result['user']->email,
            'employee_id' => $result['user']->employee_id,
            'must_change_password' => $result['user']->must_change_password,
            'status' => $result['user']->status,
        ],
    ])->cookie(
        'token',        // Cookie name
        $token,         // The JWT token
        60,             // Minutes until expiration (60 = 1 hour)
        '/',            // Path - available on all routes
        null,           // Domain - current domain
        false,           // Secure - only send over HTTPS (set to false for local dev)
        true,           // HttpOnly - JavaScript CANNOT access this cookie
        false,          // Raw
        'lax'          // SameSite policy
    );
}

public function changePassword(ChangePasswordRequest $request)
{
    $data = $request->validated();

    $user = User::find($data['user_id']);

    $success = $this->userService->changePassword($user, $data['new_password']);

    if (!$success) {
        return response()->json(['message' => 'Password is weak or common.'], 400);
    }

    // 👇 ADD THIS: Send password change alert email
    try {
        Mail::to($user->email)->send(new PasswordChangedAlert($user));
        Log::info('Password change alert sent to: ' . $user->email);
    } catch (\Exception $e) {
        Log::error('Failed to send password change email: ' . $e->getMessage());
    }

    // 👇 Generate a NEW token with updated user data
    $newToken = JWTAuth::fromUser($user->fresh());

    // Return response WITH NEW cookie
    return response()->json([
        'message' => 'Password updated successfully.',
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'employee_id' => $user->employee_id,
            'must_change_password' => $user->must_change_password,
            'status' => $user->status,
        ],
    ])->cookie(
        'token',        // Cookie name
        $newToken,      // NEW token
        60,             // Minutes until expiration
        '/',            // Path
        null,           // Domain
        false,          // Secure
        true,           // HttpOnly
        false,          // Raw
        'lax'           // SameSite policy
    );
}



public function createUser(CreateUserRequest $request)
{
    $data = $request->validated();

    $user = $this->userService->createUser($data);

    return response()->json([
        'message' => 'User created successfully.',
        'user' => $user,
    ], 201);
}


public function getAllUsers(Request $request)
{
    try {
        // Only select columns that actually exist in your table
        $users = User::all(['id', 'email', 'employee_id', 'status', 'must_change_password', 'last_login_at']);
        
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch users: ' . $e->getMessage()
        ], 500);
    }
}



public function logout(Request $request)
{
    try {
        JWTAuth::invalidate(JWTAuth::getToken());
        
        // Clear the cookie by setting it to expire in the past
        return response()->json(['message' => 'Logged out successfully.'])
            ->cookie('token', '', -1); // Expire immediately
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to logout, token may be invalid.'], 400);
    }
}

    public function unlockUser(UnlockUserRequest $request)
{
    $user = User::find($request->user_id);
    
    $success = $this->userService->unlockUser($user);
    
    if (!$success) {
        return response()->json(['message' => 'User is not locked or could not be unlocked.'], 400);
    }
    
    return response()->json(['message' => 'User unlocked successfully.']);
}

public function me(Request $request)
{
    try {
        // Get token from cookie
        $token = $request->cookie('token');
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'No token found'
            ], 401);
        }
        
        // Authenticate user with token from cookie
        $user = JWTAuth::setToken($token)->authenticate();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        // Return user data
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'must_change_password' => $user->must_change_password,
                'status' => $user->status,
                'name' => $user->name ?? null, // if you have a name field
                'role' => $user->role ?? null, // if you have a role field
                // Add any other user fields you need
            ]
        ]);
        
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Token expired'
        ], 401);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Token invalid'
        ], 401);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Authentication failed: ' . $e->getMessage()
        ], 401);
    }
}
}