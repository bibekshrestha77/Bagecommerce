<?php
// verify.php

// Get the JSON payload from frontend
$data = json_decode(file_get_contents("php://input"));

$token = $data->token;
$amount = $data->amount;

// Your Khalti secret key
$secret_key = "test_secret_key_6f3c5e109bdb4cc5a3f...";

$url = "https://khalti.com/api/v2/payment/verify/";

$args = http_build_query(array(
    "token" => $token,
    "amount" => $amount
));

$headers = [ 
    "Authorization: Key $secret_key"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status_code == 200) {
    // Decode the response to get details
    $response_data = json_decode($response, true);

    // You can store this transaction in your database here
    echo json_encode([
        "message" => "✅ Payment verified successfully! TXN ID: " . $response_data['idx']
    ]);
} else {
    echo json_encode([
        "message" => "❌ Payment verification failed!"
    ]);
}
?>
