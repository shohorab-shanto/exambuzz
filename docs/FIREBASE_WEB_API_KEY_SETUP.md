# Firebase Authentication Setup - Web API Key Method

## Overview
Simple Firebase Google/Facebook login using **Web API Key** (no JSON file required).

## ✨ Why This Approach?

**Advantages:**
- ✅ **Simple setup** - Just one environment variable
- ✅ **No JSON file** management
- ✅ **Easy deployment** - No file uploads needed
- ✅ **Lightweight** - Uses HTTP REST API instead of heavy SDK
- ✅ **Works perfectly** for authentication

## 🔑 Required Credentials

You only need **ONE credential**: The Firebase Web API Key

### How to Get Firebase Web API Key

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project (or create new one)
3. Click ⚙️ **Settings** → **Project Settings**
4. Go to **General** tab
5. Scroll to **Your apps** section or **Web API Key** field
6. Copy the **Web API Key** 
   - Example: `AIzaSyAbCdEfGhIjKlMnOpQrStUvWxYz1234567`

## ⚙️ Setup Steps

### Step 1: Enable Google Sign-In in Firebase

1. Firebase Console → **Authentication** → **Sign-in method**
2. Click **Google** provider
3. Toggle **Enable**
4. Select a support email
5. Click **Save**

### Step 2: (Optional) Enable Facebook Sign-In

1. Same screen → Click **Facebook**
2. Enable and add Facebook App ID and Secret
3. Copy OAuth redirect URI to Facebook app settings

### Step 3: Update .env File

Add these variables to your `.env`:

```env
# Firebase Web API Key
FIREBASE_WEB_API_KEY=AIzaSyAbCdEfGhIjKlMnOpQrStUvWxYz1234567
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_ENABLED=true
```

**Where to find Project ID:**
- Firebase Console → Project Settings → General → Project ID

### Step 4: Clear Config Cache

```bash
php artisan config:clear
php artisan config:cache
```

### Step 5: Test the API

Use Postman or test with:

```bash
curl -X POST https://your-domain.com/api/firebase/google-login \
  -H "Content-Type: application/json" \
  -d '{
    "firebase_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjY4ZGU...",
    "phone": "01712345678"
  }'
```

## 🔧 How It Works

### Architecture

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│             │         │              │         │             │
│  Mobile App │────1───▶│   Firebase   │────2───▶│   Our API   │
│             │         │              │         │             │
└─────────────┘         └──────────────┘         └─────────────┘
                                                        │
                                                        │ 3. Verify
                                                        ▼
                                            ┌─────────────────────┐
                                            │  Firebase REST API  │
                                            │  (Web API Key)      │
                                            └─────────────────────┘
```

1. Mobile app authenticates with Firebase SDK
2. Mobile app gets Firebase ID Token
3. Mobile app sends token to our API
4. **Our API verifies token with Firebase REST API** using Web API Key
5. Our API returns Bearer token for subsequent requests

### Backend Token Verification

```php
// Verify token using Firebase REST API
POST https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={WEB_API_KEY}
Body: { "idToken": "firebase_id_token" }

// Response contains user data:
{
  "users": [{
    "localId": "firebase_uid_12345",
    "email": "user@example.com",
    "displayName": "John Doe",
    "photoUrl": "https://...",
    "emailVerified": true
  }]
}
```

## 📱 Mobile App Implementation

### Android (Kotlin)

```kotlin
import com.google.firebase.auth.FirebaseAuth
import com.google.android.gms.auth.api.signin.GoogleSignIn
import com.google.android.gms.auth.api.signin.GoogleSignInOptions

// Configure Google Sign-In
val gso = GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
    .requestIdToken(getString(R.string.default_web_client_id)) // From google-services.json
    .requestEmail()
    .build()

val googleSignInClient = GoogleSignIn.getClient(this, gso)

// Sign in
val signInIntent = googleSignInClient.signInIntent
startActivityForResult(signInIntent, RC_SIGN_IN)

// After Google sign-in success
val credential = GoogleAuthProvider.getCredential(idToken, null)
FirebaseAuth.getInstance().signInWithCredential(credential)
    .addOnCompleteListener { task ->
        if (task.isSuccessful) {
            // Get Firebase ID Token
            FirebaseAuth.getInstance().currentUser?.getIdToken(true)
                ?.addOnCompleteListener { tokenTask ->
                    val firebaseToken = tokenTask.result?.token
                    // Send to your API
                    sendToBackend(firebaseToken)
                }
        }
    }
```

### Flutter

```dart
import 'package:firebase_auth/firebase_auth.dart';
import 'package:google_sign_in/google_sign_in.dart';

Future<void> signInWithGoogle() async {
  final GoogleSignInAccount? googleUser = await GoogleSignIn().signIn();
  final GoogleSignInAuthentication googleAuth = await googleUser!.authentication;

  final credential = GoogleAuthProvider.credential(
    accessToken: googleAuth.accessToken,
    idToken: googleAuth.idToken,
  );

  UserCredential userCredential = await FirebaseAuth.instance
      .signInWithCredential(credential);

  // Get Firebase ID Token
  String? firebaseToken = await userCredential.user?.getIdToken();

  // Send to your API
  sendToBackend(firebaseToken);
}
```

### React Native

```javascript
import auth from '@react-native-firebase/auth';
import { GoogleSignin } from '@react-native-google-signin/google-signin';

