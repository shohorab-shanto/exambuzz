# Postman Collection - Firebase Authentication Update

**Date:** October 22, 2025  
**Status:** ✅ Updated

---

## What Was Updated

### ✅ Exam App Complete API.postman_collection.json

**Added 2 new endpoints to Authentication section:**

1. **Firebase Google Login**
   - Method: `POST`
   - Endpoint: `{{base_url}}firebase/google-login`
   - Description: Firebase-based Google authentication for mobile apps

2. **Firebase Facebook Login**
   - Method: `POST`
   - Endpoint: `{{base_url}}firebase/facebook-login`
   - Description: Firebase-based Facebook authentication for mobile apps

**Total Authentication Endpoints:** 9 (was 7, now 9)

### ✅ Firebase_Authentication_API.postman_collection.json

**New standalone collection created:**
- Complete Firebase authentication documentation
- Includes request/response examples
- Sample responses for success, error, and validation cases
- Ready for import and testing

---

## Postman Collections Available

| Collection | Endpoints | Description |
|------------|-----------|-------------|
| **Exam App Complete API.postman_collection.json** | All endpoints including Firebase | Main collection with all API endpoints |
| **Firebase_Authentication_API.postman_collection.json** | Firebase auth only | Standalone Firebase authentication collection |
| **Exam App.postman_collection.json** | Legacy endpoints | Older collection (no Firebase) |

---

## How to Use in Postman

### Option 1: Use Complete Collection (Recommended)

1. **Import Collection**
   ```
   File → Import → Choose File
   Select: docs/Exam App Complete API.postman_collection.json
   ```

2. **Navigate to Authentication**
   ```
   Exam App - Complete API Collection 2025
   └── Authentication
       ├── Register
       ├── Login
       ├── Verify OTP
       ├── Resend OTP
       ├── Forgot Password
       ├── Reset Password
       ├── Logout
       ├── 🔥 Firebase Google Login  ← NEW
       └── 🔥 Firebase Facebook Login ← NEW
   ```

3. **Set Base URL**
   - Click on collection
   - Go to Variables tab
   - Set `base_url` to: `https://exambuzz.live/api/` (or your server URL)

4. **Test Firebase Login**
   - Open "Firebase Google Login"
   - Replace `firebase_token` with real Firebase ID token
   - Add optional `phone` if needed
   - Click Send
   - Copy `access_token` from response
   - Set as `token` variable for authenticated requests

### Option 2: Use Standalone Firebase Collection

1. **Import Collection**
   ```
   File → Import → Choose File
   Select: docs/Firebase_Authentication_API.postman_collection.json
   ```

2. **Use Firebase Endpoints**
   - Focused collection for Firebase only
   - Includes detailed documentation
   - Sample responses provided

---

## Firebase Endpoints Details

### POST /api/firebase/google-login

**Request (Token Only):**
```json
{
    "firebase_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6..."
}
```

**Note:** Phone is optional and can be omitted entirely.

**Success Response (200/201):**
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
        "phone": "01712345678",
        ...
    }
}
```

**Error Response (401):**
```json
{
    "status": false,
    "message": "Invalid Firebase token",
    "error": "Token verification failed"
}
```

### POST /api/firebase/facebook-login

Same request/response format as Google login.

---

## Testing Flow

1. **Get Firebase Token**
   - Use Firebase SDK in mobile app or test app
   - Authenticate user with Google/Facebook
   - Get Firebase ID token

2. **Send to API**
   - Open Postman
   - Select "Firebase Google Login" or "Firebase Facebook Login"
   - Paste Firebase token in request body
   - Send request

3. **Use Access Token**
   - Copy `access_token` from response
   - Set as environment/collection variable `token`
   - Use for all authenticated API requests:
     ```
     Authorization: Bearer {{token}}
     ```

---

## Environment Variables

Set these in Postman environment or collection variables:

| Variable | Example Value | Description |
|----------|---------------|-------------|
| `base_url` | `https://exambuzz.live/api/` | Your API base URL |
| `token` | `1|abc123...` | Bearer token after login |
| `firebase_token` | `eyJhbGciOi...` | Firebase ID token (for testing) |

---

## Collection Structure

```
Exam App - Complete API Collection 2025
├── Authentication
│   ├── Register
│   ├── Login
│   ├── Verify OTP
│   ├── Resend OTP
│   ├── Forgot Password
│   ├── Reset Password
│   ├── Logout
│   ├── 🔥 Firebase Google Login     ← NEW
│   └── 🔥 Firebase Facebook Login   ← NEW
├── User Profile
├── Exams
├── Written Exams
├── Materials
├── Packages
├── Notifications
├── Reviews
└── ... (other endpoints)
```

---

## Backup

Original collection backed up to:
```
docs/Exam App Complete API.postman_collection.json.backup
```

---

## What's Different from Old Social Login?

| Feature | Old Method | Firebase Method |
|---------|------------|-----------------|
| **Authentication** | Direct Google/Facebook OAuth | Firebase SDK → Firebase token → Our API |
| **Endpoint** | `/api/login/google` | `/api/firebase/google-login` |
| **Request** | User data (google_id, name, email) | Firebase ID token only |
| **Security** | Client provides user data | Token verified with Firebase |
| **Mobile Integration** | Custom OAuth flow | Firebase SDK (easier) |
| **Token Expiry** | No built-in | 1 hour (Firebase handles) |

### Both Methods Are Available

- ✅ **Old method** still works (backward compatible)
- ✅ **Firebase method** is more secure and recommended for new apps
- 🎯 **Migrate gradually** - no breaking changes

---

## Files Updated

```
✅ docs/Exam App Complete API.postman_collection.json
✅ docs/Firebase_Authentication_API.postman_collection.json (new)
✅ docs/POSTMAN_FIREBASE_UPDATE.md (this file)
```

---

## Summary

✅ **Main collection updated** - 2 Firebase endpoints added  
✅ **Standalone collection created** - Detailed Firebase documentation  
✅ **Backward compatible** - Old endpoints still available  
✅ **Ready to use** - Import and test immediately  

---

**Need Help?**
- Full setup guide: `docs/FIREBASE_AUTHENTICATION_SETUP.md`
- Quick start: `FIREBASE_LOGIN_QUICK_START.md`

**Last Updated:** October 22, 2025

