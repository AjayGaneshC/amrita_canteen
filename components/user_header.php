<?php

include 'components/connect.php';
if(isset($message)){
   foreach($message as $message){ 
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<style>
<?php include 'css/style.css'; ?>
</style>

<header class="header">
   <a href="home.php">
      <img src="images/amrita_logo_white.png" alt="Amrita Canteen">
   </a>
      
   <!-- <a href="home.php" class="logo">Amrita Canteen</a> -->

      <div class="navbar">
         <a href="home.php">Home</a>
         <a href="menu.php">Menu</a>
         <a href="orders.php">Orders</a>
      </div>

      <div class="icons">
         <?php
            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_items = $count_cart_items->rowCount();
         ?>
         <!-- <a href="search.php"><i class="fas fa-search"></i></a> -->
         <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?= $total_cart_items; ?>)</span></a>
         <?php if(isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="fas fa-user"></a>
         <?php else: ?>
            <a href="login.php" class="fas fa-user"></a>
         <?php endif; ?>
      </div>

</header>