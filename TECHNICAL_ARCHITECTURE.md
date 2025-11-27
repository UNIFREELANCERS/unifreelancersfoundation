# M-Pesa Integration - Technical Architecture

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER (Browser)                       │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │              donate.html                                    │  │
│  │  • HTML Structure                                           │  │
│  │  • Two tabs: Message Form & M-Pesa Form                    │  │
│  │  • Tailwind CSS Styling                                    │  │
│  └──────────────────────┬──────────────────────────────────────┘  │
│                         │                                          │
│  ┌──────────────────────▼──────────────────────────────────────┐  │
│  │              mpesa-payment.js                               │  │
│  │  • Form Validation                                          │  │
│  │  • API Communication                                        │  │
│  │  • Error/Success Handling                                  │  │
│  │  • UI State Management                                     │  │
│  └──────────────────────┬──────────────────────────────────────┘  │
│                         │                                          │
└─────────────────────────┼──────────────────────────────────────────┘
                          │ HTTPS POST (JSON)
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    APPLICATION LAYER (Server)                       │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │         mpesa-stk-push.php (Main Controller)                │  │
│  │                                                             │  │
│  │  1. Receive POST Request                                   │  │
│  │     ├─ Phone Number                                        │  │
│  │     ├─ Amount                                              │  │
│  │     ├─ Donor Info                                          │  │
│  │     └─ Purpose                                             │  │
│  │                                                             │  │
│  │  2. Input Validation                                       │  │
│  │     ├─ Sanitize inputs                                     │  │
│  │     ├─ Validate format                                     │  │
│  │     └─ Verify amounts                                      │  │
│  │                                                             │  │
│  │  3. Load Configuration                                     │  │
│  │     └─ mpesa-config.php                                    │  │
│  │                                                             │  │
│  │  4. Generate Access Token                                  │  │
│  │     ├─ Consumer Key & Secret                               │  │
│  │     └─ Base64 Encoding                                     │  │
│  │                                                             │  │
│  │  5. Build STK Push Payload                                 │  │
│  │     ├─ Timestamp Generation                                │  │
│  │     ├─ Password Encryption                                 │  │
│  │     ├─ Transaction Reference                               │  │
│  │     └─ Callback URL                                        │  │
│  │                                                             │  │
│  │  6. Log Transaction                                        │  │
│  │     └─ Store in mpesa_logs/                                │  │
│  │                                                             │  │
│  │  7. Return Response                                        │  │
│  │     └─ JSON (success/error)                                │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                         │                                          │
└─────────────────────────┼──────────────────────────────────────────┘
                          │ HTTPS API Call (with Bearer Token)
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│              M-Pesa API LAYER (Safaricom)                           │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  1. Authentication Endpoint                                        │
│     https://api.safaricom.co.ke/oauth/v1/generate                  │
│     ├─ Returns: Access Token                                       │
│     └─ Valid for: 3600 seconds (1 hour)                            │
│                                                                     │
│  2. STK Push Endpoint                                              │
│     https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest    │
│     ├─ Input: Transaction Request                                  │
│     └─ Output: Checkout Request ID                                 │
│                                                                     │
│  3. M-Pesa Gateway                                                 │
│     ├─ Generates STK Push                                          │
│     ├─ Sends to Phone                                              │
│     └─ Waits for PIN Entry                                         │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│              MOBILE LAYER (Donor's Phone)                           │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  STK Push Dialog Box Appears                                       │
│  ┌───────────────────────────────────────────┐                    │
│  │  Enter Amount: 1000 KES                   │                    │
│  │  Receiving Party: UNI Freelancers         │                    │
│  │                                           │                    │
│  │  [Enter PIN] ••••                         │                    │
│  │                                           │                    │
│  │  [OK]  [CANCEL]                           │                    │
│  └───────────────────────────────────────────┘                    │
│                                                                     │
│  Donor Enters PIN → M-Pesa Processes                               │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                          │
                          ▼ (Success/Failure Response)
┌─────────────────────────────────────────────────────────────────────┐
│              CALLBACK LAYER (Webhook)                               │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  M-Pesa Sends Callback to:                                         │
│  https://unifreelancers.work/mpesa-callback.php                    │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │         mpesa-callback.php (Webhook Handler)                │  │
│  │                                                             │  │
│  │  1. Receive POST Request (Raw JSON)                         │  │
│  │                                                             │  │
│  │  2. Parse Callback Data                                     │  │
│  │     ├─ Result Code (0 = success)                            │  │
│  │     ├─ Amount                                               │  │
│  │     ├─ M-Pesa Reference                                     │  │
│  │     ├─ Phone Number                                         │  │
│  │     └─ Transaction Date                                     │  │
│  │                                                             │  │
│  │  3. Process Based on Result                                 │  │
│  │     ├─ If Success (0):                                      │  │
│  │     │  ├─ Save successful_transactions.json                │  │
│  │     │  ├─ Log successful_payments_*.log                    │  │
│  │     │  └─ Send confirmation (optional)                     │  │
│  │     │                                                       │  │
│  │     └─ If Failed (other codes):                             │  │
│  │        ├─ Save failed_transactions.json                    │  │
│  │        ├─ Log failed_payments_*.log                        │  │
│  │        └─ Alert admin                                      │  │
│  │                                                             │  │
│  │  4. Acknowledge Receipt                                     │  │
│  │     └─ Return HTTP 200 OK                                  │  │
│  │                                                             │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│                   STORAGE LAYER (File System)                       │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  mpesa_logs/ Directory                                             │
│  ├── callbacks_YYYY-MM-DD.log         [Raw callback data]          │
│  ├── successful_payments_YYYY-MM-DD.log [Success summary]          │
│  ├── failed_payments_YYYY-MM-DD.log   [Failure summary]            │
│  ├── successful_transactions.json     [Detailed success JSON]      │
│  └── failed_transactions.json         [Detailed failure JSON]      │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│                   MONITORING LAYER (Dashboard)                      │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │         mpesa-dashboard.php (Admin Interface)               │  │
│  │                                                             │  │
│  │  • Read transaction logs                                   │  │
│  │  • Display statistics                                      │  │
│  │  • Show recent transactions                                │  │
│  │  • Download reports                                        │  │
│  │  • Monitor system health                                   │  │
│  │                                                             │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Data Flow Sequence

```
Donor Enters Details
    ↓
JavaScript Validates
    ↓
HTTPS POST to mpesa-stk-push.php
    ↓
Backend Validates Input
    ↓
Get M-Pesa Access Token
    ↓
Create Encrypted Request
    ↓
Send to M-Pesa API
    ↓
M-Pesa Returns Checkout Request ID
    ↓
Response Sent to Browser
    ↓
Donor Sees "Check your phone"
    ↓
M-Pesa Sends STK Push to Phone
    ↓
Donor Enters PIN
    ↓
M-Pesa Processes Payment
    ↓
M-Pesa Sends Callback (HTTPS POST)
    ↓
mpesa-callback.php Receives Data
    ↓
Parse & Validate Callback
    ↓
Save Transaction (JSON/Logs)
    ↓
Return HTTP 200 OK
    ↓
Dashboard Shows Transaction
    ↓
Complete ✓
```

---

## API Endpoints

### 1. STK Push Endpoint (Backend)

**URL:** `/mpesa-stk-push.php`

**Method:** POST

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "phone": "254712345678",
  "amount": 1000,
  "donor_name": "John Doe",
  "donor_email": "john@example.com",
  "purpose": "General Fund"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Payment prompt sent to your phone",
  "transaction_reference": "UNI-DONATION-1234567890-1234",
  "response": {
    "ResponseCode": "0",
    "ResponseDescription": "Request accepted for processing",
    "MerchantRequestID": "...",
    "CheckoutRequestID": "..."
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Error message here"
}
```

---

### 2. M-Pesa Callback Endpoint

**URL:** `/mpesa-callback.php`

**Method:** POST (Sent by M-Pesa)

**Headers:**
```
Content-Type: application/json
```

**Request Body (Example):**
```json
{
  "Body": {
    "stkCallback": {
      "MerchantRequestID": "...",
      "CheckoutRequestID": "...",
      "ResultCode": 0,
      "ResultDesc": "The service request has been processed successfully.",
      "CallbackMetadata": {
        "Item": [
          {"Name": "Amount", "Value": 1000},
          {"Name": "MpesaReceiptNumber", "Value": "SJK12345"},
          {"Name": "TransactionDate", "Value": "20251128123456"},
          {"Name": "PhoneNumber", "Value": "254712345678"}
        ]
      }
    }
  }
}
```

---

## Database Schema (Optional)

If using database instead of JSON files:

```sql
CREATE TABLE donations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  transaction_ref VARCHAR(255) UNIQUE NOT NULL,
  merchant_request_id VARCHAR(255),
  checkout_request_id VARCHAR(255),
  phone VARCHAR(20) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  donor_name VARCHAR(100),
  donor_email VARCHAR(100),
  purpose VARCHAR(255),
  mpesa_ref VARCHAR(50),
  status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
  result_code INT,
  result_desc TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(transaction_ref),
  INDEX(phone),
  INDEX(status),
  INDEX(created_at)
);

