<?php
// Simple curl request to test connecting to the production server
function test_post($url, $data = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL verify just for testing connectivity
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $http_code, 'body' => $response];
}

$baseUrl = "https://picme225.site";

// Test 1: Simple GET to API health or root
echo "Testing connectivity to $baseUrl...\n";
$ch = curl_init($baseUrl . "/api/user/services"); // public endpoint
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "GET /api/user/services: HTTP $code | Response size: " . strlen($res) . " bytes\n";

// Test 2: OAuth token endpoint for driver app
$driverData = [
    'grant_type' => 'password',
    'client_id' => '4',
    'client_secret' => 'YHrrmLiTzK51sFR2huce8MrzA9lhI0rsUMRxDW9L',
    'username' => 'test@test.com',
    'password' => 'secret',
    'scope' => '',
];
echo "\nTesting Driver OAuth endpoint (/api/provider/oauth/token)...\n";
$res = test_post($baseUrl . "/api/provider/oauth/token", $driverData);
echo "POST /api/provider/oauth/token: HTTP {$res['code']}\n";
echo "Response: " . $res['body'] . "\n";

// Test 3: Unified login endpoint for user app
$userData = [
    'client_id' => '3',
    'client_secret' => '3XunnpG2kTZPOHQA9aF9M49Q9jQWKcxCwz1W9oRJ',
];
echo "\nTesting User login endpoint (/api/user/unified-login)...\n";
$res = test_post($baseUrl . "/api/user/unified-login", $userData);
echo "POST /api/user/unified-login: HTTP {$res['code']}\n";
echo "Response: " . $res['body'] . "\n";

// Test 4: Unified login with INVALID secret (should get 401)
$invalidUserData = [
    'client_id' => '3',
    'client_secret' => 'WRONG_SECRET_TESTING_123',
    'username' => 'test@test.com',
    'password' => 'secret',
    'device_id' => '123',
    'device_type' => 'android',
    'device_token' => 'abc',
];
echo "\nTesting User login with INVALID secret...\n";
$res = test_post($baseUrl . "/api/user/unified-login", $invalidUserData);
echo "POST /api/user/unified-login (Invalid Secret): HTTP {$res['code']}\n";
echo "Response: " . $res['body'] . "\n";
