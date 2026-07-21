<?php
$postId = 1; // Change to a valid ID for testing
$url = "http://localhost:8010/api/user/social/posts/$postId/delete";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Authorization: Bearer TEST_TOKEN' // You'd need a real token here or disable auth for test
]);

$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "Status: " . $info['http_code'] . "\n";
echo "Response: $response\n";
