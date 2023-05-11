<?php
session_start();
require_once 'components/connect.php';

// SESSION CHECK IF USER IS LOGIN
if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit();
}

// CHECK IF CART IS EMPTY CANT REDIRECT TO GCASH_PAYMENT.PHP
$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
$select_cart->execute([$_SESSION['user_id']]);
if($select_cart->rowCount() == 0){
    header('Location: checkout.php');
    exit();
}

// GET USER'S PROFILE
$user_id = $_SESSION['user_id'];
$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

// GET CART ITEMS
$grand_total = 0;
$cart_items = [];
$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
$select_cart->execute([$user_id]);
if($select_cart->rowCount() > 0){
    while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
        $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].')';
        $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
    }
}

// PREPARE DATA FOR TRANSACTION
$total_products = implode(', ', $cart_items);
$name = $fetch_profile['name'];
$number = $fetch_profile['number'];
$email = $fetch_profile['email'];
$address = $fetch_profile['address'];
$method = 'GCASH';
$payment_status = 'Pending';

// INSERTING DATA TO DATABASE
if(isset($_POST['submit'])){
    $gcash_name = $_POST['gcash_name'];
    $gcash_num = $_POST['gcash_num'];
    $gcash_amount = $_POST['gcash_amount'];

    $errors = [];

    // Errors;
    if(empty($gcash_name)){
        $errors[] = 'GCASH Name is required.';
    }

    if(empty($gcash_num)){
        $errors[] = 'GCASH Number is required.';
    }

    if(empty($gcash_amount)){
        $errors[] = 'Payment Amount is required.';
    }elseif($gcash_amount < $grand_total){
        $errors[] = 'Payment Amount must be greater than or equal to the Total Price!';
    }

    // If no error
    if(empty($errors)){
        // Generate random numbers
        $reference_number = uniqid();
        $insert_order = $conn->prepare("INSERT INTO `orders` (user_id, total_products, total_price, name, number, email, address, method, gcash_name, gcash_num, gcash_amount, payment_status, change_amount, reference_number) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $change_amount = $gcash_amount - $grand_total;
        $insert_order->execute([$user_id, $total_products, $grand_total, $name, $gcash_num, $email, $address, $method, $gcash_name, $gcash_num, $gcash_amount, $payment_status, $change_amount, $reference_number]);

        // Automatic update if i submit
        $order_id = $conn->lastInsertId(); // Get the ID of the inserted order
        $update_payment_status = $conn->prepare("UPDATE `orders` SET payment_status = 'Paid' WHERE id = ?");
        $update_payment_status->execute([$order_id]);

        // Automatic delete cart if i submit
        $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
        $delete_cart->execute([$user_id]);

        $_SESSION['payment_status'] = $payment_status;

        header('location: gcash_confirmation.php');
        exit();
    }
}

?>


<!-- GCASH PAGE -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initialization-scale=1.0">
<title>GCASH Payment</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="css/gcash.css">
</head>
<body>
	<header>
        <img src="images/gcash-logo.png" alt="">
        <h1 style="font-size: 20px;">PLEASE FILL UP THE FORM!</h1>
	</header>
	<main>
		<section class="gcash-form">
			<form method="POST">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger p-0">
                        <ul class="m-1">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <label class="mt-2" for="">GCASH Name : </label><br>
                <select name="gcash_name" id="">
                    <option value="">SELECT GCASH NAME : </option>
                    <option value="Russ A. Chie">Russ A. Chie</option>
                    <!-- <option value="Archie De Vera">Archie De Vera</option> -->
                </select><br>
                <label for="">GCASH Number : </label><br>
                <select name="gcash_num" id="">
                    <option value="">SELECT GCASH NUMBER : </option>
                    <option value="09495748302">09495748302</option>
                    <!-- <option value="09123456789">09123456789</option> -->
                </select><br>
                <label for="gcash_amount">GCASH Payment Amount:</label><br>
                <input type="text" id="gcash_amount" name="gcash_amount" value="<?php echo isset($_POST['gcash_amount']) ? $_POST['gcash_amount'] : '' ?>" 
                oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6); if (parseInt(this.value) > 100000) { this.value = '100000'; }" 
                maxlength="6" placeholder="Please enter your payment here.."><br>
                
                <p class="bg-danger" style="color: white; font-style: bold; font-size: 30px;">TO PAY: <span>₱<?php echo $grand_total; ?></span></p>
                    <input type="submit" name="submit" value="SUBMIT PAYMENT">
                <div>
                <a href="checkout.php" class="btn btn-danger p-2 mt-2 text-white">GO BACK</a>
                </div>
			</form>
		</section>
	</main>


    <footer>
        <p style="margin-top: 20px;">&copy; Russel Vincent Cuevas 2023 GCASH-CLONE</p>
    </footer>

<div class="loading">
    <img src="images/gcash.gif" alt="">
</div>

<script>
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

