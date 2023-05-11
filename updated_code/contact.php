<?php

// INCLUDING DATABASE CONNECTION
include 'components/connect.php';

// SESSION
session_start();

// SESSION IF THE USER IS LOGIN
if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];

   // Retrieve user information from the database using the $user_id
   // Example code: 
   $select_user = $conn->prepare("SELECT name, email, number FROM `users` WHERE id = ?");
   $select_user->execute([$user_id]);
   

   // Check if the user exists
   if ($select_user->rowCount() > 0) {
      $user = $select_user->fetch();
      $name = $user['name'];
      $email = $user['email'];
      $number = $user['number'];
   }
} else {
   $user_id = '';
}


// QUERY FOR MESSAGE
if(isset($_POST['send'])){

   $name = $_POST['name'];
   $email = $_POST['email'];
   $number = $_POST['number'];
   $msg = $_POST['msg'];
   

   $select_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $select_message->execute([$name, $email, $number, $msg]);

   if($select_message->rowCount() > 0){
      $message[] = '• Already sent message!';
   }else{

      $insert_message = $conn->prepare("INSERT INTO `messages`(user_id, name, email, number, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$user_id, $name, $email, $number, $msg]);

      $message[] = '• Sent message successfully!';

   }

}

?>

<!-- CONTACT US PAGE -->
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact | Page</title>

    <!-- SWIPER LINK -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <!-- FONT AWESOME LINK -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
   <!-- CSS LINK  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<!-- INCLUDING HEADER -->
<?php include 'components/user_header.php'; ?>
<!-- HEADER ENDS -->

<!-- BREADCRUMB TITLE START -->
<div class="heading">
   <h3>Contact Us</h3>
   <p><a href="home.php">Home</a> <span> / Contact</span></p>
</div>
<!-- BREADCRUMB ENDS -->

<!-- CONTACT US START -->

<section class="contact">
   <div class="row">
      <div class="image">
         <img src="images/contact.png" alt="">
      </div>

<form action="" method="post">
    <h3>Tell us something!</h3>
    <input type="text" name="name" maxlength="50" class="box" placeholder="Enter your name" required value="<?php echo isset($name) ? $name : ''; ?>">
    <input type="number" name="number" min="0" max="9999999999" class="box" placeholder="Enter your number" required value="<?php echo isset($number) ? $number : ''; ?>" maxlength="11">
    <input type="email" name="email" maxlength="50" class="box" placeholder="Enter your email" required value="<?php echo isset($email) ? $email : ''; ?>">
    <textarea name="msg" class="box" required placeholder="Enter your message" maxlength="500" cols="30" rows="10"></textarea>
    <input type="submit" value="send message" name="send" class="btn">
</form>
   </div>

</section>

<!-- CONTACT US ENDS -->










<!-- INCLUDING FOOTER -->
<?php include 'components/footer.php';?>
<!-- FOOTER ENDS -->

<!-- CUSTOM JS FILE -->
<script src="js/script.js"></script>

</body>
</html>