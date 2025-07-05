<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../env.php';
loadEnv();

$orderNumber = $_GET['order_number'] ?? null;


$url = "https://epay.guiddini.dz/api/payment/receipt";
$data = array('order_number' => $orderNumber);

$headers = [
    "Accept: application/json",
    "Content-Type: application/json",
    "x-app-key: " . getenv("X_ZAKAT_APP_KEY"),
    "x-app-secret: " . getenv("X_ZAKAT_APP_SECRET")
];

$ch = curl_init($url . '?' . http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
