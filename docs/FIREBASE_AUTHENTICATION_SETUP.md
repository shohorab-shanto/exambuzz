# Firebase Authentication Setup Guide

## Overview
This project uses **Firebase Authentication** for Google and Facebook login in mobile apps. The mobile app authenticates users with Firebase SDK, then sends the Firebase ID token to our API for verification.

## ✅ What's Implemented

- ✅ Firebase Admin SDK for PHP (v7.16.0) installed
- ✅ Firebase authentication controller (`FirebaseAuthController`)
- ✅ Google login via Firebase (`POST /api/firebase/google-login`)
- ✅ Facebook login via Firebase (`POST /api/firebase/facebook-login`)
- ✅ Database column `firebase_uid` added to users table
- ✅ Auto account creation/linking
- ✅ Bearer token authentication
- ✅ Postman collection included

## Architecture

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐         ┌──────────────┐
│             │         │              │         │             │         │              │
│  Mobile App │────────▶│   Firebase   │────────▶│   Our API   │────────▶│   Database   │
│             │         │              │         │             │         │              │
└─────────────┘         └──────────────┘         └─────────────┘         └──────────────┘
     │                        │                        │                        │
     │ 1. Authenticate        │                        │                        │
     │    with Firebase       │                        │                        │
     │                        │                        │                        │
     │◀───────────────────────┘                        │                        │
     │ 2. Get ID Token                                 │                        │
     │                                                 │                        │
     │─────────────────────────────────────────────────┘                        │
     │ 3. Send Token to API                                                     │
     │                                                                          │
     │                            4. Verify Token                               │
     │                            5. Create/Login User                          │
     │                                                                          │
     │◀─────────────────────────────────────────────────────────────────────────┘
     │ 6. Get Bearer Token
     │
```

## Prerequisites

### 1. Firebase Project Setup

1. **Create Firebase Project**
   - Go to [Firebase Console](https://console.firebase.google.com/)
   - Click "Add project" or select existing project
   - Enable Google Analytics (optional)

2. **Enable Authentication Providers**
   - In Firebase Console, go to **Authentication** → **Sign-in method**
   - Enable **Google**
   - Enable **Facebook** (requires Facebook App ID and Secret)
   - Save changes

3. **Generate Service Account**
   - Go to **Project Settings** (gear icon) → **Service accounts**
   - Click **Generate new private key**
   - Download the JSON file (e.g., `firebase-credentials.json`)
   - **KEEP THIS FILE SECURE** - Never commit to Git!

### 2. Configure Facebook (if using Facebook Login)

1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create/select your app
3. Get your **App ID** and **App Secret**
4. Add these to Firebase:
   - Firebase Console → Authentication → Sign-in method → Facebook
   - Enter App ID and App Secret
   - Copy the OAuth redirect URI and add it to Facebook app settings

## Backend Setup (Laravel API)

### Step 1: Upload Service Account File

Upload the Firebase service account JSON file to:
```
storage/app/firebase/firebase-credentials.json
```

Create the directory if it doesn't exist:
```bash
mkdir -p storage/app/firebase
chmod 755 storage/app/firebase
```

### Step 2: Update .env Configuration

Update your `.env` file with Firebase settings:

```env
# Firebase Configuration
FIREBASE_ENABLED=true
FIREBASE_PROJECT_ID=your-firebase-project-id
FIREBASE_CREDENTIALS=storage/app/firebase/firebase-credentials.json
```

Replace `your-firebase-project-id` with your actual Firebase project ID (found in Firebase Console → Project Settings).

### Step 3: Clear Configuration Cache

```bash
php artisan config:clear
php artisan config:cache
```

### Step 4: Verify Setup

Test that Firebase is configured correctly:

```bash
php artisan tinker
```

```php
$factory = (new \Kreait\Firebase\Factory)->withServiceAccount(storage_path('app/firebase/firebase-credentials.json'));
$auth = $factory->createAuth();
echo "Firebase Auth initialized successfully!";
```

## API Endpoints

### 1. Firebase Google Login

**Endpoint:** `POST /api/firebase/google-login`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "firebase_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjY4ZGU...",
    "phone": "01712345678"
}
```

