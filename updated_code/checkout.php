<?php

// INCLUDING DATABASE CONNECTION
include 'components/connect.php';

// SESSION IF NOT LOGIN YOU CANT GO TO DIRECT PAGE
// ELSE IF YOU TRY TO GO YOU WILL GO BACK AT THE HOMEPAGE
session_start();
if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:home.php');
};


// CHECKOUT QUERY
if(isset($_POST['submit'])){

    $name = $_POST['name'];
    $number = $_POST['number'];
    $email = $_POST['email'];
    $method = $_POST['method'];
    $address = $_POST['address'];
    $total_products = $_POST['total_products'];
    $total_price = $_POST['total_price'];
 
    $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $check_cart->execute([$user_id]);
 
    if($check_cart->rowCount() > 0){
 
       if($address == ''){
          $message[] = '• Please add your address';
       }else{
          
          $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
          $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);
 
          $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
          $delete_cart->execute([$user_id]);
 
          header ('location: orders.php');
       }
       
    }else{
       $message[] = '• Your Cart is Empty';
    }
 
 }
 
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout</title>

    <!-- FONT AWESOME LINK -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
   <!-- CSS LINK  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
    
<!-- INCLUDING HEADER -->
<?php include 'components/user_header.php';?>
<!-- HEADER END -->

<div class="heading">
   <h3>Checkout</h3>
   <p><a href="home.php">Home</a> <span> / Checkout</span></p>
</div>

<!-- CHECKOUT START -->
<section class="checkout">

   <h1 class="title">Order Summary</h1>

   <form action="" method="post" onsubmit="return redirectPaymentMethod();">

   <div class="cart-items">
      <h3>Cart Items</h3>
      <?php
         $grand_total = 0;
         $cart_items[] = '';
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') - ';
               $total_products = implode($cart_items);
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
      ?>
      <p><span class="name"><?= $fetch_cart['name']; ?></span><span class="price">₱<?= $fetch_cart['price']; ?> x <?= $fetch_cart['quantity']; ?></span></p>
      <?php
            }
         }else{
            echo '<p class="empty">Your cart is empty!</p>';
         }
      ?>
      <p class="grand-total"><span class="name">GRAND TOTAL :</span><span class="price">₱<?= $grand_total; ?></span></p>
      <a href="cart.php" class="btn">View Cart</a>
   </div>

   <input type="hidden" name="total_products" value="<?= $total_products; ?>">
   <input type="hidden" name="total_price" value="<?= $grand_total; ?>" value="">
   <input type="hidden" name="name" value="<?= $fetch_profile['name'] ?>">
   <input type="hidden" name="number" value="<?= $fetch_profile['number'] ?>">
   <input type="hidden" name="email" value="<?= $fetch_profile['email'] ?>">
   <input type="hidden" name="address" value="<?= $fetch_profile['address'] ?>">

   <div class="user-info">
      <h3>My Information</h3>
      <p><i class="fas fa-user"></i> <span> <?= $fetch_profile['name'] ?></span></p>
      <p><i class="fas fa-phone"></i> <span> <?= $fetch_profile['number'] ?></span></p>
      <p><i class="fas fa-envelope"></i> <span><?= $fetch_profile['email'] ?></span></p>
      <p><i class="fas fa-map-marker-alt"> </i><span> <?php if($fetch_profile['address'] == ''){echo 'Enter your address first!';}else{echo $fetch_profile['address'];} ?></span></p>
      <!-- <a href="update_profile.php" class="btn">Update Info</a> -->
      <select name="method" class="box" required>
         <option value="" disabled selected>SELECT PAYMENT METHOD --</option>
         <option value="CASH ON DELIVERY">CASH ON DELIVERY</option>
         <option value="GCASH">GCASH</option>
      </select>
      <!-- <div class="gcash">
         <span id="gcashnum">STORE GCASH Number: 09483284522 </span>
      </div> -->
      <div id="gcash-info"></div>
      <input type="submit" value="place order" class="btn <?php if($fetch_profile['address'] == ''){echo 'disabled';} ?>" style="width:100%; background:#E0163D; color:#fff;" name="submit">
   </div>

</form>
   
</section>
<!-- CHECKOUT END -->

<!-- SWIPER JS  -->
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<!-- CUSTOM JS FILE -->
<script src="js/script.js"></script>
<script>
   document.querySelector('select[name="method"]').addEventListener('change', function() {
      if (this.value === 'GCASH') {
         document.querySelector('#gcash-info').innerHTML = '<p><span style="color: red; font-size: 15px;">"CLICK PLACE ORDER TO PROCEED IN GCASH PAYMENT"</span></p>';
      } else {
         document.querySelector('#gcash-info').innerHTML = '';
      }
   });

  function redirectPaymentMethod() {
  var paymentMethod = document.getElementsByName("method")[0].value;
  if (paymentMethod === "GCASH") {
    // Check if the cart is empty
    <?php 
      $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $check_cart->execute([$user_id]);
      $cart_items = $check_cart->fetchAll(PDO::FETCH_ASSOC);
      if (count($cart_items) === 0) { 
    ?>
      alert("YOUR CART IS EMPTY PLEASE ADD FOOD TO YOUR CART BEFORE PROCEEDING TO GCASH PAYMENT!");
      return false;
    <?php } else { ?>
      window.location.href = "gcash_payment.php";
      return false;
    <?php } ?>
  }
  return true;
}
</script>


</body>
</html>