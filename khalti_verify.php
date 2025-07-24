<?php
include 'components/connect.php';
session_start();

// Validate access
if (!isset($_GET['pidx']) || !isset($_SESSION['user_id']) || !isset($_SESSION['delivery_info'])) {
    die("Invalid access.");
}

$user_id = $_SESSION['user_id'];
$pidx = $_GET['pidx'];

// Step 1: Verify payment with Khalti
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

// Step 2: Check payment status
if (isset($result['status']) && $result['status'] === "Completed") {
    
    // Fetch cart items
    $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $select_cart->execute([$user_id]);

    if ($select_cart->rowCount() > 0) {
        $cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);

        // Build product summary
        $total_products = [];
        $total_price = 0;

        foreach ($cart_items as $item) {
            $total_products[] = $item['name'] . " (x" . $item['quantity'] . ")";
            $total_price += $item['price'] * $item['quantity'];
        }

        $products_string = implode(", ", $total_products);

        // Step 3: Get delivery info from session
        $delivery = $_SESSION['delivery_info'];
        $name = $delivery['name'];
        $email = $delivery['email'];
        $number = $delivery['phone'];

        // Combine full address
        $address = $delivery['address1'];
        if (!empty($delivery['address2'])) {
            $address .= ', ' . $delivery['address2'];
        }
        $address .= ', ' . $delivery['city'] . ', ' . $delivery['state'] . ', ' . $delivery['country'] . ' - ' . $delivery['zip'];

        $method = 'Khalti';
        $payment_status = 'completed';
        $placed_on = date('Y-m-d');

        // Step 4: Insert into orders
        $insert_order = $conn->prepare("INSERT INTO `orders` (user_id, name, number, email, address, method, total_products, total_price, payment_status, placed_on) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_order->execute([$user_id, $name, $number, $email, $address, $method, $products_string, $total_price, $payment_status, $placed_on]);

        // Step 5: Clear cart
        $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
        $delete_cart->execute([$user_id]);

        // Step 6: Clear delivery session
        unset($_SESSION['delivery_info']);
        unset($_SESSION['order_id']);
    }

    // ✅ Redirect to orders page
    header("Location: orders.php");
    exit();

} else {
    // ❌ Payment failed
    header("Location: cart.php");
    exit();
}
