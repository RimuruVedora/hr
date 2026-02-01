<?php

// API Endpoint URL (Targeting the Production Domain)
$url = 'https://hr2.viahale.com/api/employee/sync';
// Localhost fallback:
// $url = 'http://localhost/hr/public/api/employee/sync';

// The Secret Token (Must match .env EMPLOYEE_SYNC_TOKEN on the SERVER)
$token = 'secret_token_12345'; 

// Sample Employee Data to Send
$data = [
    'employee_id' => 'EMP-' . rand(1000, 9999), // Random ID for testing
    'first_name' => 'John',
    'last_name' => 'Doe API',
    'email' => 'john.doe.api.' . rand(100,999) . '@example.com', // Unique email
    'job_role' => 'Software Engineer',
    'department' => 'IT Department',
    'status' => 'Active',
    'date_hired' => date('Y-m-d'),
    'password' => 'securepass123'
];

// Initialize cURL
$ch = curl_init($url);

// Configure cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token // Add the Bearer Token
]);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for errors
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    echo "HTTP Status Code: " . $httpCode . "\n";
    echo "Response:\n" . $response . "\n";
}

// Close cURL
curl_close($ch);
