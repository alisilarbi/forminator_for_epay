<?php
require_once('../../../../wp-load.php');

if (!session_id())
    session_start();

$orderNumber = get_transient('orderNumber');

if (!$orderNumber) {
    echo 'Nope, no data snooping please. I have your ip address : ' . $_SERVER['REMOTE_ADDR'];
    die();
}

$query = $wpdb->prepare("SELECT * FROM `{$wpdb->prefix}forminator_for_satim_orders` WHERE `id` = %s", $orderNumber);
$result = $wpdb->get_row($query, ARRAY_A);
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Recu de paiement SATIM">
    <meta name="author" content="https://github.com/Da-ci">
    <title>Reçu de paiement</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
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
                    <th class="fixed-width-column" scope="row">Message d'erreur</th>
                    <td>
                        <?php echo $result['message_return'] ?>
                    </td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">Méthode de paiement</th>
                    <td>CIB/Edahabia</td>
                </tr>
                <tr>
                    <th class="fixed-width-column" scope="row">Date & Heure de paiement</th>
                    <td>
                        <?php echo $result['submission_time'] ?>
                    </td>
                </tr>
            </tbody>
        </table>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>
</body>

</html>