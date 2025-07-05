<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../env.php';
loadEnv();

$email = $_GET['email'] ?? null;
$orderNumber = $_GET['order_number'] ?? null;


$url = "https://epay.guiddini.dz/api/payment/email";
$headers = [
    "Accept: application/json",
    "Content-Type: application/json",
    "x-app-key: " . getenv("X_ZAKAT_APP_KEY"),
    "x-app-secret: " . getenv("X_ZAKAT_APP_SECRET")
];
$data = json_encode([
    "order_number" => $orderNumber,
    "email" => $email,
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