CREATE TABLE donation_logs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  donation_id INT,
  event_type VARCHAR(50),
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(donation_id) REFERENCES donations(id),
  INDEX(donation_id),
  INDEX(created_at)
);
```

---

## Error Handling Strategy

```
Request → Validation
    │
    ├─ Invalid Format
    │  └─ Return 400 with error message
    │
    ├─ Missing Credentials
    │  └─ Return 500 with error message
    │
    └─ Valid
       ├─ Get Access Token
       │  ├─ Failure → Return 500
       │  └─ Success
       │
       ├─ Send STK Push
       │  ├─ Network Error → Return 503
       │  ├─ API Error → Return 400
       │  └─ Success
       │
       └─ Log & Return Response
          └─ Always log (even on error)
```

---

## Security Model

### Input Validation
```
Phone: Must be 12 digits (254...)
Amount: Must be 100-500000
Name: Max 100 chars, sanitized
Email: Valid email format
```

### Data Protection
```
Transit: HTTPS Only
Storage: JSON/Logs (plaintext acceptable for non-PII)
Phone: Masked in logs after first 9 chars
Credentials: Environment variables (not hardcoded)
```

### Access Control
```
STK Push: Public (no auth - rate limiting recommended)
Callback: No auth (but verify IP if possible)
Dashboard: Password protected
Admin: Strong password required
```

---

## Performance Considerations

### Response Times
- Form load: < 2s
- STK initiation: < 5s
- Callback receipt: < 30s
- Dashboard load: < 3s

### Scaling
- Current design: Up to 1000 requests/hour
- Logging: Daily rollover
- Storage: ~1MB per 1000 transactions

### Optimization
- Minimize PHP processing
- Use caching for access tokens (optional)
- Async callback processing (optional)
- CDN for static assets

---

## Monitoring & Alerts

### What to Monitor
- API response times
- Error rates
- Callback success rates
- Disk space (logs)
- Server resource usage

### Alert Thresholds
- Success rate < 90% → Alert
- Response time > 10s → Alert
- Failed callbacks > 5 in a row → Alert
- Disk space < 1GB → Alert

---

## Deployment Architecture

```
Production Server
├── Web Root (/public_html/)
│   ├── Frontend
│   │   ├── donate.html
│   │   ├── mpesa-payment.js
│   │   └── [other pages]
│   │
│   ├── Backend
│   │   ├── mpesa-stk-push.php
│   │   ├── mpesa-callback.php
│   │   └── mpesa-config.php
│   │
│   ├── Tools
│   │   ├── mpesa-dashboard.php
│   │   └── mpesa-test-simulate.php (remove in prod)
│   │
│   └── Logs (/mpesa_logs/)
│       ├── callbacks_*.log
│       ├── successful_*.log
│       ├── failed_*.log
│       ├── *.json
│       └── [daily rollover]
│
└── Environment
    ├── PHP 7.2+
    ├── cURL enabled
    ├── HTTPS configured
    └── Write permissions on mpesa_logs/
```

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 28-Nov-2025 | Initial release with M-Pesa STK Push |

---

**Architecture Version:** 1.0
**Last Updated:** November 28, 2025
