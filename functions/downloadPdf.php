<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../env.php';
loadEnv();

$orderNumber = $_GET['order_number'] ?? null;

if (!$orderNumber) {
    die('Missing order number.');
}

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

$responseArray = json_decode($response, true);
$pdfUrl = $responseArray['links']['href'] ?? null;

if (!$pdfUrl) {
    die('Unable to retrieve PDF URL.');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Downloading PDF...</title>
</head>

<body>
    <script>
        window.open(<?php echo json_encode($pdfUrl); ?>, '_blank');
        setTimeout(function() {
            window.close();
        }, 1000);
    </script>

</body>

</html>