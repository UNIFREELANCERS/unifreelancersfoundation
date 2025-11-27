<?php
/**
 * M-Pesa Configuration File
 * Central configuration for all M-Pesa related settings
 */

return [
    // Environment: 'production' or 'sandbox'
    'environment' => getenv('MPESA_ENV') ?? 'production',
    
    // M-Pesa Credentials
    'consumer_key' => getenv('MPESA_CONSUMER_KEY') ?? 'AP5uFUZQFBbYHaPWKYsAXvxuAgzRWTmQQFJEg0shV8n1q74C',
    'consumer_secret' => getenv('MPESA_CONSUMER_SECRET') ?? 'cqAiEFAvZDIpplcGZRfM1K6PfnnpCIKVAYwmdbKbZ3rCbJrrmBDGGAY33MTdqtDG',
    'passkey' => getenv('MPESA_PASSKEY') ?? 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
    'business_shortcode' => getenv('MPESA_SHORTCODE') ?? '174379',
    
    // URLs
    'callback_url' => getenv('MPESA_CALLBACK_URL') ?? 'https://unifreelancers.work/mpesa-express-simulate/',
    'timeout_url' => getenv('MPESA_TIMEOUT_URL') ?? 'https://unifreelancers.work/mpesa-timeout/',
    
    // API Endpoints (will be set based on environment)
    'endpoints' => [
        'production' => [
            'access_token' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
        ],
        'sandbox' => [
            'access_token' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
        ]
    ],
    
    // Logging
    'log_dir' => 'mpesa_logs',
    'log_transactions' => true,
    'log_callbacks' => true,
    
    // Donation settings
    'min_amount' => 100,
    'max_amount' => 500000,
    'currency' => 'KES',
    
    // Email notifications
    'send_confirmation_email' => true,
    'admin_email' => getenv('ADMIN_EMAIL') ?? 'donations@unifreelancers.work',
    'from_email' => getenv('FROM_EMAIL') ?? 'noreply@unifreelancers.work',
];
?>
