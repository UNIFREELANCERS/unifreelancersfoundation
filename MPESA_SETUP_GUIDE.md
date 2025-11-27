# M-Pesa STK Push Integration Guide for UNI Freelancers

## Overview
This integration adds M-Pesa STK Push payment capability to your UNI Freelancers donation page, allowing donors to make instant mobile money donations directly from their M-Pesa accounts.

## Files Included

### Frontend Files
- **donate.html** - Updated donation page with M-Pesa payment option
- **mpesa-payment.js** - JavaScript for handling payment form and API calls

### Backend Files
- **mpesa-stk-push.php** - Main STK Push request handler
- **mpesa-callback.php** - Receives and processes M-Pesa responses
- **mpesa-config.php** - Centralized configuration file
- **mpesa-test-simulate.php** - Testing/simulation tool (use only in development)

### Documentation
- **README.md** - This file

## Credentials Already Configured

Your M-Pesa credentials have been embedded in the system:

```
Consumer Key: AP5uFUZQFBbYHaPWKYsAXvxuAgzRWTmQQFJEg0shV8n1q74C
Consumer Secret: cqAiEFAvZDIpplcGZRfM1K6PfnnpCIKVAYwmdbKbZ3rCbJrrmBDGGAY33MTdqtDG
Pass Key: bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
Business Short Code: 174379
Callback URL: https://unifreelancers.work/mpesa-express-simulate/
```

## Prerequisites

- PHP 7.2 or higher with cURL support
- HTTPS enabled on your domain (required by M-Pesa)
- Ability to set up webhook/callback URLs
- Web server with write permissions for logging directory

## Installation & Deployment Steps

### Step 1: Upload Files to Your Server

Upload all the PHP and HTML files to your web server:

```
/public_html/
├── donate.html
├── mpesa-payment.js
├── mpesa-stk-push.php
├── mpesa-callback.php
├── mpesa-config.php
└── mpesa-logs/ (will be created automatically)
```

### Step 2: Create Logging Directory

The system will automatically create `mpesa_logs` directory, but ensure your web server has write permissions:

```bash
# Via terminal on your server
mkdir -p mpesa_logs
chmod 755 mpesa_logs
```

### Step 3: Update Your M-Pesa Callback URL

1. Log in to your M-Pesa developer portal
2. Go to Application Settings
3. Update the callback URL to: `https://unifreelancers.work/mpesa-callback.php`
4. Save changes

### Step 4: Verify HTTPS

M-Pesa requires HTTPS. Ensure:
- Your domain has valid SSL certificate
- All requests use `https://` not `http://`

### Step 5: Test the Integration

#### Option A: Manual Testing with Simulator

1. Access the test simulator: `https://unifreelancers.work/mpesa-test-simulate.php`

2. Make a POST request with the following:
```json
{
  "secret": "test-secret-key-123",
  "action": "simulate_success",
  "phone": "254712345678",
  "amount": 1000
}
```

**Important:** Change the secret token in `mpesa-test-simulate.php` after testing!

#### Option B: Live Testing

1. Visit your donation page: `https://unifreelancers.work/donate.html`
2. Click on "Pay with M-Pesa" tab
3. Enter test details:
   - Name: Test Donor
   - Email: test@example.com
   - Phone: 254712345678 (your M-Pesa registered phone)
   - Amount: 100 (minimum)
4. Click "Initiate M-Pesa Payment"
5. Check your phone for the STK Push prompt
6. Enter your M-Pesa PIN

### Step 6: Monitor Transactions

Transactions are logged in the `mpesa_logs` directory:

- **callbacks_YYYY-MM-DD.log** - All raw callbacks
- **successful_payments_YYYY-MM-DD.log** - Successful payments
- **failed_payments_YYYY-MM-DD.log** - Failed payments
- **successful_transactions.json** - Detailed successful transaction data
- **failed_transactions.json** - Detailed failed transaction data

## How It Works

### Payment Flow

1. **Donor Enters Details**
   - Name, Email, Phone, Amount, Purpose
   - Form validates in browser (JavaScript)

2. **Form Submission**
   - JavaScript sends JSON to `mpesa-stk-push.php`
   - Backend validates credentials

3. **Access Token Generation**
   - Backend requests access token from M-Pesa API
   - Token used for authentication

4. **STK Push Request**
   - Backend sends encrypted request to M-Pesa
   - M-Pesa displays pop-up on donor's phone

5. **Donor Authorization**
   - Donor enters M-Pesa PIN
   - Payment is processed

