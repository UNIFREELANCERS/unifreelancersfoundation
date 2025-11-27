<?php
// callback.php - STK Push response handler

// Get the raw POST data from Safaricom
$callbackJSONData = file_get_contents('php://input');

// Decode the JSON data
$callbackData = json_decode($callbackJSONData, true);

// Optional: log the full response to a file for debugging
$logFile = 'mpesa_callback_log.txt';
$logEntry = date('Y-m-d H:i:s') . " - " . $callbackJSONData . "\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Check if callback data exists
if (isset($callbackData['Body']['stkCallback'])) {
    $stkCallback = $callbackData['Body']['stkCallback'];

    $merchantRequestID = $stkCallback['MerchantRequestID'] ?? '';
    $checkoutRequestID = $stkCallback['CheckoutRequestID'] ?? '';
    $resultCode = $stkCallback['ResultCode'] ?? '';
    $resultDesc = $stkCallback['ResultDesc'] ?? '';

    // Optional: payment details if successful
    if ($resultCode == 0 && isset($stkCallback['CallbackMetadata']['Item'])) {
        $items = $stkCallback['CallbackMetadata']['Item'];
        $amount = 0;
        $mpesaReceiptNumber = '';
        $transactionDate = '';

        foreach ($items as $item) {
            if ($item['Name'] == 'Amount') $amount = $item['Value'];
            if ($item['Name'] == 'MpesaReceiptNumber') $mpesaReceiptNumber = $item['Value'];
            if ($item['Name'] == 'TransactionDate') $transactionDate = $item['Value'];
        }

        // Here you can store the payment info in your database
        // Example: savePayment($checkoutRequestID, $mpesaReceiptNumber, $amount, $transactionDate);

        // Log successful payment
        $logEntrySuccess = date('Y-m-d H:i:s') . " - SUCCESS: Amount: $amount, Receipt: $mpesaReceiptNumber, Date: $transactionDate\n";
        file_put_contents($logFile, $logEntrySuccess, FILE_APPEND);
    }

    // You can also handle failed payments
    else {
        $logEntryFail = date('Y-m-d H:i:s') . " - FAILED: ResultCode: $resultCode, Desc: $resultDesc\n";
        file_put_contents($logFile, $logEntryFail, FILE_APPEND);
    }

    // Respond back to Safaricom
    header('Content-Type: application/json');
    echo json_encode([
        'ResultCode' => 0,
        'ResultDesc' => 'Callback received successfully'
    ]);
} else {
    // Invalid callback data
    header('HTTP/1.1 400 Bad Request');
    echo 'Invalid callback data';
}
?>