**Parameters:**
- `firebase_token` (required): Firebase ID token from mobile app
- `phone` (optional): User's phone number if not available in Firebase

**Success Response (200 OK):**
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
        "registration_id": "2025000001",
        "status": 1,
        "email_verified_at": "2025-10-22T10:30:00.000000Z",
        "created_at": "2025-10-22T10:30:00.000000Z",
        "updated_at": "2025-10-22T10:30:00.000000Z"
    }
}
```

**New User Response (201 Created):**
```json
{
    "status": true,
    "message": "Registration successful",
    "token_type": "Bearer",
    "access_token": "2|abcdefghijklmnopqrstuvwxyz987654321",
    "user": { ... }
}
```

**Error Response (401 Unauthorized):**
```json
{
    "status": false,
    "message": "Invalid Firebase token",
    "error": "Token verification failed"
}
```

### 2. Firebase Facebook Login

**Endpoint:** `POST /api/firebase/facebook-login`

Same request/response format as Google login.

## Mobile App Implementation

### Android (Java/Kotlin)

#### 1. Add Firebase to Android App

Add to `build.gradle` (app level):
```gradle
dependencies {
    implementation 'com.google.firebase:firebase-auth:22.3.0'
    implementation 'com.google.android.gms:play-services-auth:20.7.0'
}
```

#### 2. Google Sign-In Implementation

```kotlin
// Initialize Firebase Auth
private lateinit var auth: FirebaseAuth

auth = Firebase.auth

// Configure Google Sign-In
val gso = GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
    .requestIdToken(getString(R.string.default_web_client_id))
    .requestEmail()
    .build()

val googleSignInClient = GoogleSignIn.getClient(this, gso)

// Sign in with Google
private fun signInWithGoogle() {
    val signInIntent = googleSignInClient.signInIntent
    startActivityForResult(signInIntent, RC_SIGN_IN)
}

// Handle result
override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
    super.onActivityResult(requestCode, resultCode, data)
    
    if (requestCode == RC_SIGN_IN) {
        val task = GoogleSignIn.getSignedInAccountFromIntent(data)
        try {
            val account = task.getResult(ApiException::class.java)!!
            firebaseAuthWithGoogle(account.idToken!!)
        } catch (e: ApiException) {
            // Handle error
        }
    }
}

// Authenticate with Firebase
private fun firebaseAuthWithGoogle(idToken: String) {
    val credential = GoogleAuthProvider.getCredential(idToken, null)
    auth.signInWithCredential(credential)
        .addOnCompleteListener(this) { task ->
            if (task.isSuccessful) {
                // Get Firebase ID Token
                auth.currentUser?.getIdToken(true)
                    ?.addOnCompleteListener { tokenTask ->
                        if (tokenTask.isSuccessful) {
                            val firebaseToken = tokenTask.result?.token
                            // Send to your API
                            sendTokenToAPI(firebaseToken!!)
                        }
                    }
            }
        }
}

// Send to your API
private fun sendTokenToAPI(firebaseToken: String) {
    val client = OkHttpClient()
    val json = JSONObject()
    json.put("firebase_token", firebaseToken)
    json.put("phone", "01712345678") // Optional
    
    val body = RequestBody.create(
        "application/json".toMediaType(),
        json.toString()
    )
    
    val request = Request.Builder()
        .url("https://exambuzz.live/api/firebase/google-login")
        .post(body)
        .build()
    
    client.newCall(request).enqueue(object : Callback {
        override fun onResponse(call: Call, response: Response) {
            val responseData = response.body?.string()
            val jsonResponse = JSONObject(responseData)
            val accessToken = jsonResponse.getString("access_token")
            // Save access token and use for API calls
        }
        
        override fun onFailure(call: Call, e: IOException) {
            // Handle error
        }
    })
}
```

### iOS (Swift)

#### 1. Add Firebase to iOS App

Add to `Podfile`:
```ruby
pod 'Firebase/Auth'
pod 'GoogleSignIn'
```

#### 2. Google Sign-In Implementation

```swift
import Firebase
import GoogleSignIn

