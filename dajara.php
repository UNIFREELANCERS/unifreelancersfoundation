<?php
// daraja.php - MPesa STK Push Sandbox Integration

// Sandbox credentials
$consumerKey = 'YOUR_CONSUMER_KEY';
$consumerSecret = 'YOUR_CONSUMER_SECRET';
$shortcode = '174379'; // Sandbox shortcode
$passkey = 'YOUR_PASSKEY';
$phoneNumber = '2547XXXXXXXX'; // Test phone number (sandbox)
$amount = 1; // Amount to charge
$callbackURL = 'https://yourdomain.com/callback.php'; // Your callback URL

// Get OAuth Token
function getAccessToken($consumerKey, $consumerSecret) {
    $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $data = json_decode($result);
    return $data->access_token ?? null;
}

// Initiate STK Push
function stkPush($accessToken, $shortcode, $passkey, $amount, $phoneNumber, $callbackURL) {
    $timestamp = date('YmdHis');
    $password = base64_encode($shortcode . $passkey . $timestamp);
    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

    $curl_post_data = [
        'BusinessShortCode' => $shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phoneNumber,
        'PartyB' => $shortcode,
        'PhoneNumber' => $phoneNumber,
        'CallBackURL' => $callbackURL,
        'AccountReference' => 'TestPayment',
        'TransactionDesc' => 'Test payment'
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken", "Content-Type: application/json"]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response, true);
}

// Execute
$accessToken = getAccessToken($consumerKey, $consumerSecret);

if ($accessToken) {
    $response = stkPush($accessToken, $shortcode, $passkey, $amount, $phoneNumber, $callbackURL);
    echo '<pre>';
    print_r($response);
    echo '</pre>';
} else {
    echo "Failed to get access token.";
}
?>
