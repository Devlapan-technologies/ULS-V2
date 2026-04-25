<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CpanelService
{
    protected $cpanelHost;
    protected $cpanelUser;
    protected $cpanelToken;

    public function __construct()
    {
        $this->cpanelHost = env('CPANEL_HOST');
        $this->cpanelUser = env('CPANEL_USER');
        $this->cpanelToken = env('CPANEL_TOKEN');
    }

    /**
     * Create an email account in cPanel
     */
    public function createEmailAccount($email, $password)
    {
        list($username, $domain) = explode('@', $email);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'cpanel ' . $this->cpanelUser . ':' . $this->cpanelToken,
            ])->post("https://{$this->cpanelHost}:2083/execute/Email/add_pop", [
                'email' => $username,
                'domain' => $domain,
                'password' => $password,
                'quota' => 1024,
            ]);

            $result = $response->json();
            
            Log::info('cPanel create email response:', ['result' => $result, 'status' => $response->status()]);

            // Check if the email was created successfully
            $isSuccess = false;
            
            // Case 1: Response is a string containing "OK"
            if (is_string($result) && strpos($result, 'OK') !== false) {
                $isSuccess = true;
            }
            
            // Case 2: Response is array with "OK" in first position
            if (is_array($result) && count($result) > 0) {
                if ((isset($result[0]) && $result[0] === "OK") || 
                    (isset($result['result']) && $result['result'] === 1) ||
                    (isset($result['messages']) && in_array('OK', $result['messages']))) {
                    $isSuccess = true;
                }
            }
            
            // Case 3: HTTP status is 200 and response is not empty
            if ($response->successful()) {
                // If we got a response, it's likely success since cPanel returns 200 even for failures
                // But we check for error indicators
                $hasError = false;
                if (is_string($result) && (strpos($result, 'error') !== false || strpos($result, 'invalid') !== false)) {
                    $hasError = true;
                }
                if (!$hasError) {
                    $isSuccess = true;
                }
            }

            if ($isSuccess) {
                Log::info("Email account created: {$email}");
                return ['success' => true, 'message' => 'Email account created'];
            }

            // Extract error message
            $error = 'Unknown error';
            if (is_string($result)) {
                $error = $result;
            } elseif (is_array($result)) {
                if (isset($result[1]) && !empty($result[1]) && $result[1] !== "") {
                    $error = $result[1];
                } elseif (isset($result['errors'])) {
                    $error = is_array($result['errors']) ? implode(', ', $result['errors']) : $result['errors'];
                } elseif (isset($result['message'])) {
                    $error = $result['message'];
                }
            }

            Log::error("cPanel email creation failed for {$email}: " . $error);
            return ['success' => false, 'message' => $error];

        } catch (\Exception $e) {
            Log::error("cPanel API error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Change password for email account in cPanel
     */
    public function changeEmailPassword($email, $newPassword)
    {
        list($username, $domain) = explode('@', $email);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'cpanel ' . $this->cpanelUser . ':' . $this->cpanelToken,
            ])->post("https://{$this->cpanelHost}:2083/execute/Email/passwd_pop", [
                'email' => $username,
                'domain' => $domain,
                'password' => $newPassword,
            ]);

            $result = $response->json();
            
            Log::info('cPanel change password response:', ['result' => $result]);

            $isSuccess = false;
            
            if (is_string($result) && strpos($result, 'OK') !== false) {
                $isSuccess = true;
            }
            
            if (is_array($result) && count($result) > 0) {
                if ((isset($result[0]) && $result[0] === "OK") || 
                    (isset($result['result']) && $result['result'] === 1)) {
                    $isSuccess = true;
                }
            }
            
            if ($response->successful() && !isset($result['result']) && !isset($result['errors'])) {
                $isSuccess = true;
            }

            if ($isSuccess) {
                Log::info("Email password changed: {$email}");
                return ['success' => true, 'message' => 'Password updated'];
            }

            $error = 'Unknown error';
            if (is_string($result)) {
                $error = $result;
            } elseif (is_array($result)) {
                if (isset($result[1]) && !empty($result[1])) {
                    $error = $result[1];
                } elseif (isset($result['errors'])) {
                    $error = is_array($result['errors']) ? implode(', ', $result['errors']) : $result['errors'];
                }
            }

            Log::error("cPanel password change failed for {$email}: " . $error);
            return ['success' => false, 'message' => $error];

        } catch (\Exception $e) {
            Log::error("cPanel API error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}