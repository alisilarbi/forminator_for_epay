<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../../../../wp-load.php');
require __DIR__ . '/../env.php';
loadEnv();

$orderNumber = $_GET['order_number'] ?? null;
if (!isset($orderNumber)) {
    http_response_code(400);
    echo json_encode(['error' => 'رقم الطلب غير صالح أو مفقود']);
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

$responseArray = json_decode($response, true);
$transaction = $responseArray['data']['attributes'] ?? null;
$meta = $responseArray['meta'] ?? null;

if ($transaction && isset($transaction['status'])) {
    if ($transaction['status'] == 'paid') {
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="إيصال الدفع SATIM">
    <meta name="author" content="https://github.com/Da-ci">
    <title>إيصال الدفع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans Arabic', sans-serif;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
</head>
<body class="bg-light">
    <?php get_header(); ?>
    <div class="container" style="margin-bottom: 50px;">
        <div class="text-center">
            <h3 class="mb-5">إيصال الدفع</h3>
        </div>
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th class="fixed-width-column" scope="row">رسالة من SATIM</th>
                    <td><?php echo $transaction['action_code_description']; ?></td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">طريقة الدفع</th>
                    <td>CIB/Edahabia</td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">مبلغ الدفع</th>
                    <td><?php echo $transaction['deposit_amount']; ?> دج</td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">رقم الطلب</th>
                    <td><?php echo $transaction['order_id']; ?></td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">معرف المعاملة</th>
                    <td><?php echo $transaction['order_number']; ?></td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">رقم التفويض</th>
                    <td><?php echo $transaction['approval_code']; ?></td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">تاريخ ووقت الدفع</th>
                    <td><?php echo $transaction['updated_at']; ?></td>
                </tr>
            </tbody>
        </table>
        <div>
            <label for="emailInput">إرسال عبر البريد الإلكتروني:</label>
            <div class="d-flex flex-row justify-content-between">
                <input type="text" id="emailInput" placeholder="بريدك الإلكتروني">
                <button id="sendEmailButton" class="mx-2">إرسال البريد الإلكتروني</button>
                <a class="btn btn-outline-secondary mx-2" href="<?php echo site_url('/wp-content/plugins/forminator_for_satim/functions/generateReceiptPDF.php'); ?>" target="_blank">تحميل PDF</a>
                <button class="btn btn-outline-secondary mx-2" id="printPDFButton">طباعة</button>
            </div>
        </div>
    </div>
    <div class="dropdown-divider mb-3" style="max-width: 800px; margin: auto;"></div>
    <div class="d-flex flex-column" style="max-width: 300px; margin: auto;">
        <div class="mb-2">في حالة وجود مشكلة مع بطاقتك CIB، اتصل بالرقم الأخضر لـ SATIM</div>
        <div>
            <img src="./assets/images/numero-vert-satim-300x64.png" class="w-100 h-100">
        </div>
    </div>
    <?php get_footer(); ?>
    <div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">إشعار</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="notification-message"></p>
                </div>
            </div>
        </div>
    </div>
    <script>
        var orderNumber = '<?php echo $orderNumber; ?>';
    </script>
    <script>
        jQuery(document).ready(function($) {
            $('#sendEmailButton').on('click', function() {
                var userEmail = $('#emailInput').val();
                $.ajax({
                    type: 'GET',
                    url: '<?php echo site_url('/wp-content/plugins/epay/functions/sendEmail.php'); ?>',
                    data: {
                        email: userEmail,
                        order_number: orderNumber
                    },
                    success: function(response) {
                        try {
                            var jsonResponse = JSON.parse(response);
                            var message = jsonResponse.message || 'تم الإرسال بنجاح';
                            $('#notification-message').text(message);
                        } catch (e) {
                            $('#notification-message').text('حدث خطأ أثناء إرسال البريد الإلكتروني');
                        }
                        $('#notificationModal').modal('show');
                    }
                });
            });
        });
        $(document).ready(function() {
            $('#printPDFButton').click(function() {
                fetch('<?php echo site_url('/wp-content/plugins/epay/functions/generateReceiptPDF.php'); ?>')
                    .then(response => response.blob())
                    .then(blob => {
                        const url = URL.createObjectURL(blob);
                        const printWindow = window.open(url, '_blank');
                        printWindow.focus();
                        printWindow.print();
                    });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>
<?php
    } else {
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="إيصال الدفع SATIM">
    <meta name="author" content="https://github.com/Da-ci">
    <title>إيصال الدفع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans Arabic', sans-serif;
        }
    </style>
</head>
<body class="bg-light">
    <?php get_header(); ?>
    <div class="container" style="margin-bottom: 50px;">
        <div class="text-center">
            <h3 class="mb-5">إيصال الدفع</h3>
        </div>
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th class="fixed-width-column" scope="row">رسالة الخطأ</th>
                    <td><?php echo $transaction['action_code_description'] ?? 'المعاملة غير مدفوعة'; ?></td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">طريقة الدفع</th>
                    <td>CIB/Edahabia</td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">تاريخ ووقت الدفع</th>
                    <td><?php echo $transaction['updated_at'] ?? 'غير متوفر'; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="dropdown-divider mb-3" style="max-width: 800px; margin: auto;"></div>
    <div class="d-flex flex-column" style="max-width: 300px; margin: auto;">
        <div class="mb-2">في حالة وجود مشكلة مع بطاقتك CIB، اتصل بالرقم الأخضر لـ SATIM</div>
        <div>
            <img src="./assets/images/numero-vert-satim-300x64.png" class="w-100 h-100">
        </div>
    </div>
    <?php get_footer(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>
<?php
    }
} else {
    echo "خطأ: غير قادر على استرداد بيانات المعاملة.";
}
?>