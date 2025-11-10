# bKash Payment Gateway Integration for Mobile App

## Overview

Complete bKash payment gateway integration for mobile app users to purchase packages using bKash Tokenized Checkout API v1.2.0-beta.

## Features

✅ **Automated Payment Flow**
- Create payment request
- Redirect to bKash payment page
- Automatic callback handling
- Auto-create package history record
- Real-time payment verification

✅ **Payment Management**
- Check payment status
- Search transactions
- Transaction tracking
- Error handling

✅ **Security**
- Token-based authentication
- Automatic token refresh
- SSL verification
- Sandbox/Production mode

---

## Architecture

```
Mobile App → API → bKash Gateway → User Payment → Callback → Package Activation
```

### Flow Diagram

```
1. User selects package
2. App calls /api/bkash/create-payment
3. API creates payment with bKash
4. API returns bkashURL
5. App opens bkashURL (WebView/Browser)
6. User completes payment on bKash
7. bKash redirects to callback URL
8. API verifies and executes payment
9. API creates package_history record
10. Returns success response to app
11. Package activated for user
```

---

## Environment Setup

### Required Environment Variables

Add these to your `.env` file:

```env
# bKash Configuration
SANDBOX=true                                    # true for sandbox, false for production
BKASH_USERNAME=your_sandbox_username            # Get from bKash merchant portal
BKASH_PASSWORD=your_sandbox_password            # Get from bKash merchant portal
BKASH_APP_KEY=your_sandbox_app_key             # Get from bKash merchant portal
BKASH_APP_SECRET=your_sandbox_app_secret       # Get from bKash merchant portal
```

### How to Get bKash Credentials

1. **Sandbox (Testing):**
   - Visit: https://developer.bka.sh/
   - Register/Login
   - Go to Dashboard → Apps
   - Create/Select your app
   - Get credentials from app details

2. **Production:**
   - Contact bKash merchant support
   - Complete merchant registration
   - Get production credentials
   - Set `SANDBOX=false` in .env

---

## API Endpoints

### Base URL
```
http://your-domain.com/api/bkash
```

### 1. Create Payment

**Endpoint:** `POST /api/bkash/create-payment`

**Purpose:** Initiate a bKash payment for package purchase

**Request:**
```json
{
  "package_id": 1,
  "user_id": 1,
  "amount": 1000
}
```

**Validation Rules:**
- `package_id`: required, must exist in packages table
- `user_id`: required, must exist in users table
- `amount`: required, numeric, minimum 1

**Success Response (200):**
```json
{
  "status": true,
  "message": "Payment created successfully",
  "data": {
    "bkashURL": "https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout?paymentID=TR0011abc123",
    "paymentID": "TR0011abc123",
    "merchantInvoiceNumber": "PKG_1_1_1699520000",
    "amount": "1000",
    "callbackURL": "http://localhost/api/bkash/callback"
  }
}
```

**Error Responses:**

*Validation Error (422):*
```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "package_id": ["The package id field is required."]
  }
}
```

*Package Not Available (400):*
```json
{
  "status": false,
  "message": "Package not available for purchase"
}
```

*bKash API Error (500):*
```json
{
  "status": false,
  "message": "Failed to create payment",
  "error": "Invalid credentials"
}
```

**Mobile App Implementation:**
```kotlin
// Kotlin Example
fun createPayment(packageId: Int, userId: Int, amount: Double) {
    val request = CreatePaymentRequest(packageId, userId, amount)
    
    apiService.createPayment(request).enqueue(object : Callback<PaymentResponse> {
        override fun onResponse(call: Call<PaymentResponse>, response: Response<PaymentResponse>) {
            if (response.isSuccessful) {
                val bkashURL = response.body()?.data?.bkashURL
                // Open bkashURL in WebView
                openBkashPayment(bkashURL)
            }
        }
        
        override fun onFailure(call: Call<PaymentResponse>, t: Throwable) {
            // Handle error
        }
    })
}

fun openBkashPayment(url: String) {
    // Open in WebView or Custom Tab
    val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
    startActivity(intent)
}
```

---

### 2. Payment Callback

**Endpoint:** `GET/POST /api/bkash/callback`

**Purpose:** Automatically called by bKash after payment completion

**⚠️ Note:** This endpoint is called automatically by bKash. You don't need to call it manually from the mobile app.

**Query Parameters (from bKash):**
- `paymentID`: Payment ID
- `status`: success/failure/cancel

**Success Response (200):**
```json
{
  "status": true,
  "message": "Payment completed successfully",
  "data": {
    "transactionID": "8HK7D15MNO",
    "paymentID": "TR0011abc123",
    "amount": "1000",
    "currency": "BDT",
    "customerMsisdn": "01700000000",
    "package": {
      "id": 1,
      "name": "BCS Premium Package",
      "validity": 365
    },
    "package_history_id": 15
  }
}
```

