<?php
include 'components/connect.php';
session_start();

if (!isset($_GET['pidx']) || !isset($_SESSION['user_id'])) {
    die("Invalid access.");
}

$user_id = $_SESSION['user_id'];
$pidx = $_GET['pidx'];

// Verify payment with Khalti
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://a.khalti.com/api/v2/epayment/lookup/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode(["pidx" => $pidx]),
    CURLOPT_HTTPHEADER => [
        "Authorization: Key 1cb49e98099c442e927e8afdabb130e4",
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "Curl Error: " . $err;
    exit;
}

$result = json_decode($response, true);

if (isset($result['status']) && $result['status'] === "Completed") {
    // Payment is successful ✅

    // Fetch user cart items
    $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $select_cart->execute([$user_id]);

    if ($select_cart->rowCount() > 0) {
        $cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);

        // Combine product names and total amount
        $total_products = [];
        $total_price = 0;

        foreach ($cart_items as $item) {
            $total_products[] = $item['name'] . " (x" . $item['quantity'] . ")";
            $total_price += $item['price'] * $item['quantity'];
        }

        $products_string = implode(", ", $total_products);

        // Dummy shipping details (you can customize or fetch from session/form)
        $name = 'Test Customer';
        $email = 'test@example.com';
        $number = '9800000001';
        $address = 'Kathmandu, Nepal';
        $method = 'Khalti';
        $payment_status = 'completed';
        $placed_on = date('Y-m-d');

        // Insert into orders
        $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, address, method, total_products, total_price, payment_status, placed_on) VALUES(?,?,?,?,?,?,?,?,?,?)");
        $insert_order->execute([$user_id, $name, $number, $email, $address, $method, $products_string, $total_price, $payment_status, $placed_on]);

        // Clear cart
        $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
        $delete_cart->execute([$user_id]);
    }

    // Redirect to home or orders page
    header("Location: orders.php");
    exit();

} else {
    // Payment failed ❌
    header("Location: cart.php");
    exit();
}
