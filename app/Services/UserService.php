<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $commonPasswords = [
        '123456','password','qwerty','111111','123123','admin','letmein'
        // you can extend this list
    ];

    // Attempt login
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        // Check if account is locked
        if ($user->status === 'locked') {
            return ['success' => false, 'message' => 'Account locked due to too many failed login attempts.'];
        }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            $user->failed_attempts = ($user->failed_attempts ?? 0) + 1;

            // Lock account after 3 failed attempts
            if ($user->failed_attempts >= 3) { 
                $user->status = 'locked';
            }

            $user->save();
            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        // Reset failed attempts on successful login
        $user->failed_attempts = 0;
        $user->status = 'active';
        $user->updateLastLogin();

        // Check password strength
        if ($this->isWeakPassword($password)) {
            $user->must_change_password = true;
            $user->save();

            return ['success' => true, 'message' => 'Password weak, please change your password.', 'user' => $user];
        }

        return ['success' => true, 'message' => 'Login successful.', 'user' => $user];
    }

    // Check password strength & common passwords
    public function isWeakPassword(string $password): bool
    {
        $weak = strlen($password) < 8 ||
                !preg_match('/[a-z]/', $password) ||
                !preg_match('/[A-Z]/', $password) ||
                !preg_match('/[0-9]/', $password) ||
                !preg_match('/[@$!%*#?&]/', $password);

        $common = in_array(strtolower($password), $this->commonPasswords);

        return $weak || $common;
    }

    // Force password change
    public function changePassword(User $user, string $newPassword): bool
    {
        if ($this->isWeakPassword($newPassword)) {
            return false; // cannot set weak password
        }

        $user->password = bcrypt($newPassword);
        $user->must_change_password = false;
        $user->save();

        return true;
    }

    public function unlockUser(User $user): bool
    {
        if ($user->status !== 'locked') {
            return false; // nothing to unlock
        }

        $user->status = 'active';
        $user->failed_attempts = 0;
        $user->save();

        return true;
    }

public function createUser(array $data)
{
    // Check if employee_id is provided
    if (!isset($data['employee_id'])) {
        throw new \InvalidArgumentException('Employee ID is required');
    }

    $user = User::create([
        'email' => $data['email'],
        'password' => bcrypt($data['password']),
        'must_change_password' => true,
        'status' => 'active',
        'failed_attempts' => 0,
        'employee_id' => $data['employee_id'],
    ]);

    return $user;
}
}