<?php
require __DIR__ . '/../env.php';
loadEnv();

// $orderNumber = $_GET['order_number'];
global $orderNumber;

if (!isset($orderNumber)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing order number']);
    exit;
}

$url = "https://epay.guiddini.dz/api/payment/show";
$headers = [
    "Accept: application/json",
    "Content-Type: application/json",
    "x-app-key: " . getenv("X_ZAKAT_APP_KEY"),
    "x-app-secret: " . getenv("X_ZAKAT_APP_SECRET")
];

$data = array('order_number' => $orderNumber);

$ch = curl_init($url . '?' . http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
curl_close($ch);


