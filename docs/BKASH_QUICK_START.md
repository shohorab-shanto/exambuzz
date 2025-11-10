# bKash Payment - Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Step 1: Configure Environment Variables

Add to your `.env` file:

```env
SANDBOX=true
BKASH_USERNAME=sandboxTokenizedUser02
BKASH_PASSWORD=sandboxTokenizedUser02@12345
BKASH_APP_KEY=4f6o0cjiki2rfm34kfdadl1eqq
BKASH_APP_SECRET=2is7hdktrekvrbljjh44ll3d9l1dtjo4pasmjvs5vl5qr3fug4b
```

### Step 2: Enable bKash Payment

1. Login to admin panel
2. Go to **Company Info** settings
3. Set **bKash Payment Enable** to **Enable**

### Step 3: Test the API

#### Using Postman:

**Import Collection:**
```
docs/bKash_Payment_API.postman_collection.json
```

**Set Variable:**
- `base_url` = `http://localhost/api/`

**Test Flow:**

1. **Get Packages**
   ```
   POST {{base_url}}packages
   ```

2. **Create Payment**
   ```
   POST {{base_url}}bkash/create-payment
   
   Body:
   {
     "package_id": 1,
     "user_id": 1,
     "amount": 1000
   }
   ```

3. **Open bkashURL** (from response) in browser

4. **Complete Payment:**
   - Wallet: `01770618567`
   - OTP: `123456`
   - PIN: `12345`

5. **Verify Package History:**
   ```
   POST {{base_url}}package-history
   
   Body:
   {
     "user_id": 1
   }
   ```

### Step 4: Mobile App Integration

**Create Payment:**
```kotlin
// Kotlin Example
data class CreatePaymentRequest(
    val package_id: Int,
    val user_id: Int,
    val amount: Double
)

// API Call
apiService.createPayment(CreatePaymentRequest(1, 1, 1000.0))
    .enqueue(object : Callback<PaymentResponse> {
        override fun onResponse(call: Call, response: Response) {
            val bkashURL = response.body()?.data?.bkashURL
            // Open in WebView
            openWebView(bkashURL)
        }
    })
```

**Handle Callback:**
```kotlin
webView.webViewClient = object : WebViewClient() {
    override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
        if (request?.url.toString().contains("/api/bkash/callback")) {
            // Payment completed
            finish()
            return true
        }
        return false
    }
}
```

---

## 📱 API Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/bkash/create-payment` | Create payment |
| GET/POST | `/api/bkash/callback` | Payment callback (auto) |
| POST | `/api/bkash/check-payment-status` | Check status |
| POST | `/api/bkash/search-transaction` | Search by TrxID |

---

## ✅ Checklist

- [ ] Add environment variables to `.env`
- [ ] Enable bKash in admin panel
- [ ] Import Postman collection
- [ ] Test create payment
- [ ] Complete test payment
- [ ] Verify package history created
- [ ] Integrate in mobile app
- [ ] Test end-to-end flow

---

## 🆘 Quick Troubleshooting

**Problem: "Failed to create payment"**
- Check bKash credentials in `.env`
- Verify `SANDBOX=true`

**Problem: "Package not available"**
- Check package status is 1 (active)
- Check package published_at date

**Problem: "Payment data expired"**
- Complete payment within 15 minutes
- Try creating new payment

**Problem: Callback not working**
- Check callback URL is accessible
- Check server logs: `tail -f storage/logs/laravel.log`

---

## 📚 Full Documentation

For detailed documentation, see:
- `docs/BKASH_PAYMENT_INTEGRATION.md` - Complete guide
- `docs/bKash_Payment_API.postman_collection.json` - Postman collection

---

## 🎉 You're Ready!

Your bKash payment integration is complete. Start testing!

