<?php
// security-test.php - Complete security testing

echo "🔐 SECURITY TEST SUITE\n";
echo "=====================\n\n";

$baseUrl = 'http://127.0.0.1:8000/api';
$results = [];
$testsPassed = 0;
$testsFailed = 0;

function runTest($name, $callback) {
    global $results, $testsPassed, $testsFailed;
    echo "Testing: $name... ";
    $result = $callback();
    if ($result['success']) {
        echo "✅ PASSED\n";
        $testsPassed++;
    } else {
        echo "❌ FAILED - {$result['message']}\n";
        $testsFailed++;
    }
    $results[] = [
        'test' => $name,
        'result' => $result
    ];
}

function makeRequest($url, $method = 'POST', $data = null, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $defaultHeaders = ['Content-Type: application/json'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

// ============================================
// TEST 1: RATE LIMITING
// ============================================
runTest('Rate Limiting (50 per minute)', function() use ($baseUrl) {
    $blockedAt = null;
    
    for ($i = 1; $i <= 55; $i++) {
        $response = makeRequest("$baseUrl/login", 'POST', [
            'email' => 'test@example.com',
            'password' => 'wrong'
        ]);
        
        if ($response['code'] == 429) {
            $blockedAt = $i;
            break;
        }
    }
    
    if ($blockedAt == 51) {
        return ['success' => true, 'message' => "Blocked at $blockedAt requests (correct)"];
    } else {
        return ['success' => false, 'message' => "Blocked at $blockedAt, expected 51"];
    }
});

// ============================================
// TEST 2: ACCOUNT LOCKOUT (3 failed attempts)
// ============================================
runTest('Account Lockout after 3 failures', function() use ($baseUrl) {
    // Try to create a test user first
    $email = 'locktest_' . time() . '@example.com';
    
    // Make 3 failed login attempts
    for ($i = 1; $i <= 3; $i++) {
        $response = makeRequest("$baseUrl/login", 'POST', [
            'email' => $email,
            'password' => 'wrongpassword'
        ]);
    }
    
    // 4th attempt should still fail but not lock further
    $response = makeRequest("$baseUrl/login", 'POST', [
        'email' => $email,
        'password' => 'wrongagain'
    ]);
    
    // Check if response indicates locked account
    $body = $response['body'];
    $isLocked = isset($body['message']) && strpos($body['message'], 'locked') !== false;
    
    return ['success' => $isLocked, 'message' => $body['message'] ?? 'No lock message'];
});

// ============================================
// TEST 3: PASSWORD STRENGTH VALIDATION
// ============================================
runTest('Password Strength Validation', function() use ($baseUrl) {
    $weakPasswords = [
        'password' => 'Common password',
        '123456' => 'Simple numbers',
        'short' => 'Too short',
        'onlylowercase' => 'No uppercase',
        'ONLYUPPERCASE' => 'No lowercase',
        'NoNumbers!' => 'No numbers',
        '1234ABCD' => 'No special chars',
    ];
    
    $failedTests = [];
    
    foreach ($weakPasswords as $password => $reason) {
        // Need a real user ID - this assumes user 1 exists
        $response = makeRequest("$baseUrl/change-password", 'POST', [
            'user_id' => 1,
            'new_password' => $password
        ]);
        
        // Should get 400 or 422 for weak password
        if (!in_array($response['code'], [400, 422])) {
            $failedTests[] = "$password ($reason) returned {$response['code']}";
        }
    }
    
    if (empty($failedTests)) {
        return ['success' => true, 'message' => 'All weak passwords rejected'];
    } else {
        return ['success' => false, 'message' => implode(', ', $failedTests)];
    }
});

// ============================================
// TEST 4: SQL INJECTION PROTECTION
// ============================================
runTest('SQL Injection Protection', function() use ($baseUrl) {
    $injections = [
        "test@example.com' OR '1'='1",
        "admin'--",
        "test@example.com' UNION SELECT * FROM users--",
        "'; DROP TABLE users; --"
    ];
    
    $vulnerable = false;
    
    foreach ($injections as $injection) {
        $response = makeRequest("$baseUrl/login", 'POST', [
            'email' => $injection,
            'password' => 'anything'
        ]);
        
        // Should still return 401/422, not 500 or success
        if ($response['code'] == 200) {
            $vulnerable = true;
            break;
        }
    }
    
    return ['success' => !$vulnerable, 'message' => $vulnerable ? 'Possible SQL injection' : 'Protected'];
});

// ============================================
// TEST 5: XSS PROTECTION
// ============================================
runTest('XSS Protection', function() use ($baseUrl) {
    $xssPayloads = [
        '<script>alert(1)</script>',
        '"><script>alert(1)</script>',
        '<img src=x onerror=alert(1)>',
        'javascript:alert(1)'
    ];
    
    $xssReflected = false;
    
    foreach ($xssPayloads as $payload) {
        $response = makeRequest("$baseUrl/login", 'POST', [
            'email' => $payload,
            'password' => $payload
        ]);
        
        $body = json_encode($response['body']);
        if (strpos($body, '<script>') !== false || strpos($body, 'onerror=') !== false) {
            $xssReflected = true;
            break;
        }
    }
    
    return ['success' => !$xssReflected, 'message' => $xssReflected ? 'XSS possible' : 'Protected'];
});

// ============================================
// TEST 6: TOKEN AUTHENTICATION
// ============================================
runTest('Token Authentication', function() use ($baseUrl) {
    // Try accessing protected route without token
    $response = makeRequest("$baseUrl/logout", 'POST');
    
    if ($response['code'] == 401) {
        return ['success' => true, 'message' => 'Protected route blocked'];
    } else {
        return ['success' => false, 'message' => "Protected route returned {$response['code']}"];
    }
});

// ============================================
// TEST 7: RAPID FIRE ATTACK SIMULATION
// ============================================
runTest('Rapid Fire Attack Protection', function() use ($baseUrl) {
    $start = microtime(true);
    $successCount = 0;
    
    // Send 60 requests as fast as possible
    for ($i = 1; $i <= 60; $i++) {
        $response = makeRequest("$baseUrl/login", 'POST', [
            'email' => 'test@example.com',
            'password' => 'wrong'
        ]);
        
        if ($response['code'] != 429) {
            $successCount++;
        }
        
        // Don't wait between requests
    }
    
    $time = microtime(true) - $start;
    
    if ($successCount <= 50) {
        return ['success' => true, 'message' => "Only $successCount succeeded in {$time}s"];
    } else {
        return ['success' => false, 'message' => "$successCount succeeded, rate limiting may be weak"];
    }
});

// ============================================
// TEST 8: PASSWORD RESET WITH COMMON PASSWORD
// ============================================
runTest('Common Password Blocking', function() use ($baseUrl) {
    $commonPasswords = ['password', '123456', 'qwerty', 'admin', 'letmein'];
    
    $blocked = true;
    
    foreach ($commonPasswords as $password) {
        $response = makeRequest("$baseUrl/change-password", 'POST', [
            'user_id' => 1,
            'new_password' => $password
        ]);
        
        if (!in_array($response['code'], [400, 422])) {
            $blocked = false;
            break;
        }
    }
    
    return ['success' => $blocked, 'message' => $blocked ? 'Common passwords blocked' : 'Common password accepted'];
});

// ============================================
// TEST 9: BRUTE FORCE PROTECTION
// ============================================
runTest('Brute Force Protection', function() use ($baseUrl) {
    $email = 'test@example.com';
    $attempts = 10;
    $blocked = false;
    
    for ($i = 1; $i <= $attempts; $i++) {
        $response = makeRequest("$baseUrl/login", 'POST', [
            'email' => $email,
            'password' => "wrong$i"
        ]);
        
        $body = $response['body'];
        if (isset($body['message']) && strpos($body['message'], 'locked') !== false) {
            $blocked = true;
            break;
        }
    }
    
    return ['success' => $blocked, 'message' => $blocked ? 'Account locked after attempts' : 'Account not locked'];
});

// ============================================
// SUMMARY
// ============================================
echo "\n\n📊 SECURITY TEST SUMMARY\n";
echo "=======================\n";
echo "✅ Passed: $testsPassed\n";
echo "❌ Failed: $testsFailed\n";
echo "📈 Security Score: " . round(($testsPassed / ($testsPassed + $testsFailed)) * 100) . "%\n\n";

foreach ($results as $r) {
    $icon = $r['result']['success'] ? '✅' : '❌';
    echo "$icon {$r['test']}: {$r['result']['message']}\n";
}

echo "\n";