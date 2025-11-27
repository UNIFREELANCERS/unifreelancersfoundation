<?php
/**
 * M-Pesa STK Push - Testing/Simulation Mode
 * This file allows you to simulate M-Pesa callbacks for testing purposes
 * 
 * NOTE: Only use this in development/testing. Remove before production.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  die('Only POST requests allowed');
}

// Security: Add a secret token for testing
$testingSecret = 'test-secret-key-123'; // Change this to something random
$submittedSecret = $_POST['secret'] ?? '';

if ($submittedSecret !== $testingSecret) {
  echo json_encode(['error' => 'Invalid secret']);
  exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'simulate_success') {
  simulateSuccessfulPayment();
} elseif ($action === 'simulate_failure') {
  simulateFailedPayment();
} elseif ($action === 'simulate_cancelled') {
  simulateCancelledPayment();
} else {
  echo json_encode(['error' => 'Invalid action']);
}

function simulateSuccessfulPayment() {
  $phone = $_POST['phone'] ?? '254712345678';
  $amount = $_POST['amount'] ?? 1000;
  $mpesaRef = 'SJK' . time();
  
  $callbackData = [
    'Body' => [
      'stkCallback' => [
        'MerchantRequestID' => 'test-' . time(),
        'CheckoutRequestID' => 'ws_CO_' . time(),
        'ResultCode' => 0,
        'ResultDesc' => 'The service request has been processed successfully.',
        'CallbackMetadata' => [
          'Item' => [
            ['Name' => 'Amount', 'Value' => $amount],
            ['Name' => 'MpesaReceiptNumber', 'Value' => $mpesaRef],
            ['Name' => 'TransactionDate', 'Value' => date('YmdHis')],
            ['Name' => 'PhoneNumber', 'Value' => $phone]
          ]
        ]
      ]
    ]
  ];
  
  sendCallbackToHandler($callbackData);
}

function simulateFailedPayment() {
  $callbackData = [
    'Body' => [
      'stkCallback' => [
        'MerchantRequestID' => 'test-' . time(),
        'CheckoutRequestID' => 'ws_CO_' . time(),
        'ResultCode' => 1,
        'ResultDesc' => 'Check your entries and try again.'
      ]
    ]
  ];
  
  sendCallbackToHandler($callbackData);
}

function simulateCancelledPayment() {
  $callbackData = [
    'Body' => [
      'stkCallback' => [
        'MerchantRequestID' => 'test-' . time(),
        'CheckoutRequestID' => 'ws_CO_' . time(),
        'ResultCode' => 1032,
        'ResultDesc' => 'Request cancelled by user'
      ]
    ]
  ];
  
  sendCallbackToHandler($callbackData);
}

function sendCallbackToHandler($data) {
  // Send to callback handler
  $callbackUrl = 'mpesa-callback.php';
  
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $callbackUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  
  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  
  echo json_encode([
    'success' => true,
    'message' => 'Callback sent successfully',
    'http_code' => $httpCode,
    'response' => $response
  ]);
}
?>
