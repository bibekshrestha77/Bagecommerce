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
    <title>Delivery Details</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

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
        <input type="hidden" name="order_id" value="ORDER<?= rand(10000,99999); ?>">

        <button type="submit" class="btn">Proceed to Payment</button>
    </form>
</section>

<?php include 'components/footer.php'; ?>
</body>
</html>
