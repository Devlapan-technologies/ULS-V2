<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    // Mass assignable attributes
    protected $fillable = [
        'email',
        'password',
        'must_change_password',
        'status',          // e.g., 'active', 'locked'
        'failed_attempts', // default 0
        'employee_id',     // ADD THIS LINE
    ];

    // Hidden attributes
    protected $hidden = [
        'password',
    ];

    // Dates
    protected $dates = [
        'last_login_at',
    ];

    // Default attribute values
    protected $attributes = [
        'status' => 'active',
        'failed_attempts' => 0,
        'must_change_password' => true,
    ];

    // Update last login
    public function updateLastLogin()
    {
        $this->last_login_at = now();
        $this->save();
    }

    // ===== JWT Methods =====
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}