// Configure Google Sign-In
guard let clientID = FirebaseApp.app()?.options.clientID else { return }
let config = GIDConfiguration(clientID: clientID)
GIDSignIn.sharedInstance.configuration = config

// Sign in with Google
GIDSignIn.sharedInstance.signIn(withPresenting: self) { result, error in
    guard let user = result?.user,
          let idToken = user.idToken?.tokenString else {
        return
    }
    
    let credential = GoogleAuthProvider.credential(
        withIDToken: idToken,
        accessToken: user.accessToken.tokenString
    )
    
    // Authenticate with Firebase
    Auth.auth().signIn(with: credential) { authResult, error in
        if let error = error {
            print("Firebase auth error: \\(error)")
            return
        }
        
        // Get Firebase ID Token
        Auth.auth().currentUser?.getIDToken { idToken, error in
            if let error = error {
                print("Error getting token: \\(error)")
                return
            }
            
            if let firebaseToken = idToken {
                self.sendTokenToAPI(token: firebaseToken)
            }
        }
    }
}

// Send to API
func sendTokenToAPI(token: String) {
    let url = URL(string: "https://exambuzz.live/api/firebase/google-login")!
    var request = URLRequest(url: url)
    request.httpMethod = "POST"
    request.setValue("application/json", forHTTPHeaderField: "Content-Type")
    
    let body: [String: Any] = [
        "firebase_token": token,
        "phone": "01712345678" // Optional
    ]
    
    request.httpBody = try? JSONSerialization.data(withJSONObject: body)
    
    URLSession.shared.dataTask(with: request) { data, response, error in
        if let data = data {
            let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any]
            if let accessToken = json?["access_token"] as? String {
                // Save and use access token
                print("Access token: \\(accessToken)")
            }
        }
    }.resume()
}
```

### Flutter

#### 1. Add Dependencies

Add to `pubspec.yaml`:
```yaml
dependencies:
  firebase_core: ^2.24.0
  firebase_auth: ^4.15.0
  google_sign_in: ^6.1.5
  http: ^1.1.0
```

#### 2. Google Sign-In Implementation

```dart
import 'package:firebase_auth/firebase_auth.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

Future<void> signInWithGoogle() async {
  // Trigger the authentication flow
  final GoogleSignInAccount? googleUser = await GoogleSignIn().signIn();

  // Obtain the auth details from the request
  final GoogleSignInAuthentication? googleAuth = await googleUser?.authentication;

  // Create a new credential
  final credential = GoogleAuthProvider.credential(
    accessToken: googleAuth?.accessToken,
    idToken: googleAuth?.idToken,
  );

  // Sign in to Firebase
  UserCredential userCredential = await FirebaseAuth.instance.signInWithCredential(credential);

  // Get Firebase ID Token
  String? firebaseToken = await userCredential.user?.getIdToken();

  // Send to API
  if (firebaseToken != null) {
    await sendTokenToAPI(firebaseToken);
  }
}

Future<void> sendTokenToAPI(String firebaseToken) async {
  final response = await http.post(
    Uri.parse('https://exambuzz.live/api/firebase/google-login'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({
      'firebase_token': firebaseToken,
      'phone': '01712345678', // Optional
    }),
  );

  if (response.statusCode == 200 || response.statusCode == 201) {
    final data = jsonDecode(response.body);
    String accessToken = data['access_token'];
    // Save and use access token
    print('Access token: $accessToken');
  } else {
    print('Error: ${response.body}');
  }
}
```

### React Native

#### 1. Install Dependencies

```bash
npm install @react-native-firebase/app @react-native-firebase/auth
npm install @react-native-google-signin/google-signin
```

#### 2. Google Sign-In Implementation

```javascript
import auth from '@react-native-firebase/auth';
import { GoogleSignin } from '@react-native-google-signin/google-signin';

// Configure Google Sign-In
GoogleSignin.configure({
  webClientId: 'YOUR_WEB_CLIENT_ID',
});

// Sign in with Google
async function signInWithGoogle() {
  // Get the user's ID token
  const { idToken } = await GoogleSignin.signIn();

  // Create a Google credential
  const googleCredential = auth.GoogleAuthProvider.credential(idToken);

  // Sign-in the user with the credential
  const userCredential = await auth().signInWithCredential(googleCredential);

  // Get Firebase ID Token
  const firebaseToken = await userCredential.user.getIdToken();

  // Send to API
  await sendTokenToAPI(firebaseToken);
}

// Send to API
async function sendTokenToAPI(firebaseToken) {
  try {
    const response = await fetch('https://exambuzz.live/api/firebase/google-login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        firebase_token: firebaseToken,
        phone: '01712345678', // Optional
      }),
    });

    const data = await response.json();
    const accessToken = data.access_token;
    // Save and use access token
    console.log('Access token:', accessToken);
  } catch (error) {
    console.error('Error:', error);
  }
}
```

## Using the Access Token

After successful authentication, use the Bearer token for all API requests:

```
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz123456789
```

Example API call:
```bash
curl -X GET https://exambuzz.live/api/user/profile \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

