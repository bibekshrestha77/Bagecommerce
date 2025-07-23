<?php
session_start();
require 'components/connect.php'; // Make sure this points to your DB connection file

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items for user
$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
$select_cart->execute([$user_id]);

if ($select_cart->rowCount() == 0) {
    die("Your cart is empty.");
}

$cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);

// Build product details and calculate total
$amount_total = 0;
$product_details = [];
$amount_breakdown = [];

foreach ($cart_items as $item) {
    $price_paisa = $item['price'] * 100;
    $qty = $item['quantity'];
    $total_price = $price_paisa * $qty;

    $product_details[] = [
        "identity" => $item['pid'],
        "name" => $item['name'],
        "total_price" => $total_price,
        "quantity" => $qty,
        "unit_price" => $price_paisa
    ];

    $amount_breakdown[] = [
        "label" => $item['name'],
        "amount" => $total_price
    ];

    $amount_total += $total_price;
}

// Optional VAT logic (e.g., 13% VAT)
$vat_amount = round($amount_total * 0.13);
$amount_breakdown[] = [
    "label" => "VAT (13%)",
    "amount" => $vat_amount
];
$amount_total += $vat_amount;

// Build request payload
$order_id = 'ORDER' . rand(1000, 9999);
$data = [
    "return_url" => "http://localhost/bagecommerce/khalti_verify.php",
    "website_url" => "http://localhost/bagecommerce",
    "amount" => $amount_total,
    "purchase_order_id" => $order_id,
    "purchase_order_name" => "Bag Purchase",
    "customer_info" => [
        "name" => "Test Customer",
        "email" => "test@example.com",
        "phone" => "9800000001"
    ],
    "amount_breakdown" => $amount_breakdown,
    "product_details" => $product_details
];

$payload = json_encode($data);

// Initiate Khalti request
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://a.khalti.com/api/v2/epayment/initiate/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $payload,
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

if (isset($result['payment_url'])) {
    header("Location: " . $result['payment_url']);
    exit;
} else {
    echo "<pre>Initiation Failed:\n";
    print_r($result);
    echo "</pre>";
}
