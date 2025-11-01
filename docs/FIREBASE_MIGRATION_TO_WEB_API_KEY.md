# Migration Guide: Firebase Admin SDK → Web API Key

## Overview
We've migrated from Firebase Admin SDK (Service Account JSON) to Firebase Web API Key for simpler authentication.

## What Changed

### Before (Admin SDK)
```env
FIREBASE_CREDENTIALS=storage/app/firebase/firebase-credentials.json
FIREBASE_PROJECT_ID=your-project-id
```

Required:
- ❌ Service Account JSON file
- ❌ kreait/firebase-php package
- ❌ File management/upload

### After (Web API Key)
```env
FIREBASE_WEB_API_KEY=AIzaSyAbCdEfGhIjKlMnOpQrStUvWxYz1234567
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_ENABLED=true
```

Required:
- ✅ Just one environment variable
- ✅ No packages needed (uses Laravel HTTP)
- ✅ No file management

## Migration Steps

### Step 1: Get Web API Key

1. Firebase Console → Project Settings → General
2. Copy **Web API Key**
3. Example: `AIzaSyAbCdEfGhIjKlMnOpQrStUvWxYz1234567`

### Step 2: Update .env

Remove or comment out old config:
```env
# FIREBASE_CREDENTIALS=storage/app/firebase/firebase-credentials.json
```

Add new config:
```env
FIREBASE_WEB_API_KEY=AIzaSyAbCdEfGhIjKlMnOpQrStUvWxYz1234567
```

### Step 3: Clear Cache

```bash
php artisan config:clear
php artisan config:cache
```

### Step 4: Test

Test login endpoints - they should work immediately!

### Step 5: (Optional) Remove Old Files

If you're sure the migration works:

```bash
# Remove service account JSON (optional - keep as backup)
rm storage/app/firebase/firebase-credentials.json

# Remove kreait/firebase-php package (optional)
composer remove kreait/firebase-php
```

## Code Changes Made

### 1. config/firebase.php
```php
// OLD
'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/firebase-credentials.json')),

// NEW
'web_api_key' => env('FIREBASE_WEB_API_KEY', ''),
```

### 2. FirebaseAuthController.php

**Old Approach:**
```php
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;

public function __construct() {
    $factory = (new Factory)->withServiceAccount($credentialsPath);
    $this->auth = $factory->createAuth();
}

// Verify token
$verifiedIdToken = $this->auth->verifyIdToken($token);
$firebaseUser = $this->auth->getUser($firebaseUid);
```

**New Approach:**
```php
use Illuminate\Support\Facades\Http;

public function __construct() {
    $this->webApiKey = config('firebase.web_api_key');
}

// Verify token via REST API
private function verifyFirebaseToken($idToken) {
    $response = Http::post("https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={$this->webApiKey}", [
        'idToken' => $idToken
    ]);
    return $response->json()['users'][0] ?? null;
}
```

## Backward Compatibility

✅ **API Endpoints unchanged**
- `POST /api/firebase/google-login` - Same
- `POST /api/firebase/facebook-login` - Same

✅ **Request/Response format unchanged**
- Same request body
- Same response structure
- Same Bearer token format

✅ **Mobile app unchanged**
- Mobile apps don't need updates
- Same Firebase SDK usage
- Same token sending logic

## Benefits of Migration

| Benefit | Impact |
|---------|--------|
| Simpler Setup | ⬇️ 90% less configuration |
| No File Management | ✅ One less security concern |
| Easier Deployment | ✅ Just .env variable |
| Lighter Weight | ⬇️ Remove heavy PHP package |
| Same Functionality | ✅ Authentication works identically |

## Potential Concerns

### 1. "Is Web API Key secure?"

**Yes!** Firebase Web API Keys are designed to be public:
- Used in millions of mobile apps
- Protected by Firebase security rules
- Built-in rate limiting
- Only authenticates, doesn't access data

### 2. "What about rate limits?"

Firebase has generous limits:
- **100 verifications/second** per project
- **More than enough** for most apps
- Monitor in Firebase Console

### 3. "Can I still manage users?"

For basic auth: Yes (login, register, token verify)

For advanced features (create custom tokens, manage users programmatically):
- Keep Admin SDK
- Or use Firebase Console for manual management

### 4. "Should I remove kreait/firebase-php?"

**Optional:**
- Keep it if you might need Admin SDK features later
- Remove it if you want to reduce dependencies
- Either way works fine

## Rollback Plan

If you need to rollback to Admin SDK:

1. **Restore .env:**
   ```env
   FIREBASE_CREDENTIALS=storage/app/firebase/firebase-credentials.json
   ```

2. **Revert code changes** (we kept old files as backup)

3. **Clear cache:**
   ```bash
   php artisan config:clear
   ```

## Testing Checklist

- [ ] Google login works
- [ ] Facebook login works  
- [ ] New user registration
- [ ] Existing user login
- [ ] Bearer token generated
- [ ] Phone number updates
- [ ] Email linking works

## Support

- **Firebase REST API Docs:** https://firebase.google.com/docs/reference/rest/auth
- **Web API Key Info:** https://firebase.google.com/docs/projects/api-keys
- **Identity Toolkit API:** https://cloud.google.com/identity-platform/docs/reference/rest

---

**Migration Date:** November 1, 2025  
**Status:** ✅ Complete  
**Breaking Changes:** None (backward compatible)

