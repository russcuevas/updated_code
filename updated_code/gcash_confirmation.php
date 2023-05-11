<?php 
// INCLUDING DATABASE CONNECTION
include 'components/connect.php';

// SESSION START
session_start();

// IF THE USER IS LOGIN
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
// ELSE IT WILL SHOW
} else {
    $user_id = '';
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; font-size:25px; text-align:center;'> <a href='login.php'>Login </a> first to see your receipt!</div>";
};

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GCASH Confirmation</title>
    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- CSS FOR PRINTING -->
    <link rel="stylesheet" type="text/css" media="print" href="css/gcash-print.css">
    <!-- CSS FOR GCASH -->
    <link rel="stylesheet" href="css/gcash.css">
</head>
<body style="background-image: none;">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
            <?php 
            $select_order = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY id DESC LIMIT 1");
            $select_order->execute([$user_id]);
            $fetch_order = $select_order->fetch();

        if ($fetch_order && $fetch_order['method'] == 'GCASH') {
            $total_price = $fetch_order['total_price'];
            $gcash_amount = $fetch_order['gcash_amount'];
            $change_amount = $fetch_order['change_amount'];
            $gcashName = $fetch_order['gcash_name'];
            $maskedName = '';
            
            $nameParts = explode(' ', $gcashName);
            
            foreach ($nameParts as $part) {
                $maskedPart = substr($part, 0, 1) . str_repeat('*', strlen($part) - 2) . substr($part, -1);
                $maskedName .= $maskedPart . ' ';
            }

            $maskedName = trim($maskedName);

        if($fetch_order['payment_status'] == 'Paid'){
        ?>
        <img style="display: block; margin: 0 auto;" src="images/gcash-logo.png" alt="">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mt-3">Successfully Paid To :</h3>
            </div>
            <div class="card-body">
                <i class="fas fa-user fa-10x user-icon d-flex justify-content-center mb-3"></i>
                <h4 style="text-align: center; font-weight: 900;">Reference Number <span style="color: red;"><?= $fetch_order['reference_number'] ?></span></h4 style="text-align: center;">
                <p style="color: black;">GCASH Name : <span style="color: red; font-weight: 800;"><?= $maskedName ?></span></p>
                <p style="color: black;">GCASH Number : <span style="color: red; font-weight: 800;"><?= $fetch_order['gcash_num'] ?></span></p>
                <p style="color: black;">PHP : <span style="color: red; font-weight: 800;">₱<?= $gcash_amount ?></span></p>
                <p style="color: black;">Date : <span style="color: red; font-weight: 800;"><?= $fetch_order['placed_on'] ?></span></p>
                <a href="gcashorders.php" class="btn btn-primary">Check your orders</a>
                <div class="">
                <button class="print-btn btn btn-warning mt-2 text-white" onclick="window.print()" print>Print Receipt</button><br>
                </div>
            </div>
        </div>
        <h6 style="text-align: end; color: red; margin-top: 5px; font-weight: bolder;">"NOTE: This GCASH is only clone made <br> by Russel Vincent Cuevas"</h6>
        <?php
            } elseif($fetch_order['payment_status'] == 'Paid'){
                header('location: gcash_confirmation.php');
            }
        } else {
            header('location: gcash_payment.php');
        }
        ?>

        </div>
    </div>
</div>

<div class="loading">
    <img src="images/gcash.gif" alt="">
</div>

<!-- JAVASCRIPT THAT IF I CLICK PRINT RECEIPT IT WILL HIDE THE CHECK YOUR ORDERS -->
<script type="text/javascript">
    window.onbeforeprint = function() {
        document.querySelector('a.btn.btn-primary').style.display = 'none';
    }
    window.onafterprint = function() {
        document.querySelector('a.btn.btn-primary').style.display = '';
    }

    // FOR LOADING PAGE
    function loading() {
    document.querySelector('.loading').style.display = 'none';
    }

    function fadeOut() {
    setInterval(loading, 2000);
    }

    window.onload = fadeOut;
</script>
</body>
</html>
