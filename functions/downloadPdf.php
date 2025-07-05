<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../env.php';
loadEnv();

$orderNumber = $_GET['order_number'] ?? null;
if (!$orderNumber) {
    http_response_code(400);
    exit('Missing order number.');
}

$url = "https://epay.guiddini.dz/api/payment/receipt";
$headers = [
    "Accept: application/json",
    "Content-Type: application/json",
    "x-app-key: " . getenv("X_ZAKAT_APP_KEY"),
    "x-app-secret: " . getenv("X_ZAKAT_APP_SECRET")
];

$ch = curl_init($url . '?' . http_build_query(['order_number' => $orderNumber]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$pdfUrl = $data['links']['href'] ?? null;
if (!$pdfUrl) {
    http_response_code(500);
    exit('PDF URL not found.');
}

$ch = curl_init($pdfUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$pdfData = curl_exec($ch);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="receipt_' . $orderNumber . '.pdf"');
header('Content-Length: ' . strlen($pdfData));
echo $pdfData;
exit;
