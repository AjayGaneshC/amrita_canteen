<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];

   $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
   $select_profile->execute([$user_id]);
   $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
}else{
   $user_id = '';
   header('location:login.php');
};

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Profile</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

</head>
<body>

<style>
<?php include 'css/style.css'; ?>
</style>
   
<?php include 'components/user_header.php'; ?>

<section class="user-details">

   <div class="user">
      <?php
         if (isset($fetch_profile)) {
      ?>
      <img src="images/user-icon.png" alt="">
      <p><i class="fas fa-user"></i><span><span><?= $fetch_profile['name']; ?></span></span></p>
      <p><i class="fas fa-phone"></i><span><?= $fetch_profile['number']; ?></span></p>
      <p><i class="fas fa-envelope"></i><span><?= $fetch_profile['email']; ?></span></p>
      <a href="update_profile.php" class="btn">UPDATE</a>
      <a href="components/user_logout.php" onclick="return confirm('logout from this website?');" class="delete-btn">LOGOUT</a>
      <?php
         }
      ?>
   </div>

</section>

<script src="js/script.js"></script>

</body>
</html>