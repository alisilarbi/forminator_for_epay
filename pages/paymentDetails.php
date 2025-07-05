<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../env.php';
loadEnv();

$orderNumber = $_GET['order_number'] ?? null;
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


print_r(json_decode($response, true));
die();




?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Recu de paiement SATIM">
    <meta name="author" content="https://github.com/Da-ci">
    <title>Reçu de paiement</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
</head>

<body class="bg-light">
    <?php get_header(); ?>

    <div class="container" style="margin-bottom: 50px;">
        <div class="text-center">
            <h3 class="mb-5 ">Reçu de paiement</h3>
        </div>
        <!-- order information -->
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th class="fixed-width-column" scope="row">Message de SATIM</th>
                    <td>
                        <?php echo $result['message_return'] ?>
                    </td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">Méthode de paiement</th>
                    <td>CIB/Edahabia</td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">Montant du paiement</th>
                    <td>
                        <?php echo $result['total'] ?> DA
                    </td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">Numéro de commande</th>
                    <td>
                        <?php echo $result['id'] ?>
                    </td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">Identifiant de la transaction</th>
                    <td>
                        <?php echo $result['order_id'] ?>
                    </td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">Numéro d'autorisation</th>
                    <td>
                        <?php echo $result['code'] ?>
                    </td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">Date & Heure de paiement</th>
                    <td>
                        <?php echo $result['submission_time'] ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div>
            <label for="emailInput">Envoyer par email:</label>
            <div class="d-flex flex-row justify-content-between">
                <input type="text" id="emailInput" placeholder="Your email">
                <button id="sendEmailButton" style="margin-left: 10px;">Send Email</button>
                <a class="btn btn-outline-secondary" href="<?php echo site_url('/wp-content/plugins/forminator_for_satim/functions/generateReceiptPDF.php') ?>" target="_blank" style="margin-left: 10px;">Download PDF</a>
                <button class="btn btn-outline-secondary" id="printPDFButton" style="margin-left: 10px;">Imprimer</button>
            </div>
        </div>
    </div>

    <!-- contact SATIM -->
    <div class="dropdown-divider mb-3" style="max-width: 800px; margin: auto;"></div>
    <div class="d-flex flex-column" style="max-width: 300px; margin: auto;">
        <div class="mb-2">Au cas de problème avec votre carte CIB, contacter le numéro vert de la SATIM</div>
        <div>
            <img src="./assets/images/numero-vert-satim-300x64.png" class="w-100 h-100">
        </div>
    </div>
    <?php get_footer(); ?>

    <div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Notification</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="notification-message"></p>
                </div>
            </div>
        </div>
    </div>


    <script>
        jQuery(document).ready(function($) {
            $('#sendEmailButton').on('click', function() {
                var userEmail = $('#emailInput').val();
                $.ajax({
                    type: 'GET',
                    url: '<?php echo site_url('/wp-content/plugins/forminator_for_satim/functions/sendReceiptViaEmail.php') ?>',
                    data: {
                        action: 'send_email_action',
                        email: userEmail,
                    },
                    success: function(response) {
                        $('#notification-message').text(response);
                        $('#notificationModal').modal('show');
                    },
                });
            });

        });

        $(document).ready(function() {
            $('#downloadPDFButton').on('click', function() {
                $('#downloadLink').click();
            });
        });

        $(document).ready(function() {
            $('#printPDFButton').click(function() {
                fetch('<?php echo site_url('/wp-content/plugins/forminator_for_satim/functions/generateReceiptPDF.php') ?>')
                    .then(response => response.blob())
                    .then(blob => {
                        const url = URL.createObjectURL(blob);

                        const printWindow = window.open(url, '_blank');
                        printWindow.focus();
                        printWindow.print();
                    });
            });
        });

        // $(document).ready(function() {
        //     $('#printPDFButton').click(function() {
        //         // Fetch the PDF data from the server
        //         fetch('<?php echo site_url('/wp-content/plugins/forminator_for_satim/functions/generateReceiptPDF.php') ?>')
        //             .then(response => response.blob())
        //             .then(blob => {
        //                 // Create a temporary URL for the PDF
        //                 const url = URL.createObjectURL(blob);

        //                 // Create an iframe to hold the PDF content
        //                 const iframe = document.createElement('iframe');
        //                 iframe.style.display = 'none';
        //                 document.body.appendChild(iframe);

        //                 // Load the PDF into the iframe
        //                 iframe.src = url;

        //                 // Once the PDF loads, trigger print preview
        //                 iframe.onload = function() {
        //                     // Prepare the content for print (assuming the PDF is within the iframe)
        //                     const content = iframe.contentWindow.document.body;

        //                     // Trigger print preview focused on the PDF content
        //                     window.print({
        //                         focusedFrame: iframe,
        //                         printableContent: content,
        //                         printMode: 'iframe' // Add this line to print within the iframe
        //                     });

        //                     // Remove the iframe after printing
        //                     iframe.remove();
        //                 };
        //             });
        //     });
        // });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>

</html>