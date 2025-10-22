# Postman Collections - Firebase Token-Only Authentication

## ✅ Updated Collections

All Postman collections have been updated to show the **token-only approach**.

---

## 📦 Available Collections

### 1. **Exam App Complete API.postman_collection.json** (Main)
- Complete API collection with all endpoints
- Firebase Google Login ✅
- Firebase Facebook Login ✅
- All other app endpoints

### 2. **Firebase_Authentication_API.postman_collection.json** (Standalone)
- Dedicated Firebase authentication collection
- Detailed documentation
- Request/response examples
- Error scenarios

---

## 🔥 Firebase Endpoints

### **POST /api/firebase/google-login**

**Request Body:**
```json
{
    "firebase_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6..."
}
```

**That's it!** No other fields required.

### **POST /api/firebase/facebook-login**

**Request Body:**
```json
{
    "firebase_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6..."
}
```

Same as Google - just the token!

---

## 📋 What to Send

| Field | Required | Description |
|-------|----------|-------------|
| `firebase_token` | ✅ Required | Firebase ID token from mobile app |
| `phone` | ⭕ Optional | Can be omitted completely |

---

## 🎁 Response Format

```json
{
    "status": true,
    "message": "Login successful",
    "token_type": "Bearer",
    "access_token": "1|abcdefghijklmnopqrstuvwxyz...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "firebase_uid": "firebase_uid_12345",
        "phone": null,
        "image": "https://lh3.googleusercontent.com/a/...",
        "email_verified_at": "2025-10-22T10:30:00.000000Z",
        "registration_id": "2025000001",
        "status": 1
    }
}
```

---

## 🚀 How to Use in Postman

1. **Import Collection**
   ```
   File → Import → Select collection file
   ```

2. **Set Base URL**
   - Collection Variables → `base_url`
   - Set to: `https://exambuzz.live/api/`

3. **Test Firebase Login**
   - Open `Firebase Google Login` request
   - Replace `firebase_token` value with real token
   - Click **Send**

4. **Save Access Token**
   - Copy `access_token` from response
   - Set as collection variable `token`
   - Use for authenticated requests

---

## 🔑 Getting Firebase Token

To test the API, you need a real Firebase ID token from:

1. **Mobile App with Firebase SDK** (recommended)
2. **Firebase Auth Emulator** (for testing)
3. **Test Web App** with Firebase initialized

You cannot manually create or fake Firebase tokens - they must come from Firebase SDK.

---

## 📱 Mobile App Request Example

```javascript
// Get Firebase token
const token = await auth().currentUser.getIdToken();

// Send to API
fetch('https://exambuzz.live/api/firebase/google-login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    firebase_token: token,
    // That's all!
  }),
});
```

---

## ✨ What Backend Extracts

From the Firebase token, backend automatically gets:

- ✅ **Name** (from Firebase displayName)
- ✅ **Email** (from Firebase email)
- ✅ **Profile Photo** (from Firebase photoUrl)
- ✅ **Firebase UID** (unique identifier)
- ⭕ **Phone** (optional - from request if provided)

---

## 📚 Collections Structure

```
Exam App - Complete API Collection
├── Authentication
│   ├── Register
│   ├── Login
│   ├── Verify OTP
│   ├── Resend OTP
│   ├── Forgot Password
│   ├── Reset Password
│   ├── Logout
│   ├── 🔥 Firebase Google Login    ← Token only!
│   └── 🔥 Firebase Facebook Login  ← Token only!
├── ... (other endpoints)
```

---

## 🎯 Key Changes

### Before:
```json
{
    "firebase_token": "...",
    "phone": "01712345678"
}
```

### After:
```json
{
    "firebase_token": "..."
}
```

**Simpler!** Phone is optional and can be added later.

---

## 💡 Pro Tips

1. **Token-only is simpler** - Just send the token
2. **Phone is optional** - Don't block users without phone
3. **Use environment variables** - Set `base_url` and `token` as variables
4. **Save responses** - Create example responses for your team
5. **Test both providers** - Google and Facebook use same format

---

## 📖 Related Documentation

- `FIREBASE_TOKEN_ONLY_APPROACH.md` - Complete guide
- `FIREBASE_LOGIN_QUICK_START.md` - Quick setup
- `docs/FIREBASE_AUTHENTICATION_SETUP.md` - Full documentation

---

**Last Updated:** October 22, 2025  
**Status:** ✅ All collections updated to token-only approach