## Testing with Postman

1. **Import Collection**
   - Open Postman
   - Import: `docs/Firebase_Authentication_API.postman_collection.json`

2. **Get Firebase Token (Manual Testing)**
   - Use Firebase Console → Authentication → Users
   - Click on a test user → Copy UID
   - Or use Firebase SDK in a test app

3. **Test Login**
   - Open "Firebase Google Login" request
   - Replace `firebase_token` with a real Firebase ID token
   - Send request
   - Copy the `access_token` from response
   - Set as environment variable or use in subsequent requests

## Security Considerations

### 1. Service Account Security
- **NEVER** commit `firebase-credentials.json` to Git
- Add to `.gitignore`:
  ```
  storage/app/firebase/
  ```
- Store securely on production server
- Restrict file permissions:
  ```bash
  chmod 600 storage/app/firebase/firebase-credentials.json
  ```

### 2. Token Validation
- Firebase ID tokens expire after 1 hour
- Mobile apps should refresh tokens automatically
- Our API verifies token signature with Firebase
- Invalid/expired tokens are rejected (401 Unauthorized)

### 3. HTTPS Required
- Always use HTTPS in production
- Firebase token verification requires secure connection
- Update `APP_URL` in `.env` to use `https://`

### 4. Rate Limiting
- Implement rate limiting on login endpoints
- Use Laravel's throttle middleware:
  ```php
  Route::post('/firebase/google-login', [FirebaseAuthController::class, 'googleLogin'])
      ->middleware('throttle:10,1');
  ```

## Troubleshooting

### Error: "Firebase not configured"
**Solution:**
1. Check if `firebase-credentials.json` exists
2. Verify path in `.env` is correct
3. Run `php artisan config:clear`

### Error: "Invalid Firebase token"
**Solution:**
1. Check if token is fresh (not expired)
2. Verify Firebase project ID matches
3. Ensure service account has correct permissions
4. Check if authentication is enabled in Firebase Console

### Error: "Class 'Kreait\Firebase\Factory' not found"
**Solution:**
```bash
composer require kreait/firebase-php
composer dump-autoload
```

### Token expires too quickly
**Solution:**
- Firebase ID tokens last 1 hour by default
- Implement token refresh in mobile app:
  ```kotlin
  auth.currentUser?.getIdToken(true) // Force refresh
  ```

## Migration from Old Social Login

If you're migrating from the old social login system:

1. **Users with `google_id`** will be linked by email
2. **New field `firebase_uid`** will be populated on first Firebase login
3. **Old login endpoints** still work (backward compatible)
4. **Gradually migrate** mobile apps to Firebase method

## Support & Resources

- **Firebase Documentation:** https://firebase.google.com/docs/auth
- **Laravel Firebase:** https://firebase-php.readthedocs.io/
- **Google Sign-In:** https://developers.google.com/identity
- **Facebook Login:** https://developers.facebook.com/docs/facebook-login

## Summary

✅ **Backend:** Fully configured and ready  
✅ **API Endpoints:** Working  
✅ **Postman Collection:** Created  
⏳ **Mobile App:** Implement Firebase SDK  
⏳ **Firebase Console:** Configure providers  
⏳ **Service Account:** Upload credentials  

---

**Last Updated:** October 22, 2025  
**Package Version:** Firebase PHP SDK v7.16.0