**What Happens in Callback:**
1. Receives callback from bKash
2. Executes payment with bKash
3. Verifies payment status
4. Creates `package_histories` record with:
   - user_id
   - package_id
   - amount
   - transaction_id (bKash trxID)
   - payment_method: "bkash"
   - payment_method_identity (phone number)
   - pg_transaction_id (paymentID)
   - bank_transaction_id (trxID)
   - payment_processes: "bkash_tokenized"
5. Returns success/failure response

**Mobile App Implementation:**

You can handle the callback in two ways:

**Option 1: WebView with JavaScript Interface**
```kotlin
webView.webViewClient = object : WebViewClient() {
    override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
        val url = request?.url.toString()
        if (url.contains("/api/bkash/callback")) {
            // Payment completed, close WebView
            // Refresh package status
            finish()
            return true
        }
        return false
    }
}
```

**Option 2: Custom URL Scheme**
```kotlin
// Register custom scheme in AndroidManifest.xml
<intent-filter>
    <action android:name="android.intent.action.VIEW" />
    <category android:name="android.intent.category.DEFAULT" />
    <category android:name="android.intent.category.BROWSABLE" />
    <data android:scheme="yourapp" android:host="payment" />
</intent-filter>

// Handle in Activity
override fun onCreate(savedInstanceState: Bundle?) {
    super.onCreate(savedInstanceState)
    
    val data = intent.data
    if (data != null && data.scheme == "yourapp") {
        // Payment completed
        handlePaymentCallback()
    }
}
```

---

### 3. Check Payment Status

**Endpoint:** `POST /api/bkash/check-payment-status`

**Purpose:** Check the status of a payment

**Request:**
```json
{
  "paymentID": "TR0011abc123"
}
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "Payment status retrieved",
  "data": {
    "paymentID": "TR0011abc123",
    "trxID": "8HK7D15MNO",
    "transactionStatus": "Completed",
    "amount": "1000",
    "currency": "BDT",
    "intent": "sale",
    "merchantInvoiceNumber": "PKG_1_1_1699520000",
    "paymentExecuteTime": "2025-11-10T10:30:00Z",
    "statusCode": "0000",
    "statusMessage": "Successful"
  }
}
```

**Use Cases:**
- Verify payment completion
- Check pending payments
- Debug payment issues
- Customer support queries

---

### 4. Search Transaction

**Endpoint:** `POST /api/bkash/search-transaction`

**Purpose:** Search for a transaction using bKash transaction ID

**Request:**
```json
{
  "trxID": "8HK7D15MNO"
}
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "Transaction found",
  "data": {
    "trxID": "8HK7D15MNO",
    "initiationTime": "2025-11-10T10:25:00Z",
    "completedTime": "2025-11-10T10:30:00Z",
    "transactionType": "Payment",
    "transactionStatus": "Completed",
    "amount": "1000",
    "currency": "BDT",
    "organizationShortCode": "123456"
  }
}
```

**Use Cases:**
- Find payment by TrxID
- Verify transaction authenticity
- Customer support queries
- Reconciliation

---

## Database Schema

### package_histories Table

The payment creates a record in `package_histories` table:

```sql
CREATE TABLE `package_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `amount` int NOT NULL,
  `giving_amount` int DEFAULT 0,
  `transaction_id` varchar(255) NOT NULL,              -- bKash trxID
  `payment_method` varchar(255) NULL,                  -- "bkash"
  `payment_method_identity` varchar(255) NULL,         -- Phone number
  `pg_transaction_id` varchar(255) NULL,               -- bKash paymentID
  `card_type` varchar(255) NULL,                       -- "mobile_wallet"
  `bank_transaction_id` varchar(255) NULL,             -- bKash trxID
  `payment_processes` varchar(255) NULL,               -- "bkash_tokenized"
  `approval_code` varchar(255) NULL,                   -- Merchant invoice number
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

### bkash_token Table

Auto-created for token management:

```sql
CREATE TABLE `bkash_token` (
  `sandbox_mode` tinyint(1) NOT NULL,
  `id_expiry` bigint NOT NULL,
  `id_token` varchar(2048) NOT NULL,
  `refresh_expiry` bigint NOT NULL,
  `refresh_token` varchar(2048) NOT NULL
);
```

---

## Testing

### 1. Using Postman

Import the collection: `docs/bKash_Payment_API.postman_collection.json`

**Setup:**
1. Set `base_url` variable: `http://localhost/api/`
2. Run `Get Packages` to see available packages
3. Run `Create Payment` with package_id, user_id, amount
4. Copy `bkashURL` and open in browser
5. Complete payment on bKash
6. Check callback response

