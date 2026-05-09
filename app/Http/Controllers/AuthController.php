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
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $result = $this->userService->login($data['email'], $data['password']);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 401);
        }

        $token = JWTAuth::fromUser($result['user']);

       
        // Send login alert email
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
                'full_name' => $result['user']->full_name,
                'first_name' => $result['user']->first_name,
                'last_name' => $result['user']->last_name,
            ],
        ])->cookie(
            'token',        // Cookie name
            $token,         // The JWT token
            60,             // Minutes until expiration (60 = 1 hour)
            '/',            // Path - available on all routes
            null,           // Domain - current domain
            false,          // Secure - only send over HTTPS (set to false for local dev)
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

    // Send alert email
    try {
        Mail::to($user->email)->send(new PasswordChangedAlert($user));
    } catch (\Exception $e) {
        Log::error('Failed to send password change email: ' . $e->getMessage());
    }

    // Generate a NEW token with updated user data
    $token = JWTAuth::fromUser($user->fresh());   // ✅ renamed from $newToken

    // Return response WITH NEW cookie (now using $token)
    return response()->json([
        'message' => 'Password updated successfully.',
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'employee_id' => $user->employee_id,
            'must_change_password' => $user->must_change_password,
            'status' => $user->status,
            'full_name' => $user->full_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ],
    ])->cookie(
        'token',        // Cookie name
        $token,         // ✅ use $token (not $newToken)
        60,
        '/',
        null,
        false,
        true,
        false,
        'lax'
    );
}

public function createUser(CreateUserRequest $request)
{
    $data = $request->validated();

    try {
        $user = $this->userService->createUser($data);
        
        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
            ],
        ], 201);
        
    } catch (\InvalidArgumentException $e) {
        // Handle validation errors (like wrong domain)
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 422);
        
    } catch (\Exception $e) {
        // Handle other errors (cPanel failure, etc.)
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function getAllUsers(Request $request)
    {
        try {
            // Only select columns that actually exist in your table
            $users = User::all(['id', 'email', 'employee_id', 'status', 'must_change_password', 'last_login_at']);
            
            // Add full_name to each user
            $usersWithNames = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'email' => $user->email,
                    'employee_id' => $user->employee_id,
                    'status' => $user->status,
                    'must_change_password' => $user->must_change_password,
                    'last_login_at' => $user->last_login_at,
                    'full_name' => $user->full_name,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                ];
            });
            
            return response()->json([
                'success' => true,
                'users' => $usersWithNames
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
        $token = $request->cookie('token');
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'No token found'
            ], 401);
        }
        
        $user = JWTAuth::setToken($token)->authenticate();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'must_change_password' => $user->must_change_password,
                'status' => $user->status,
                'full_name' => $user->full_name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'last_login_at' => $user->last_login_at, // ADD THIS LINE
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

public function lockUser(Request $request)
{
    $request->validate(['user_id' => 'required|integer|exists:users,id']);
    $user = User::find($request->user_id);
    if ($user->status === 'locked') {
        return response()->json(['message' => 'User already locked.'], 400);
    }
    $user->status = 'locked';
    $user->save();
    return response()->json(['message' => 'User locked successfully.']);
}
}