<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HrApiService
{
    protected $hrApiUrl;

    public function __construct()
    {
        $this->hrApiUrl = env('HR_API_URL', 'http://127.0.0.1:2000/api');
        Log::info('HrApiService initialized with URL: ' . $this->hrApiUrl);
    }

    /**
     * Get employee details by employee_id
     */
    public function getEmployee($employeeId)
    {
        try {
            $url = "{$this->hrApiUrl}/employees/{$employeeId}";
            Log::info('Calling HR API: ' . $url);
            
            $response = Http::timeout(5)->get($url);
            
            Log::info('HR API response status: ' . $response->status());
            
            if ($response->successful()) {
                $data = $response->json();
                Log::info('HR API response data: ', $data);
                
                if (isset($data['success']) && $data['success'] === true) {
                    Log::info('Successfully fetched employee data');
                    return $data['data'] ?? null;
                }
                
                Log::warning('HR API returned success=false');
                return null;
            }
            
            Log::warning('HR API request failed with status: ' . $response->status());
            return null;
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch employee from HR API: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get employee name by employee_id
     */
    public function getEmployeeName($employeeId)
    {
        Log::info('getEmployeeName called for employee_id: ' . $employeeId);
        
        $employee = $this->getEmployee($employeeId);
        
        if ($employee && isset($employee['first_name']) && isset($employee['last_name'])) {
            $name = $employee['first_name'] . ' ' . $employee['last_name'];
            Log::info('Found employee name: ' . $name);
            return $name;
        }
        
        if ($employee) {
            Log::warning('Employee found but missing first_name or last_name', $employee);
        } else {
            Log::warning('Employee not found for ID: ' . $employeeId);
        }
        
        return null;
    }
}