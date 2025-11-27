<?php
/**
 * M-Pesa Callback Handler
 * This file receives and processes M-Pesa STK Push responses
 */

// Log all incoming requests
$logDir = 'mpesa_logs';
if (!is_dir($logDir)) {
  mkdir($logDir, 0755, true);
}

$callbackFile = $logDir . '/callbacks_' . date('Y-m-d') . '.log';

// Get the callback data
$callback = file_get_contents('php://input');

// Log the callback
file_put_contents($callbackFile, 
  "===== " . date('Y-m-d H:i:s') . " =====" . PHP_EOL .
  $callback . PHP_EOL . PHP_EOL,
  FILE_APPEND
);

// Parse callback data
$data = json_decode($callback, true);

if ($data) {
  // Extract key information
  $stk_push_result = $data['Body']['stkCallback'] ?? [];
  $resultCode = $stk_push_result['ResultCode'] ?? null;
  $resultDesc = $stk_push_result['ResultDesc'] ?? '';
  $merchantRequestID = $stk_push_result['MerchantRequestID'] ?? '';
  $checkoutRequestID = $stk_push_result['CheckoutRequestID'] ?? '';
  
  // Get callback metadata
  $callbackMetadata = $stk_push_result['CallbackMetadata']['Item'] ?? [];
  $callbackData = [];
  
  foreach ($callbackMetadata as $item) {
    $callbackData[$item['Name']] = $item['Value'] ?? null;
  }
  
  // Process based on result code
  if ($resultCode == 0) {
    // Success - payment completed
    $amount = $callbackData['Amount'] ?? 0;
    $mpesaRef = $callbackData['MpesaReceiptNumber'] ?? '';
    $phone = $callbackData['PhoneNumber'] ?? '';
    $transactionDate = $callbackData['TransactionDate'] ?? '';
    
    // Save successful transaction
    saveSuccessfulTransaction([
      'merchant_request_id' => $merchantRequestID,
      'checkout_request_id' => $checkoutRequestID,
      'amount' => $amount,
      'mpesa_ref' => $mpesaRef,
      'phone' => $phone,
      'transaction_date' => $transactionDate
    ]);
    
    // Send confirmation email (optional)
    // sendConfirmationEmail($phone, $amount, $mpesaRef);
    
    // Log success
    file_put_contents($logDir . '/successful_payments_' . date('Y-m-d') . '.log',
      date('Y-m-d H:i:s') . " - Amount: {$amount}, Phone: {$phone}, Ref: {$mpesaRef}" . PHP_EOL,
      FILE_APPEND
    );
    
  } else {
    // Failed or cancelled
    saveFailedTransaction([
      'merchant_request_id' => $merchantRequestID,
      'checkout_request_id' => $checkoutRequestID,
      'result_code' => $resultCode,
      'result_desc' => $resultDesc
    ]);
    
    // Log failure
    file_put_contents($logDir . '/failed_payments_' . date('Y-m-d') . '.log',
      date('Y-m-d H:i:s') . " - Code: {$resultCode}, Message: {$resultDesc}" . PHP_EOL,
      FILE_APPEND
    );
  }
}

// Acknowledge receipt
http_response_code(200);
echo json_encode(['status' => 'ok']);

/**
 * Save successful transaction to database or file
 */
function saveSuccessfulTransaction($data) {
  $logDir = 'mpesa_logs';
  $file = $logDir . '/successful_transactions.json';
  
  $transactions = [];
  if (file_exists($file)) {
    $transactions = json_decode(file_get_contents($file), true) ?? [];
  }
  
  $data['timestamp'] = date('Y-m-d H:i:s');
  $transactions[] = $data;
  
  file_put_contents($file, json_encode($transactions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

/**
 * Save failed transaction to database or file
 */
function saveFailedTransaction($data) {
  $logDir = 'mpesa_logs';
  $file = $logDir . '/failed_transactions.json';
  
  $transactions = [];
  if (file_exists($file)) {
    $transactions = json_decode(file_get_contents($file), true) ?? [];
  }
  
  $data['timestamp'] = date('Y-m-d H:i:s');
  $transactions[] = $data;
  
  file_put_contents($file, json_encode($transactions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

/**
 * Send confirmation email (optional - configure with your email service)
 */
function sendConfirmationEmail($phone, $amount, $mpesaRef) {
  // Example using PHP mail or your email service
  $to = 'donations@unifreelancers.work'; // Change to your email
  $subject = 'Donation Received - M-Pesa Reference: ' . $mpesaRef;
  $message = "A donation of KES {$amount} has been received from {$phone}.\n";
  $message .= "M-Pesa Reference: {$mpesaRef}\n";
  $message .= "Thank you for supporting UNI Freelancers!\n";
  
  // mail($to, $subject, $message);
}
?>
