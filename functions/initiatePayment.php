<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../env.php';
loadEnv();

// Validate `amount`
if (!isset($_GET['amount']) || !is_numeric($_GET['amount'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing amount']);
    exit;
}

// Get `payment_type`, default to zakat
$paymentType = isset($_GET['payment_type']) && $_GET['payment_type'] === 'wakf' ? 'WAKF' : 'ZAKAT';

$amount = $_GET['amount'];

$url = "https://epay.guiddini.dz/api/payment/initiate";
$headers = [
    "Accept: application/json",
    "Content-Type: application/json",
    "x-app-key: " . getenv("X_{$paymentType}_APP_KEY"),
    "x-app-secret: " . getenv("X_{$paymentType}_APP_SECRET")
];

$data = json_encode([
    "amount" => $amount
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Decode response
$result = json_decode($response, true);
$formUrl = $result['data']['attributes']['form_url'];
    header("Location: $formUrl");
    exit;

// If response is valid and contains form_url, redirect
// if ($httpCode === 200 && isset($result['data']['attributes']['form_url'])) {
//     $formUrl = $result['data']['attributes']['form_url'];
//     header("Location: $formUrl");
//     exit;
// } else {
//     // Fallback: show response for debugging
//     http_response_code($httpCode);
//     echo json_encode([
//         'status' => $httpCode,
//         'response' => $result
//     ]);
//     exit;
// }