### 2. Using Mobile App

**Test Flow:**
1. Select a package
2. Call create-payment API
3. Open bkashURL in WebView
4. Use test credentials (sandbox):
   - Wallet: 01770618567
   - OTP: 123456
   - PIN: 12345
5. Complete payment
6. Check package activation

### 3. bKash Sandbox Test Numbers

```
Wallet Number: 01770618567
OTP: 123456
PIN: 12345
```

---

## Error Handling

### Common Errors

**1. Invalid Credentials**
```json
{
  "status": false,
  "message": "Failed to create payment",
  "error": "Unauthorized"
}
```
**Solution:** Check BKASH_USERNAME, BKASH_PASSWORD, BKASH_APP_KEY, BKASH_APP_SECRET

**2. Token Expired**
- Automatically handled by token refresh mechanism
- Check logs if issue persists

**3. Payment Amount Too Low**
```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "amount": ["The amount must be at least 1."]
  }
}
```
**Solution:** Amount must be >= 1 BDT

**4. Package Not Available**
```json
{
  "status": false,
  "message": "Package not available for purchase"
}
```
**Solution:** Check package status and published_at date

**5. Payment Data Expired**
```json
{
  "status": false,
  "message": "Payment data not found or expired"
}
```
**Solution:** Payment data cached for 15 minutes. Complete payment within this time.

---

## Security Considerations

### 1. Environment Variables
- Never commit `.env` file
- Use different credentials for sandbox/production
- Rotate credentials periodically

### 2. Callback URL
- Ensure callback URL is accessible from internet
- Use HTTPS in production
- Validate callback parameters

### 3. Payment Verification
- Always verify payment status from bKash
- Don't trust client-side data only
- Log all transactions

### 4. Token Management
- Tokens auto-refresh before expiry
- Separate tokens for sandbox/production
- Tokens stored in database

---

## Production Checklist

Before going live:

- [ ] Get production credentials from bKash
- [ ] Set `SANDBOX=false` in .env
- [ ] Update BKASH_USERNAME, BKASH_PASSWORD
- [ ] Update BKASH_APP_KEY, BKASH_APP_SECRET
- [ ] Ensure callback URL uses HTTPS
- [ ] Test with real payments (small amounts)
- [ ] Set up payment monitoring
- [ ] Configure error alerts
- [ ] Enable bKash payment in admin panel
- [ ] Train support team
- [ ] Prepare customer communication

---

## Monitoring & Logs

### Log Locations

**Laravel Logs:**
```bash
tail -f storage/logs/laravel.log
```

**Key Log Events:**
- Payment creation
- Callback received
- Payment execution
- Token refresh
- Errors

**Example Log:**
```
[2025-11-10 10:30:00] local.INFO: bKash callback received 
{
  "paymentID":"TR0011abc123",
  "status":"success"
}

[2025-11-10 10:30:05] local.INFO: bKash payment response 
{
  "statusCode":"0000",
  "transactionStatus":"Completed",
  "trxID":"8HK7D15MNO"
}
```

---

## Troubleshooting

### Issue: bkashURL not opening

**Check:**
1. Internet connectivity
2. bKash credentials valid
3. SANDBOX mode correct
4. Firewall/proxy settings

### Issue: Callback not working

**Check:**
1. Callback URL accessible from internet
2. No authentication required on callback route
3. Server not blocking bKash IPs
4. Check server logs for errors

### Issue: Payment status shows pending

**Check:**
1. Payment completed on bKash?
2. Callback received?
3. Check payment status API
4. Contact bKash support if needed

### Issue: Package not activated

**Check:**
1. Payment status is "Completed"
2. Package history created (database)
3. User ID correct
4. Package ID valid

---

## Support

### bKash Support
- Website: https://developer.bka.sh/
- Email: support@bka.sh
- Phone: +880 2 58811995

### Project Documentation
- `docs/bKash_Payment_API.postman_collection.json` - Postman collection
- `docs/Exam App Complete API.postman_collection.json` - Complete API collection
- `app/Http/Controllers/Api/BkashPaymentController.php` - Controller
- `routes/api.php` - API routes

---

## Changelog

### Version 1.0 (November 10, 2025)
- Initial implementation
- Create payment API
- Callback handling
- Payment status check
- Transaction search
- Postman collection
- Documentation

---

## Next Steps

### Planned Enhancements
1. **Refund API** - Allow admins to refund payments
2. **Webhook Integration** - Real-time payment notifications
3. **Payment History** - User payment history in app
4. **Retry Mechanism** - Auto-retry failed payments
5. **Payment Analytics** - Dashboard for payment metrics
6. **Multiple Payment Methods** - Nagad, Rocket integration

---

## License

This integration follows bKash API Terms of Service and your application's license agreement.