6. **Callback Received**
   - M-Pesa sends callback to `mpesa-callback.php`
   - Success/failure is logged
   - Transaction data is stored

## Configuration Options

Edit `mpesa-config.php` to customize:

```php
// Environment
'environment' => 'production', // or 'sandbox'

// Donation limits
'min_amount' => 100,
'max_amount' => 500000,

// Email notifications
'send_confirmation_email' => true,
'admin_email' => 'donations@unifreelancers.work',

// Logging
'log_transactions' => true,
```

## API Response Codes

### Success
- **0** - Payment successful

### Common Errors
- **1** - Insufficient funds
- **1032** - Request cancelled by user
- **1037** - Invalid account number
- **9999** - Unknown error

## Troubleshooting

### Issue: "Failed to get access token"
- ✓ Verify credentials in `mpesa-stk-push.php`
- ✓ Check consumer key and secret
- ✓ Ensure cURL is enabled: `php -m | grep curl`

### Issue: "Invalid phone number"
- ✓ Phone must include country code (254 for Kenya)
- ✓ Must be 12 digits total (e.g., 254712345678)
- ✓ Must be M-Pesa registered account

### Issue: Callback not received
- ✓ Verify callback URL is HTTPS
- ✓ Ensure firewall allows incoming requests
- ✓ Check M-Pesa portal for callback configuration
- ✓ Review `mpesa_logs/callbacks_*.log` for errors

### Issue: CORS errors on frontend
- ✓ Ensure donation page and backend are on same domain
- ✓ Check browser console for specific errors
- ✓ Verify HTTPS is used everywhere

## Security Recommendations

1. **Remove Test File Before Production**
   - Delete `mpesa-test-simulate.php` after testing
   - Or add IP whitelist/authentication

2. **Use Environment Variables**
   - Don't hardcode credentials
   - Use .env file with sensitive data

   ```php
   // Instead of hardcoding:
   $consumerKey = getenv('MPESA_CONSUMER_KEY');
   ```

3. **Validate Callbacks**
   - Verify callback signature/authenticity
   - Log all callbacks
   - Validate amount matches before processing

4. **HTTPS Only**
   - Redirect HTTP to HTTPS
   - Use security headers

5. **Rate Limiting**
   - Implement rate limiting on STK push endpoint
   - Prevent abuse/fraud

6. **Data Storage**
   - If using database, encrypt sensitive data
   - Regular backups of logs
   - Follow PCI compliance if storing payment data

## Switching Between Sandbox and Production

To test in sandbox mode first:

1. Edit `mpesa-stk-push.php`:
   ```php
   $environment = 'sandbox'; // Change to 'production' for live
   ```

2. Use M-Pesa sandbox test credentials from the developer portal

3. After testing successfully, change to production

## Email Notifications (Optional)

To enable confirmation emails:

1. Uncomment in `mpesa-callback.php`:
   ```php
   sendConfirmationEmail($phone, $amount, $mpesaRef);
   ```

2. Configure your email service (SMTP/mail function)

3. Update admin email in `mpesa-config.php`

## Database Integration (Optional)

To save transactions to database instead of JSON files:

1. Create donors table:
```sql
CREATE TABLE donations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  transaction_ref VARCHAR(255) UNIQUE,
  phone VARCHAR(20),
  amount DECIMAL(10,2),
  donor_name VARCHAR(100),
  donor_email VARCHAR(100),
  purpose VARCHAR(255),
  mpesa_ref VARCHAR(50),
  status ENUM('pending', 'success', 'failed'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

2. Modify callback handler to save to database

## Support & Maintenance

- **Regular Log Review** - Check logs weekly for issues
- **Backup Logs** - Archive old log files monthly
- **Update Documentation** - Keep records of changes
- **Monitor Transactions** - Track donation volume and amounts

## Additional Resources

- [M-Pesa Developer Portal](https://developer.safaricom.co.ke/)
- [M-Pesa API Documentation](https://safaricom.entropix.io/)
- [STK Push Integration Guide](https://safaricom.entropix.io/docs/stk_push.html)

## Notes

- Minimum donation: KES 100
- Maximum donation: KES 500,000
- Donations are processed in KES (Kenyan Shilling)
- Transaction fees apply as per M-Pesa rates
- All transactions are logged and encrypted appropriately

## Support

For issues or questions:
1. Check logs in `mpesa_logs` directory
2. Review error messages in browser console
3. Verify credentials and URLs
4. Test with simulator first before live testing

---

**Deployment Status:** ✅ Ready for Testing & Deployment

**Last Updated:** November 28, 2025
