# Firebase Google/Facebook Login - Quick Start Guide

## ✅ What's Done

All backend code is complete and ready! Here's what's been implemented:

### Backend (Laravel API)
- ✅ Firebase Admin SDK installed (v7.16.0)
- ✅ `FirebaseAuthController` created
- ✅ Google login endpoint: `POST /api/firebase/google-login`
- ✅ Facebook login endpoint: `POST /api/firebase/facebook-login`
- ✅ Database migration: `firebase_uid` column added
- ✅ Configuration files created
- ✅ Postman collection created
- ✅ Complete documentation created

### Files Created/Modified
```
✅ app/Http/Controllers/Api/FirebaseAuthController.php
✅ config/firebase.php
✅ database/migrations/2025_10_22_133719_add_firebase_uid_to_users_table.php
✅ routes/api.php (updated)
✅ .env (Firebase config added)
✅ docs/Firebase_Authentication_API.postman_collection.json
✅ docs/FIREBASE_AUTHENTICATION_SETUP.md
```

## 🎯 Quick Setup (3 Steps)

### Step 1: Firebase Console Setup (5 minutes)

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create/select project
3. Enable Authentication → Google & Facebook
4. Download service account JSON:
   - Project Settings → Service Accounts → Generate new private key
5. Save file as: `storage/app/firebase/firebase-credentials.json`

### Step 2: Update .env (1 minute)

```env
FIREBASE_ENABLED=true
FIREBASE_PROJECT_ID=your-actual-project-id
FIREBASE_CREDENTIALS=storage/app/firebase/firebase-credentials.json
```

Replace `your-actual-project-id` with your Firebase project ID.

### Step 3: Clear Cache (10 seconds)

```bash
php artisan config:clear
php artisan config:cache
```

**Done! Backend is ready! 🎉**

## 📱 Mobile App Integration

Your mobile app needs to:

1. **Install Firebase SDK**
   - Android: `implementation 'com.google.firebase:firebase-auth:22.3.0'`
   - iOS: `pod 'Firebase/Auth'`
   - Flutter: `firebase_auth: ^4.15.0`

2. **Authenticate with Firebase**
   ```
   User taps "Login with Google" 
   → Firebase SDK handles authentication
   → Firebase returns ID token
   ```

3. **Send Token to Our API**
   ```http
   POST https://exambuzz.live/api/firebase/google-login
   Content-Type: application/json

   {
       "firebase_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjY4ZGUx..."
   }
   ```

4. **Get Bearer Token**
   ```json
   {
       "status": true,
       "access_token": "1|abc123...",
       "user": {...}
   }
   ```

5. **Use Token for API Calls**
   ```http
   Authorization: Bearer 1|abc123...
   ```

## 🧪 Testing with Postman

1. **Import Collection**
   - Open Postman
   - Import: `docs/Firebase_Authentication_API.postman_collection.json`

2. **Get Test Token** (for testing only)
   - Use Firebase Auth Emulator, or
   - Create test user in Firebase Console
   - Use Firebase SDK in a test app to get token

3. **Test API**
   - Open "Firebase Google Login" request
   - Paste real Firebase token
   - Send request
   - Should receive 200/201 with access_token

## 📋 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/firebase/google-login` | Google login via Firebase |
| POST | `/api/firebase/facebook-login` | Facebook login via Firebase |

### Request Format
```json
{
    "firebase_token": "FIREBASE_ID_TOKEN_HERE",
    "phone": "01712345678"
}
```

### Response Format
```json
{
    "status": true,
    "message": "Login successful",
    "token_type": "Bearer",
    "access_token": "1|abcdef...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "firebase_uid": "...",
        ...
    }
}
```

## 🔒 Security

- ✅ Firebase token is verified server-side
- ✅ Invalid tokens are rejected (401)
- ✅ Email is auto-verified for Firebase users
- ✅ Service account credentials are secure
- ✅ HTTPS required in production

## 🎓 Code Examples

### Android (Kotlin)
```kotlin
// Get Firebase token
auth.currentUser?.getIdToken(true)?.addOnCompleteListener { task ->
    val token = task.result?.token
    sendToAPI(token!!)
}

// Send to API
fun sendToAPI(token: String) {
    val json = JSONObject().put("firebase_token", token)
    // POST to /api/firebase/google-login
}
```

### Flutter
```dart
// Get Firebase token
String? token = await FirebaseAuth.instance.currentUser?.getIdToken();

// Send to API
final response = await http.post(
  Uri.parse('https://exambuzz.live/api/firebase/google-login'),
  body: jsonEncode({'firebase_token': token}),
);
```

### React Native
```javascript
// Get Firebase token
const token = await auth().currentUser.getIdToken();

// Send to API
fetch('https://exambuzz.live/api/firebase/google-login', {
  method: 'POST',
  body: JSON.stringify({ firebase_token: token }),
});
```

## 📚 Full Documentation

- **Setup Guide:** `docs/FIREBASE_AUTHENTICATION_SETUP.md`
- **Postman Collection:** `docs/Firebase_Authentication_API.postman_collection.json`
- **Mobile Code Examples:** See setup guide for complete implementations

## ❓ Common Questions

**Q: Do I need to keep the old Google/Facebook OAuth?**  
A: No. Firebase method is better. Old endpoints are kept for backward compatibility.

**Q: Can I test without a mobile app?**  
A: You need Firebase SDK to get a valid token. Use Firebase Auth Emulator or test app.

**Q: Where do I get the Firebase token?**  
A: Your mobile app gets it from Firebase SDK after user authentication.

**Q: How long does the token last?**  
A: Firebase tokens expire in 1 hour. Mobile app should refresh automatically.

**Q: Is this secure?**  
A: Yes! Token is verified with Firebase on every request. Can't be faked.

## 🚀 Next Steps

1. ✅ Backend is ready
2. ⏳ Setup Firebase Console (5 min)
3. ⏳ Upload service account file
4. ⏳ Update .env
5. ⏳ Implement in mobile app
6. ⏳ Test and deploy!

## 📞 Support

- **Firebase Docs:** https://firebase.google.com/docs/auth
- **PHP SDK Docs:** https://firebase-php.readthedocs.io/
- **Postman Collection:** Included in `docs/`

---

**Status:** ✅ **READY FOR USE**  
**Last Updated:** October 22, 2025  
**Version:** Firebase PHP SDK v7.16.0