async function signInWithGoogle() {
  const { idToken } = await GoogleSignin.signIn();
  const googleCredential = auth.GoogleAuthProvider.credential(idToken);
  const userCredential = await auth().signInWithCredential(googleCredential);
  
  // Get Firebase ID Token
  const firebaseToken = await userCredential.user.getIdToken();
  
  // Send to backend
  sendToBackend(firebaseToken);
}
```

## 🎯 API Endpoints

### Google Login
```
POST /api/firebase/google-login
```

**Request:**
```json
{
  "firebase_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjY4ZGU...",
  "phone": "01712345678"
}
```

**Response (Success):**
```json
{
  "status": true,
  "message": "Login successful",
  "token_type": "Bearer",
  "access_token": "1|abcdefghijklmnopqrstuvwxyz123456789",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "01712345678",
    "firebase_uid": "firebase_uid_12345",
    "status": 1
  }
}
```

### Facebook Login
```
POST /api/firebase/facebook-login
```

Same request/response format.

## 🔐 Security Notes

### Web API Key Security

⚠️ **Important:** The Firebase Web API Key is a **public key** meant for client-side use.

**This is SAFE because:**
- ✅ It's designed to be public (used in mobile apps, web apps)
- ✅ Firebase has built-in security rules and rate limiting
- ✅ Token verification happens server-side
- ✅ Can't be used to access data without proper authentication

**Additional Security:**
- Add Firebase App Check for production
- Use Firebase Security Rules
- Implement rate limiting on your API
- Monitor Firebase usage in console

### Rate Limiting

Add to your API routes:

```php
Route::post('/firebase/google-login', [FirebaseAuthController::class, 'googleLogin'])
    ->middleware('throttle:10,1'); // 10 requests per minute
```

## 🚀 Quick Start

### Complete Setup in 3 Steps:

```bash
# 1. Add to .env
echo "FIREBASE_WEB_API_KEY=AIzaSyAbCdEfGhIjKlMnOpQrStUvWxYz1234567" >> .env
echo "FIREBASE_PROJECT_ID=your-project-id" >> .env
echo "FIREBASE_ENABLED=true" >> .env

# 2. Clear cache
php artisan config:clear

# 3. Test!
# Use Postman collection: docs/Firebase_Authentication_API.postman_collection.json
```

That's it! No JSON files, no complicated setup.

## 📦 Dependencies

**Already included in your project:**
- Laravel HTTP Client (built-in)
- No additional packages needed!

**You DON'T need:**
- ❌ kreait/firebase-php (Admin SDK)
- ❌ firebase-credentials.json
- ❌ Service account setup

## 🆚 Comparison with Service Account Method

| Feature | Web API Key | Service Account JSON |
|---------|-------------|---------------------|
| Setup Complexity | ⭐ Simple | ⭐⭐⭐ Complex |
| File Management | None | JSON file required |
| Security | Good (public key) | Best (private key) |
| Deployment | Easy | Requires file upload |
| Dependencies | None | kreait/firebase-php |
| Token Verification | REST API | Admin SDK |
| User Management | Limited | Full access |
| Rate Limits | Yes | No |
| **Recommended For** | Most projects | Enterprise/Complex |

## ✅ What's Implemented

- ✅ Firebase token verification via REST API
- ✅ Google login (`POST /api/firebase/google-login`)
- ✅ Facebook login (`POST /api/firebase/facebook-login`)
- ✅ Auto user creation/linking
- ✅ Bearer token authentication
- ✅ Phone number optional
- ✅ Email verification support

## 🧪 Testing

### 1. Test with Mobile App

Use Firebase SDK in your app, get the ID token, send to API.

### 2. Test with Firebase Console

1. Firebase Console → Authentication → Users
2. Create test user with Google provider
3. Use Firebase SDK in web/mobile to authenticate
4. Extract ID token from Firebase
5. Send to your API endpoint

### 3. Test with Postman

Import: `docs/Firebase_Authentication_API.postman_collection.json`

**Note:** You need a real Firebase ID token from mobile app or web app. You cannot generate valid tokens manually.

## 🔍 Troubleshooting

### Error: "Firebase not configured"

**Solution:**
```bash
# Check .env has the key
grep FIREBASE_WEB_API_KEY .env

# Clear config
php artisan config:clear
```

### Error: "Invalid Firebase token"

**Possible causes:**
1. Token expired (valid for 1 hour)
2. Wrong Web API Key
3. Token from different Firebase project
4. Authentication not enabled in Firebase Console

**Solution:**
- Get fresh token from mobile app
- Verify Web API Key is correct
- Check Firebase project ID matches
- Enable Google sign-in in Firebase Console

### Error: HTTP 400 from Firebase API

**Solution:**
- Check FIREBASE_WEB_API_KEY is correct (no spaces)
- Verify Firebase Authentication is enabled
- Check project quota/billing

## 📚 API Documentation

Full API documentation in `docs/Firebase_Authentication_API.postman_collection.json`

## 🎉 Benefits of This Approach

1. **No JSON File Management** - One less file to secure
2. **Simpler Deployment** - Just environment variables
3. **No Heavy Dependencies** - Uses built-in Laravel HTTP client
4. **Easy to Configure** - Copy-paste from Firebase Console
5. **Production Ready** - Used by millions of apps

## 📝 Summary

**You only need:**
1. ✅ Firebase Web API Key (from Firebase Console)
2. ✅ Firebase Project ID
3. ✅ Enable Google/Facebook in Firebase Console
4. ✅ Add to .env

**You DON'T need:**
- ❌ Service Account JSON file
- ❌ File uploads
- ❌ Complex SDK setup
- ❌ Additional Composer packages

---

**Last Updated:** November 1, 2025  
**Method:** Firebase REST API with Web API Key  
**Status:** ✅ Production Ready

