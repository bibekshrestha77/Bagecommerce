<?php
session_start();
require 'components/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check required delivery fields
$required_fields = ['name', 'email', 'phone', 'address1', 'city', 'state', 'country', 'zip'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        die("Delivery details are required.");
    }
}

// Sanitize and store delivery info in session
$_SESSION['delivery_info'] = [
    'name'     => htmlspecialchars(trim($_POST['name'])),
    'email'    => htmlspecialchars(trim($_POST['email'])),
    'phone'    => htmlspecialchars(trim($_POST['phone'])),
    'address1' => htmlspecialchars(trim($_POST['address1'])),
    'address2' => htmlspecialchars(trim($_POST['address2'] ?? '')),
    'city'     => htmlspecialchars(trim($_POST['city'])),
    'state'    => htmlspecialchars(trim($_POST['state'])),
    'country'  => htmlspecialchars(trim($_POST['country'])),
    'zip'      => htmlspecialchars(trim($_POST['zip']))
];

// Fetch cart items
$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
$select_cart->execute([$user_id]);

if ($select_cart->rowCount() == 0) {
    die("Your cart is empty.");
}

$cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
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

// Add VAT (13%)
$vat_amount = round($amount_total * 0.13);
$amount_breakdown[] = [
    "label" => "VAT (13%)",
    "amount" => $vat_amount
];
$amount_total += $vat_amount;

// Generate unique order ID
$order_id = 'ORDER' . rand(10000, 99999);
$_SESSION['order_id'] = $order_id;

// Prepare Khalti request
$data = [
    "return_url" => "http://localhost/bagecommerce/khalti_verify.php",
    "website_url" => "http://localhost/bagecommerce",
    "amount" => $amount_total,
    "purchase_order_id" => $order_id,
    "purchase_order_name" => "Bag Purchase",
    "customer_info" => [
        "name"  => $_SESSION['delivery_info']['name'],
        "email" => $_SESSION['delivery_info']['email'],
        "phone" => $_SESSION['delivery_info']['phone']
    ],
    "amount_breakdown" => $amount_breakdown,
    "product_details" => $product_details
];

$payload = json_encode($data);

// Send to Khalti
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
