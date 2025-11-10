# Postman Collection - bKash Payment Gateway Update

## Update Summary

**Date:** November 10, 2025  
**Collection:** Exam App Complete API.postman_collection.json  
**Section:** Packages & Subscription

## What's New

Added **4 new endpoints** for bKash Payment Gateway integration to the existing Postman collection.

### New Endpoints

1. **bKash Create Payment**
   - Method: POST
   - Endpoint: `/api/bkash/create-payment`
   - Purpose: Create payment for package purchase
   - Auto-saves: `bkash_payment_id`, `bkash_merchant_invoice` to environment

2. **bKash Check Payment Status**
   - Method: POST
   - Endpoint: `/api/bkash/check-payment-status`
   - Purpose: Check payment status by paymentID

3. **bKash Search Transaction**
   - Method: POST
   - Endpoint: `/api/bkash/search-transaction`
   - Purpose: Search transaction by trxID

4. **bKash Callback (Info Only)**
   - Method: GET
   - Endpoint: `/api/bkash/callback`
   - Purpose: Information only - called automatically by bKash

## Features

### Test Scripts
The "Create Payment" endpoint includes automatic test scripts to save payment details:
```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    if (jsonData.data && jsonData.data.paymentID) {
        pm.environment.set('bkash_payment_id', jsonData.data.paymentID);
        pm.environment.set('bkash_merchant_invoice', jsonData.data.merchantInvoiceNumber);
    }
}
```

### Example Responses
Each endpoint includes multiple example responses:
- Success Response
- Validation Error
- Payment Failed
- Package Not Available

### Comprehensive Documentation
Each request includes detailed descriptions covering:
- Purpose and use cases
- Request parameters
- Response format
- Test credentials
- Mobile app integration code

## How to Use

### 1. Import Collection
```bash
File: docs/Exam App Complete API.postman_collection.json
```

### 2. Set Environment Variables
```javascript
base_url: http://localhost:8000/api/
token: (your auth token)
```

### 3. Test Payment Flow

**Step 1: Get Packages**
```
POST {{base_url}}packages
```
Note a package_id for testing.

**Step 2: Create Payment**
```
POST {{base_url}}bkash/create-payment

Body:
- package_id: 1
- user_id: 1
- amount: 1000
```

**Step 3: Open bkashURL**
Copy the `bkashURL` from response and open in browser.

**Step 4: Complete Payment**
Use test credentials:
- Wallet: 01770618567
- OTP: 123456
- PIN: 12345

**Step 5: Verify Payment**
```
POST {{base_url}}bkash/check-payment-status

Body:
- paymentID: {{bkash_payment_id}}
```

**Step 6: Check Package History**
```
POST {{base_url}}package-history

Body:
- user_id: 1
```

## Collection Structure

```
Exam App - Complete API Collection 2025
├── Authentication
├── Student Module
├── Teacher Module
├── User Profile
├── Packages & Subscription
│   ├── Get Packages
│   ├── Get Upcoming Packages
│   ├── Purchase Package
│   ├── Get Package History
│   ├── Get Package History V2
│   ├── bKash Create Payment ⭐ NEW
│   ├── bKash Check Payment Status ⭐ NEW
│   ├── bKash Search Transaction ⭐ NEW
│   └── bKash Callback (Info Only) ⭐ NEW
├── Revision Module
├── Material & Study Resources
└── Notifications
```

## Changes to Collection Info

Updated collection description to include:
- bKash Payment Gateway announcement
- Quick payment flow guide
- Test credentials
- Required environment variables
- Link to full documentation

## Environment Variables

The collection now uses these variables:
- `base_url` - API base URL
- `token` - Authentication token
- `bkash_payment_id` - Auto-saved from create payment
- `bkash_merchant_invoice` - Auto-saved from create payment

## Mobile App Integration Example

The collection includes Kotlin code examples in descriptions:

```kotlin
// Create Payment
apiService.createPayment(request).enqueue(object : Callback<PaymentResponse> {
    override fun onResponse(call: Call<PaymentResponse>, response: Response<PaymentResponse>) {
        if (response.isSuccessful) {
            val bkashURL = response.body()?.data?.bkashURL
            openBkashPayment(bkashURL)
        }
    }
})

// Handle Callback in WebView
webView.webViewClient = object : WebViewClient() {
    override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
        if (request?.url.toString().contains("/api/bkash/callback")) {
            finish()
            return true
        }
        return false
    }
}
```

## Testing Checklist

- [ ] Import updated collection
- [ ] Set `base_url` variable
- [ ] Configure bKash credentials in `.env`
- [ ] Run "Get Packages" to see available packages
- [ ] Run "bKash Create Payment"
- [ ] Verify `bkash_payment_id` is saved
- [ ] Open `bkashURL` in browser
- [ ] Complete payment with test credentials
- [ ] Run "bKash Check Payment Status"
- [ ] Verify payment is completed
- [ ] Check package history shows new purchase

## Related Files

1. **API Controller:**
   - `app/Http/Controllers/Api/BkashPaymentController.php`

2. **Routes:**
   - `routes/api.php` (lines 282-288)

3. **Documentation:**
   - `docs/BKASH_PAYMENT_INTEGRATION.md` - Complete documentation
   - `docs/BKASH_QUICK_START.md` - Quick start guide
   - `docs/README.md` - Updated with bKash section
   - `BKASH_IMPLEMENTATION_SUMMARY.md` - Implementation summary

4. **Postman Collections:**
   - `docs/Exam App Complete API.postman_collection.json` - Main collection (UPDATED)
   - `docs/bKash_Payment_API.postman_collection.json` - Standalone bKash collection

## Benefits

1. **All-in-One Collection**: Developers have all APIs in one place
2. **Auto-Save Variables**: Payment IDs automatically saved for subsequent requests
3. **Example Responses**: Multiple scenarios covered with examples
4. **Code Examples**: Mobile app integration code in descriptions
5. **Test Scripts**: Automatic environment variable management
6. **Comprehensive Docs**: Detailed descriptions for each endpoint

## Migration from Standalone Collection

If you were using the standalone `bKash_Payment_API.postman_collection.json`:

**Before:**
- Import separate bKash collection
- Maintain two collections

**After:**
- Use the updated main collection
- All APIs in one place
- Shared environment variables

The standalone collection is still available for those who prefer it, but the main collection now includes everything.

## Support

For issues or questions:
1. Check endpoint descriptions in Postman
2. Review `docs/BKASH_PAYMENT_INTEGRATION.md`
3. Check Laravel logs: `storage/logs/laravel.log`
4. Contact bKash support: https://developer.bka.sh/

---

**Last Updated:** November 10, 2025  
**Collection Version:** 2025 (Complete)  
**bKash API Version:** v1.2.0-beta
