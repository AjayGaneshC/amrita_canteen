<?php

require_once 'C:\\xampp\\htdocs\\canteen\\stripe-php\\init.php';

\Stripe\Stripe::setApiKey('sk_test_51OqtuHSGKPttZjMCPOx1f7x0NVPddYMqP06FcRzVqfhICxxRonoGdwczhN48vPCe78ZHWpBeuyms0ACHiOiyB2jF00xZTlue7R');

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:home.php');
   exit;
}

function generate_random_string($length = 10) {
   $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $randomString = '';
   for ($i = 0; $i < $length; $i++) {
       $randomString .= $characters[rand(0, strlen($characters) - 1)];
   }
   return $randomString;
}

$id = generate_random_string(15);

$fetch_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$fetch_profile->execute([$user_id]);
$fetch_profile = $fetch_profile->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $method = $_POST['method'];
   $method = filter_var($method, FILTER_SANITIZE_STRING);
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];

   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if($check_cart->rowCount() > 0){
      $conn->beginTransaction();

      $insert_order = $conn->prepare("INSERT INTO `orders`(id, user_id, name, number, email, method, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
      $insert_order->execute([$id, $user_id, $name, $number, $email, $method, $total_products, $total_price]);

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);

      while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
         $update_product_quantity = $conn->prepare("UPDATE `products` SET quantity = quantity - ? WHERE id = ?");
         $update_product_quantity->execute([$fetch_cart['quantity'], $fetch_cart['pid']]);
      }

      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);

      $conn->commit();

      try {
         // Create a new Stripe Checkout Session
         $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
               [
                  'price_data' => [
                     'currency' => 'inr',
                     'unit_amount' => $total_price * 100, // Stripe requires amount in cents
                     'product_data' => [
                        'name' => 'Your Product Name',
                     ],
                  ],
                  'quantity' => 1,
               ],
            ],
            'mode' => 'payment',
            'success_url' => 'http://localhost/canteen/cart.php', // URL to redirect after successful payment
            'cancel_url' => 'http://localhost/canteen/checkout.php', // URL to redirect after payment is canceled
         ]);

         // Redirect user to Stripe Checkout
         header("Location: " . $session->url);
         exit;
      } catch (\Stripe\Exception\ApiErrorException $e) {
         // Handle Stripe API errors
         echo 'Error: ' . $e->getError()->message;
      }
   } else {
      $message[] = 'Your cart is empty';
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
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

<style>
<?php include 'css/style.css'; ?>
</style>

<?php include 'components/user_header.php'; ?>

<section class="checkout">
   <h1 class="title">Order Summary</h1>
   <form action="" method="post">
      <div class="cart-items">
         <h3>Cart Items</h3>
         <?php
            $grand_total = 0;
            $cart_items[] = '';
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if($select_cart->rowCount() > 0){
               while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                  $cart_items[] = $fetch_cart['name'].' ( x'. $fetch_cart['quantity'].')';
                  $total_products = implode($cart_items);
                  $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
         ?>
         <p><span class="name"><?= $fetch_cart['name']; ?></span><span class="price">₹<?= $fetch_cart['price']; ?> x <?= $fetch_cart['quantity']; ?></span></p>
         <?php
               }
            } else {
               echo '<p class="empty">Your cart is empty!</p>';
            }
         ?>
         <p class="grand-total">Grand Total:<span class="price">₹<?= $grand_total; ?></span></p>
         <a href="cart.php" class="btn">VIEW CART</a>
      </div>
      <input type="hidden" name="total_products" value="<?= $total_products; ?>">
      <input type="hidden" name="total_price" value="<?= $grand_total; ?>" value="">
      <input type="hidden" name="name" value="<?= $fetch_profile['name'] ?>">
      <input type="hidden" name="number" value="<?= $fetch_profile['number'] ?>">
      <input type="hidden" name="email" value="<?= $fetch_profile['email'] ?>">
      <div class="user-info">
         <!-- <h3>Your Info</h3> -->
         <!-- <p><i class="fas fa-user"></i><span><?= $fetch_profile['name'] ?></span></p>
         <p><i class="fas fa-phone"></i><span><?= $fetch_profile['number'] ?></span></p>
         <p><i class="fas fa-envelope"></i><span><?= $fetch_profile['email'] ?></span></p> -->
         <!-- <a href="update_profile.php" class="btn">Update Info</a> -->
         <select name="method" class="box" required>
            <option value="" disabled selected>Select Payment Method --</option>
            <option value="cash on delivery">Cash</option>
            <option value="credit card">Online</option>
         </select>
         <input type="submit" value="PLACE ORDER" class="btn <?php {echo 'enabled';} ?>" name="submit">
      </div>
   </form>
</section>

<script src="js/script.js"></script>
</body>
</html>

