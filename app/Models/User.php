<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Services\HrApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    // Mass assignable attributes
    protected $fillable = [
        'email',
        'password',
        'must_change_password',
        'status',
        'failed_attempts',
        'employee_id',
    ];

    // Hidden attributes
    protected $hidden = [
        'password',
    ];

    // Dates
    protected $casts = [
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Default attribute values
    protected $attributes = [
        'status' => 'active',
        'failed_attempts' => 0,
        'must_change_password' => true,
    ];

    // Append virtual attributes to JSON
    protected $appends = ['full_name', 'first_name', 'last_name'];

    /**
     * Update last login timestamp
     */
    public function updateLastLogin()
    {
        $this->last_login_at = now();
        $this->save();
    }

    /**
     * Get employee full name from HR API (with caching)
     */
    public function getFullNameAttribute()
    {
        Log::info('getFullNameAttribute called for employee_id: ' . $this->employee_id);
        
        if (!$this->employee_id) {
            Log::info('No employee_id, returning email: ' . $this->email);
            return $this->email;
        }

        // Check cache first
        $cacheKey = "employee_name_{$this->employee_id}";
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            Log::info('Returning cached name for employee ' . $this->employee_id . ': ' . $cached);
            return $cached;
        }
        
        Log::info('Cache miss - calling HR API for employee: ' . $this->employee_id);
        
        try {
            $hrService = app(HrApiService::class);
            $name = $hrService->getEmployeeName($this->employee_id);
            
            if ($name) {
                Log::info('HR API returned name: ' . $name);
                Cache::put($cacheKey, $name, 86400);
                return $name;
            }
            
            Log::warning('HR API returned null for employee: ' . $this->employee_id);
            return $this->email;
            
        } catch (\Exception $e) {
            Log::error('Error fetching from HR API: ' . $e->getMessage());
            return $this->email;
        }
    }

    /**
     * Get first name only
     */
    public function getFirstNameAttribute()
    {
        $fullName = $this->full_name;
        
        // If it's an email (fallback), return email username
        if ($fullName === $this->email) {
            $username = explode('@', $this->email)[0];
            Log::info('getFirstNameAttribute returning email username: ' . $username);
            return $username;
        }
        
        // Return first part of the full name
        $firstName = explode(' ', $fullName)[0] ?? $this->email;
        Log::info('getFirstNameAttribute returning: ' . $firstName);
        return $firstName;
    }

    /**
     * Get last name only
     */
    public function getLastNameAttribute()
    {
        $fullName = $this->full_name;
        
        if ($fullName === $this->email) {
            return '';
        }
        
        $parts = explode(' ', $fullName);
        return count($parts) > 1 ? end($parts) : '';
    }

    /**
     * Clear cached employee name (call when employee is updated)
     */
    public function clearEmployeeNameCache()
    {
        if ($this->employee_id) {
            Cache::forget("employee_name_{$this->employee_id}");
            Log::info('Cleared cache for employee: ' . $this->employee_id);
        }
    }

    // ===== JWT Methods =====
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'email' => $this->email,
            'employee_id' => $this->employee_id,
        ];
    }
}