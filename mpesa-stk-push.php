<?php
/**
 * M-Pesa STK Push Integration for UNI Freelancers Donation
 * Using Darajani Payment Gateway
 */

header('Content-Type: application/json');

// M-Pesa Credentials
$consumerKey = 'AP5uFUZQFBbYHaPWKYsAXvxuAgzRWTmQQFJEg0shV8n1q74C';
$consumerSecret = 'cqAiEFAvZDIpplcGZRfM1K6PfnnpCIKVAYwmdbKbZ3rCbJrrmBDGGAY33MTdqtDG';
$passKey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
$businessShortCode = '174379';
$callbackUrl = 'https://unifreelancers.work/mpesa-express-simulate/';
$environment = 'production'; // or 'sandbox' for testing

// M-Pesa API endpoints
$accessTokenUrl = ($environment === 'sandbox') 
  ? 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
  : 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$stkPushUrl = ($environment === 'sandbox')
  ? 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
  : 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
  echo json_encode(['success' => false, 'message' => 'Invalid request']);
  exit;
}

$phoneNumber = preg_replace('/[^0-9]/', '', $input['phone']);
$amount = intval($input['amount']);
$donorName = sanitize($input['donor_name']);
$donorEmail = sanitize($input['donor_email']);
$purpose = sanitize($input['purpose']);

// Validation
if (empty($phoneNumber) || strlen($phoneNumber) < 12) {
  echo json_encode(['success' => false, 'message' => 'Invalid phone number']);
  exit;
}

if ($amount < 100) {
  echo json_encode(['success' => false, 'message' => 'Minimum amount is KES 100']);
  exit;
}

try {
  // Step 1: Get Access Token
  $accessToken = getAccessToken($consumerKey, $consumerSecret, $accessTokenUrl);
  
  if (!$accessToken) {
    throw new Exception('Failed to get access token');
  }
  
  // Step 2: Prepare STK Push Request
  $timestamp = date('YmdHis');
  $password = base64_encode($businessShortCode . $passKey . $timestamp);
  
  $transactionReference = 'UNI-DONATION-' . time() . '-' . rand(1000, 9999);
  
  $payload = [
    'BusinessShortCode' => $businessShortCode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $phoneNumber,
    'PartyB' => $businessShortCode,
    'PhoneNumber' => $phoneNumber,
    'CallBackURL' => $callbackUrl,
    'AccountReference' => $transactionReference,
    'TransactionDesc' => 'UNI Freelancers Donation - ' . $purpose,
    'Remark' => 'Donation from ' . $donorName
  ];
  
  // Step 3: Send STK Push Request
  $response = sendStkPushRequest($stkPushUrl, $payload, $accessToken);
  
  if (!$response) {
    throw new Exception('Failed to send STK Push request');
  }
  
  // Step 4: Log the transaction
  logTransaction($transactionReference, $phoneNumber, $amount, $donorName, $donorEmail, $purpose, $response);
  
  // Step 5: Return success response
  echo json_encode([
    'success' => true,
    'message' => 'Payment prompt sent to your phone',
    'transaction_reference' => $transactionReference,
    'response' => $response
  ]);
  
} catch (Exception $e) {
  error_log('M-Pesa STK Push Error: ' . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Error: ' . $e->getMessage()
  ]);
}

/**
 * Get M-Pesa Access Token
 */
function getAccessToken($consumerKey, $consumerSecret, $url) {
  $credentials = base64_encode($consumerKey . ':' . $consumerSecret);
  
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  
  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  
  if ($httpCode === 200) {
    $result = json_decode($response, true);
    return $result['access_token'] ?? null;
  }
  
  return null;
}

/**
 * Send STK Push Request
 */
function sendStkPushRequest($url, $payload, $accessToken) {
  $headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
  ];
  
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  
  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  
  if ($httpCode === 200) {
    return json_decode($response, true);
  }
  
  return null;
}

/**
 * Log transaction for records
 */
function logTransaction($transactionRef, $phone, $amount, $name, $email, $purpose, $response) {
  $logDir = 'mpesa_logs';
  if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
  }
  
  $logFile = $logDir . '/transactions_' . date('Y-m-d') . '.log';
  $logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'transaction_ref' => $transactionRef,
    'phone' => $phone,
    'amount' => $amount,
    'donor_name' => $name,
    'donor_email' => $email,
    'purpose' => $purpose,
    'response' => $response
  ];
  
  file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
}

/**
 * Sanitize input
 */
function sanitize($input) {
  return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
?>
