<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);



require_once 'env.php';
loadEnv();



$amount = isset($_GET['amount']) ? $_GET['amount'] : null;

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

// print_r($data);
// die();

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$response = curl_exec($ch);
curl_close($ch);

echo json_encode([
    'status' => $httpCode,
    'response' => json_decode($response, true)
]);

