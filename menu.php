<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/add_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Menu</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

<style>
<?php include 'css/style.css'; ?>
</style>
   
<?php include 'components/user_header.php'; ?>


<section class="products">
   <section class="search-form">
      <form method="post" action="">
         <input autocomplete="off" type="text" id="searchInput" placeholder="Search for dishes...">
      </form>
   </section>

   <div class="box-container">

      <?php
         $select_products = $conn->prepare("SELECT * FROM `products`");
         $select_products->execute();
         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
      ?>
      <form action="" method="post" class="box" onclick="location.href='quick_view.php?pid=<?= $fetch_products['id']; ?>'">
         <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
         <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
         <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
         <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">
         <!-- <a href="quick_view.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a> -->
         <!-- <button type="submit" class="fas fa-shopping-cart" name="add_to_cart"></button> -->
         <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
         <a href="category.php?category=<?= $fetch_products['category']; ?>" class="cat"><?= $fetch_products['category']; ?></a>
         <div class="flex">
            <div class="name"><?= $fetch_products['name']; ?></div>
            <div class="quantity">Qty:</div>
            <input type="number" name="qty" class="qty" min="1" max="50" value="1" maxlength="2">
         </div>
         <div class="flex">
            <div class="price"><span>₹</span><?= $fetch_products['price']; ?></div>
            <button type="submit" name="add_to_cart" class="cart-btn">add to cart</button>
         </div>
         <div class="quantity">Remaining: <?= $fetch_products['quantity']; ?></div>
      </form>
      
      <?php
            }
         }else{
            echo '<p class="empty">no products added yet!</p>';
         }
      ?>

   </div>

</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
   const searchInput = document.getElementById('searchInput');
   const menuItems = document.getElementById('menuItems');

   searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const items = document.querySelectorAll('.box');
      
      items.forEach(item => {
         const itemName = item.querySelector('.name').textContent.toLowerCase();
         if (itemName.includes(searchTerm)) {
            item.style.display = 'block';
         } else {
            item.style.display = 'none';
         }
      });
   });
});

</script>

<!-- menu section ends -->

<!-- footer section starts  -->
<?php include 'components/footer.php'; ?>
<!-- footer section ends -->

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>
