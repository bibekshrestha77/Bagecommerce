<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
}
;

include 'components/wishlist_cart.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>HamroBagRamroBag</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <!-- Swiper CSS -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />


</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <div class="home-bg">

      <section class="home">

         <div class="swiper mySwiper">
            <div class="swiper-wrapper">

               <div class="swiper-slide slide">
                  <div class="image">
                     <img src="images/schoollbag.png" alt="">
                  </div>
                  <div class="content">
                     <span>Upto 50% Off</span>
                     <h3>Latest School Backpacks</h3>
                     <a href="category.php?category=school bags" class="btn">Shop Now</a>
                  </div>
               </div>

               <div class="swiper-slide slide">
                  <div class="image">
                     <img src="images/laptopbag.png" alt="">
                  </div>
                  <div class="content">
                     <span>Upto 50% off</span>
                     <h3>Latest Laptop Bags</h3>
                     <a href="category.php?category=laptop bags" class="btn">Shop Now.</a>
                  </div>
               </div>

               <div class="swiper-slide slide">
                  <div class="image">
                     <img src="images/leatherbagg.png" alt="">
                  </div>
                  <div class="content">
                     <span>upto 50% off</span>
                     <h3>Latest Leather Bags</h3>
                     <a href="category.php?category=leather bags" class="btn">Shop Now.</a>
                  </div>
               </div>

            </div>
            <div class="swiper-pagination"></div>
         </div>


      </section>

   </div>

   <section class="category">

      <h1 class="heading">Shop by Category</h1>

      <div class="swiper category-slider">

         <div class="swiper-wrapper">

            <a href="category.php?category=school" class="swiper-slide slide">
               <img src="images/icon-1.png" alt="">
               <h3>School Backpacks</h3>
            </a>

            <a href="category.php?category=laptop" class="swiper-slide slide">
               <img src="images/icon-2.png" alt="">
               <h3>Laptop Bags</h3>
            </a>

            <a href="category.php?category=trolley" class="swiper-slide slide">
               <img src="images/icon-3.png" alt="">
               <h3>Trolley Bags</h3>
            </a>

            <a href="category.php?category=travels" class="swiper-slide slide">
               <img src="images/travelbag.png" alt="">
               <h3>Travel Backpacks</h3>
            </a>

            <a href="category.php?category=briefcases" class="swiper-slide slide">
               <img src="images/briefcasesbag.png" alt="">
               <h3>Briefcases</h3>
            </a>

            <a href="category.php?category=backpacks" class="swiper-slide slide">
               <img src="images/hikingbag.png" alt="">
               <h3>Hiking Backpacks</h3>
            </a>

            <a href="category.php?category=material-based" class="swiper-slide slide">
               <img src="images/leatherbag.png" alt="">
               <h3>Leather Bags</h3>
            </a>

            <a href="category.php?category=material-based" class="swiper-slide slide">
               <img src="images/lyonbag.png" alt="">
               <h3>Nylon Bags</h3>
            </a>

         </div>

         <div class="swiper-pagination"></div>

      </div>

   </section>

   <section class="home-products">

      <h1 class="heading">Latest products</h1>

      <div class="swiper products-slider">

         <div class="swiper-wrapper">

            <?php
            $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 6");
            $select_products->execute();
            if ($select_products->rowCount() > 0) {
               while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
                  ?>
                  <form action="" method="post" class="swiper-slide slide">
                     <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
                     <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
                     <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
                     <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
                     <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
                     <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
                     <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
                     <div class="name"><?= $fetch_product['name']; ?></div>
                     <div class="flex">
                        <div class="price"><span>Nrs.</span><?= $fetch_product['price']; ?><span>/-</span></div>
                        <input type="number" name="qty" class="qty" min="1" max="99"
                           onkeypress="if(this.value.length == 2) return false;" value="1">
                     </div>
                     <input type="submit" value="add to cart" class="btn" name="add_to_cart">
                  </form>
                  <?php
               }
            } else {
               echo '<p class="empty">no products added yet!</p>';
            }
            ?>

         </div>

         <div class="swiper-pagination"></div>

      </div>

   </section>









   <?php include 'components/footer.php'; ?>

   <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

   <script src="js/script.js"></script>

   <script>

      var swiper = new Swiper(".home-slider", {
         loop: true,
         spaceBetween: 20,
         pagination: {
            el: ".swiper-pagination",
            clickable: true,
         },
      });

      var swiper = new Swiper(".category-slider", {
         loop: true,
         spaceBetween: 20,
         pagination: {
            el: ".swiper-pagination",
            clickable: true,
         },
         breakpoints: {
            0: {
               slidesPerView: 2,
            },
            650: {
               slidesPerView: 3,
            },
            768: {
               slidesPerView: 4,
            },
            1024: {
               slidesPerView: 5,
            },
         },
      });

      var swiper = new Swiper(".products-slider", {
         loop: true,
         spaceBetween: 20,
         pagination: {
            el: ".swiper-pagination",
            clickable: true,
         },
         breakpoints: {
            550: {
               slidesPerView: 2,
            },
            768: {
               slidesPerView: 2,
            },
            1024: {
               slidesPerView: 3,
            },
         },
      });

   </script>
   <!-- Swiper JS -->
   <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

   <!-- Your custom JS file -->
   <script src="js/script.js"></script> <!-- Make sure this points to your JS file -->

</body>

</html>


<script>
   var swiper = new Swiper(".mySwiper", {
      loop: true,
      spaceBetween: 30,
      centeredSlides: true,
      autoplay: {
         delay: 3000, // 3 seconds
         disableOnInteraction: false,
      },
      slidesPerView: 1,
      pagination: {
         el: ".swiper-pagination",
         clickable: true,
      },
      navigation: {
         nextEl: ".swiper-button-next",
         prevEl: ".swiper-button-prev",
      },
   });
</script>