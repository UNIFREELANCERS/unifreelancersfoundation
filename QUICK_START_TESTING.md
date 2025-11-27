# M-Pesa Integration - Quick Start Testing Guide

## Pre-Deployment Checklist

Before going live, verify all components are in place:

- [ ] All PHP files uploaded to server
- [ ] All JavaScript files uploaded to server
- [ ] Updated HTML donation page in place
- [ ] `mpesa_logs` directory created with write permissions
- [ ] HTTPS enabled on domain
- [ ] Callback URL updated in M-Pesa portal
- [ ] Credentials verified and correct

## Testing Scenarios

### Test 1: Form Validation (No Backend Call)

**Objective:** Verify frontend validation works

1. Open donation page: `/donate.html`
2. Click "Pay with M-Pesa" tab
3. Try submitting with empty fields
4. **Expected:** Error message: "Please fill in all fields"

4. Try amount less than 100
5. **Expected:** Error message: "Minimum amount is KES 100"

6. Try invalid phone number (less than 12 digits)
7. **Expected:** Error message: "Phone number must be 12 digits"

---

### Test 2: Backend Connection (STK Push)

**Objective:** Verify backend can reach M-Pesa API

1. Fill donation form completely:
   - Name: `John Doe`
   - Email: `john@example.com`
   - Phone: `254712345678` (your actual M-Pesa phone)
   - Amount: `100`
   - Purpose: `General Fund`

2. Click "Initiate M-Pesa Payment"

3. **Expected Results:**
   - Loading message appears
   - After 2-5 seconds: Success message
   - Phone receives STK Push prompt
   - Browser shows transaction reference

4. **If error:** Check browser console (F12 > Console)
   - Note exact error message
   - Check `/mpesa_logs/callbacks_*.log` for details

---

### Test 3: Simulated Payment (Testing Environment)

If you want to test without actual M-Pesa payment:

**Using Test Simulator:**

```bash
# Make a test successful payment callback
curl -X POST https://unifreelancers.work/mpesa-test-simulate.php \
  -d "secret=test-secret-key-123" \
  -d "action=simulate_success" \
  -d "phone=254712345678" \
  -d "amount=1000"
```

**Expected:** Callback logged successfully

---

### Test 4: Live Payment Test

**Objective:** Complete actual payment flow

1. Complete donation form with real details
2. Click "Initiate M-Pesa Payment"
3. When STK prompt appears on phone, enter M-Pesa PIN
4. Payment processes
5. **Expected:**
   - Receive "Payment successful" SMS from M-Pesa
   - Browser shows success confirmation
   - Transaction logged in `mpesa_logs/successful_payments_*.log`
   - Check transaction details in `mpesa_logs/successful_transactions.json`

---

## Debugging Steps

### Step 1: Check PHP Errors

1. Access your server via SSH
2. Check PHP error logs:
   ```bash
   tail -f /var/log/php-fpm/error.log
   # or
   tail -f /var/log/apache2/error.log
   ```

### Step 2: Verify M-Pesa Logs

```bash
# List all log files
ls -la mpesa_logs/

# View latest callback
tail mpesa_logs/callbacks_*.log

# View successful transactions
cat mpesa_logs/successful_transactions.json | json_pp

# View failed transactions
cat mpesa_logs/failed_transactions.json | json_pp
```

### Step 3: Test M-Pesa Connectivity

```bash
# Test if cURL can reach M-Pesa
curl -v https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials \
  -u "YOUR_CONSUMER_KEY:YOUR_CONSUMER_SECRET"
```

### Step 4: Verify Callback Reception

1. Make a test payment
2. Immediately check logs:
   ```bash
   tail -f mpesa_logs/callbacks_*.log
   ```
3. Should see raw callback data within 30 seconds

---

## Common Test Issues & Solutions

### Issue: "Failed to get access token"

**Causes:**
- Invalid credentials
- cURL not enabled
- Firewall blocking M-Pesa API

**Solution:**
```php
// Test credentials directly in PHP
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode('KEY:SECRET')]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
echo $response;
```

---

### Issue: "Callback not received"

**Causes:**
- Callback URL not updated in M-Pesa portal
- Firewall blocking incoming requests
- HTTPS issues

**Solution:**
1. Verify callback URL:
   ```bash
   # Test your callback endpoint
   curl -X POST https://unifreelancers.work/mpesa-callback.php \
     -H "Content-Type: application/json" \
     -d '{"test": "data"}'
   ```

2. Check firewall rules allow inbound on 443 (HTTPS)

3. Verify SSL certificate is valid:
   ```bash
   openssl s_client -connect unifreelancers.work:443
   ```

---

### Issue: "Invalid phone number"

**Causes:**
- Missing country code
- Too few/many digits
- Not M-Pesa registered account

**Solution:**
- Ensure format: `254712345678` (12 digits)
- First 3 digits: `254` (Kenya)
- Rest: Your actual phone without leading 0
- If phone is `0712345678`, use `254712345678`

---

## Performance Testing

### Load Test (Simulated Multiple Payments)

```bash
# Generate 10 simultaneous payment requests
for i in {1..10}; do
  (curl -X POST https://unifreelancers.work/mpesa-stk-push.php \
    -H "Content-Type: application/json" \
    -d '{
      "phone": "254712345678",
      "amount": 100,
      "donor_name": "Test '$i'",
      "donor_email": "test'$i'@example.com",
      "purpose": "Testing"
    }') &
done
wait
```

Monitor resource usage during test.

---

## Security Testing Checklist

- [ ] Test with invalid credentials - should fail gracefully
- [ ] Test with XSS injection in name field - should be escaped
- [ ] Test with SQL injection in email - should be safe
- [ ] Test HTTPS redirect - HTTP should redirect to HTTPS
- [ ] Test callback signature verification (if available)
- [ ] Verify sensitive logs are not publicly accessible

---

## Deployment Readiness Checklist

Once all tests pass:

- [ ] Remove test simulator file (`mpesa-test-simulate.php`)
- [ ] Set environment to 'production' in code
- [ ] Configure email notifications
- [ ] Set up log rotation/archival
- [ ] Test actual donations with real money (small amount)
- [ ] Verify invoice/receipt generation (if applicable)
- [ ] Test refund process
- [ ] Set up monitoring/alerts
- [ ] Document access procedures for team
- [ ] Schedule regular log review
- [ ] Backup configuration

---

## Quick Reference: File Locations

| File | Purpose | Edit Needed? |
|------|---------|--------------|
| `donate.html` | Frontend form | Update links if needed |
| `mpesa-payment.js` | Form handling | No |
| `mpesa-stk-push.php` | Payment processing | No (credentials embedded) |
| `mpesa-callback.php` | Callback handling | No |
| `mpesa-config.php` | Configuration | Yes (for production settings) |
| `mpesa-test-simulate.php` | Testing only | DELETE before production |

---

## Support Contacts

- **M-Pesa Support:** +254 7811 000 111
- **Your Support Email:** donations@unifreelancers.work
- **Technical Issues:** Check `mpesa_logs/` directory

---

**Status:** Ready for Testing
**Last Updated:** November 28, 2025
