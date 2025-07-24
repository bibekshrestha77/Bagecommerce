<?php
session_start();
require 'components/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart
$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
$select_cart->execute([$user_id]);

if ($select_cart->rowCount() == 0) {
    echo "<script>alert('Your cart is empty!');window.location.href='shop.php';</script>";
    exit();
}

$grand_total = 0;
while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
    $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <div class="container">
        <header class="header">
            <div class="flex">
                <a href="home.php" class="logo">HamroBagRamroBag</a>
                <nav class="navbar">
                    <a href="home.php">Home</a>
                    <a href="shop.php">Shop</a>
                    <a href="orders.php">Orders</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact</a>
                </nav>
                <div class="icons">
                    <div id="menu-btn" class="fas fa-bars"></div>
                    <div id="user-btn" class="fas fa-user"></div>
                    <a href="cart.php">
                        <div class="fas fa-shopping-cart"></div>
                    </a>
                </div>
            </div>
        </header>

        <main>
            <section class="checkout-form">
                <form action="khalti.php" method="POST">
                    <h3>Enter Delivery Details</h3>

                    <div class="inputBox">
                        <span>Name:</span>
                        <input type="text" name="name" required>
                    </div>
                    <div class="inputBox">
                        <span>Phone:</span>
                        <input type="text" name="phone" required>
                    </div>
                    <div class="inputBox">
                        <span>Email:</span>
                        <input type="email" name="email" required>
                    </div>
                    <div class="inputBox">
                        <span>Address Line 1:</span>
                        <input type="text" name="address1" required>
                    </div>
                    <div class="inputBox">
                        <span>Address Line 2:</span>
                        <input type="text" name="address2">
                    </div>
                    <div class="inputBox">
                        <span>City:</span>
                        <input type="text" name="city" required>
                    </div>
                    <div class="inputBox">
                        <span>Province:</span>
                        <input type="text" name="state" required>
                    </div>
                    <div class="inputBox">
                        <span>Country:</span>
                        <input type="text" name="country" required>
                    </div>
                    <div class="inputBox">
                        <span>ZIP Code:</span>
                        <input type="text" name="zip" required>
                    </div>

                    <!-- Hidden fields for order details -->
                    <input type="hidden" name="amount" value="<?= $grand_total; ?>">
                    <input type="hidden" name="order_id" value="ORDER<?= rand(10000, 99999); ?>">

                    <button type="submit" class="btn">Proceed to Payment</button>
                </form>
            </section>
        </main>

        <footer class="footer">
            <div class="credit">Created by <span>Your Company</span> | All Rights Reserved</div>
            <div class="social">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
            </div>
        </footer>
    </div>
</body>

</html>