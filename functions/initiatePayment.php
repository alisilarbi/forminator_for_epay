<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// require_once __DIR__ . './env.php';
require_once __DIR__ . 'env.php';

loadEnv();

if (!isset($_GET['amount']) || !is_numeric($_GET['amount'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing amount']);
    exit;
}

$amount = $_GET['amount'];

$url = "https://epay.guiddini.dz/payment/initiate";
$headers = [
    "Accept: application/json",
    "Content-Type: application/json",
    "x-app-key: " . getenv('X_APP_KEY'),
    "x-app-secret: " . getenv('X_APP_SECRET')
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

echo json_encode([
    'status' => $httpCode,
    'response' => json_decode($response, true)
]);
