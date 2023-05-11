<?php
if (isset($message)) {
    foreach ($message as $message) {
        echo '
       <div class="message">
          <span>' . $message . '</span>
          <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
       </div>
       ';
    }
}
?>
<style>

.dropbtn {
    font-size: 16px;
    color: white;
    background-color: #E0163D;
    padding: 10px;
    border: none;
    cursor: pointer;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #777;
    min-width: 190px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    padding: 12px 16px;
    z-index: 1;
}

.dropdown:hover .dropdown-content {
    display: block;
}
@media screen and (max-width: 991px) {
        .dropdown-content {
        min-width: 190px;
        padding: 14px;
    }
}

    @media screen and (max-width: 768px) {
        .dropdown-content {
        min-width: 250px;
    }
}

    @media screen and (max-width: 450px) {
        .dropdown-content {
        min-width: 190px;
    }
}
</style>
<header class="header">
    <section class="flex">
        <a href="home.php" class="logo">FOOD ORDER SYSTEM</a>
        <nav class="navbar">
            <a href="home.php">Home</a>
            <a href="about.php">About</a>
        <div class="dropdown">
        <button class="dropbtn">Orders ▽ </button>
        <div class="dropdown-content">
            <a href="orders.php">COD-Orders</a>
            <a href="gcashorders.php">GCASH-Orders</a>
        </div>
        </div>
            <a href="menu.php">Menu</a>
            <a href="contact.php">Contact</a>
        </nav>

        <div class="icons">
            <?php
$count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
$count_cart_items->execute([$user_id]);
$total_cart_items = $count_cart_items->rowCount();
?>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?=$total_cart_items;?>)</span></a>
            <div id="user-btn" class="fas fa-user"></div>
            <div id="menu-btn" class="fas fa-bars"></div>
        </div>

      <div class="profile">
         <?php
$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
if ($select_profile->rowCount() > 0) {
    $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
    ?>
         <h1>Hello!</h1>
         <p class="name"><?=$fetch_profile['name'];?></p>
         <div class="flex">
            <a href="profile.php" class="btn">Profile</a>
            <a href="components/user_logout.php" class="delete-btn">Logout</a>
         </div>
         <?php
} else {
    ?>
            <p class="name">• PLEASE LOGIN FIRST! •</p>
            <a href="login.php" class="btn">Login</a>
         <?php
}
?>
      </div>
    </section>
</header>

<script>
    // Get the current page URL
    var current_url = window.location.href;

    // Get all links in the navbar
    var links = document.querySelectorAll('.navbar a');

    // Loop through each link
    links.forEach(function(link) {
        // Check if the link's href matches the current URL
        if(link.href === current_url) {
            // Add the active class and change the style to black with an underline
            link.classList.add('active');
            link.style.color = "#222";
            link.style.textDecoration = "underline";
            link.style.fontWeight = "bold";
        } else {
            // Remove the active class and reset the style to white with no underline
            link.classList.remove('active');
            link.style.color = "white";
            link.style.textDecoration = "none";
            link.style.fontWeight = "normal";
        }

        // Add hover effects
        link.addEventListener('mouseover', function() {
            link.style.color = "#222";
            link.style.textDecoration = "underline";
            link.style.fontWeight = "bold";
        });

        link.addEventListener('mouseout', function() {
            if(link.classList.contains('active')) {
                link.style.color = "#222";
                link.style.textDecoration = "underline";
            } else {
                link.style.color = "white";
                link.style.fontWeight = "normal";
                link.style.textDecoration = "none";
            }
        });
    });
</script>