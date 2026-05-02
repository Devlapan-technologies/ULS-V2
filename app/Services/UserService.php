<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    protected $commonPasswords = [
        '123456','password','qwerty','111111','123123','admin','letmein'
    ];
    
    protected $cpanelService;

    public function __construct(CpanelService $cpanelService)
    {
        $this->cpanelService = $cpanelService;
    }

    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        if ($user->status === 'locked') {
            return ['success' => false, 'message' => 'Account locked.'];
        }

        if (!Hash::check($password, $user->password)) {
            $user->failed_attempts = ($user->failed_attempts ?? 0) + 1;
            if ($user->failed_attempts >= 3) { 
                $user->status = 'locked';
            }
            $user->save();
            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        $user->failed_attempts = 0;
        $user->status = 'active';
        $user->updateLastLogin();

        if ($this->isWeakPassword($password)) {
            $user->must_change_password = true;
            $user->save();
            return ['success' => true, 'message' => 'Please change your password.', 'user' => $user];
        }

        return ['success' => true, 'message' => 'Login successful.', 'user' => $user];
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



    public function isWeakPassword(string $password): bool
    {
        $weak = strlen($password) < 8 ||
                !preg_match('/[a-z]/', $password) ||
                !preg_match('/[A-Z]/', $password) ||
                !preg_match('/[0-9]/', $password) ||
                !preg_match('/[@$!%*#?&]/', $password);

        return $weak || in_array(strtolower($password), $this->commonPasswords);
    }

    public function changePassword(User $user, string $newPassword): bool
    {
        if ($this->isWeakPassword($newPassword)) {
            return false;
        }

        // Update database
        $user->password = bcrypt($newPassword);
        $user->must_change_password = false;
        $user->save();

        // Update cPanel email password
        $this->cpanelService->changeEmailPassword($user->email, $newPassword);

        return true;
    }

    public function unlockUser(User $user): bool
    {
        if ($user->status !== 'locked') {
            return false;
        }

        $user->status = 'active';
        $user->failed_attempts = 0;
        $user->save();

        return true;
    }

public function createUser(array $data)
{
    // Validate email domain
    if (!isset($data['email']) || !str_ends_with($data['email'], '@devlapan.com')) {
        throw new \InvalidArgumentException('Only @devlapan.com email addresses are allowed. Please use a company email.');
    }
    
    if (!isset($data['employee_id'])) {
        throw new \InvalidArgumentException('Employee ID is required');
    }

    \DB::beginTransaction();
    
    try {
        // Create user instance
        $user = new User();
        
        // Set custom ID if provided
        if (isset($data['user_id'])) {
            $user->id = $data['user_id'];
        }
        
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->must_change_password = true;
        $user->status = 'active';
        $user->failed_attempts = 0;
        $user->employee_id = $data['employee_id'];
        $user->save();

        // Create email account in cPanel
        $cpanelResult = $this->cpanelService->createEmailAccount($data['email'], $data['password']);
        
        if (!$cpanelResult['success']) {
            \DB::rollBack();
            throw new \Exception($cpanelResult['message']);
        }
        
        \DB::commit();
        return $user;
        
    } catch (\Exception $e) {
        \DB::rollBack();
        throw $e;
    }
}
}