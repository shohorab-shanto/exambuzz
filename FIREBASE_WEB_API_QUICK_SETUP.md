# Firebase Google Login - Quick Setup Guide (Web API Key)

## ⚡ Super Simple Setup - 3 Steps!

### Step 1: Get Your Firebase Web API Key

1. Go to https://console.firebase.google.com/
2. Select your project (or create new)
3. Click ⚙️ → **Project Settings**
4. **General** tab → Copy **Web API Key**
   ```
   Example: AIzaSyAbCdEfGhIjKlMnOpQrStUvWxYz1234567
   ```

### Step 2: Enable Google Sign-In

1. Firebase Console → **Authentication** → **Sign-in method**
2. Click **Google** → Toggle **Enable**
3. Select support email → **Save**

### Step 3: Add to .env File

```env
FIREBASE_WEB_API_KEY=AIzaSyAbCdEfGhIjKlMnOpQrStUvWxYz1234567
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_ENABLED=true
```

Then clear cache:
```bash
php artisan config:clear
```

## ✅ That's It!

Your Firebase Google Login is ready!

## 🎯 What You Get

- ✅ Google Login: `POST /api/firebase/google-login`
- ✅ Facebook Login: `POST /api/firebase/facebook-login`
- ✅ Auto user creation
- ✅ Bearer token authentication
- ✅ Works immediately!

## 📱 Mobile App Usage

**Request:**
```json
POST /api/firebase/google-login

{
  "firebase_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjY4ZGU...",
  "phone": "01712345678"
}
```

**Response:**
```json
{
  "status": true,
  "message": "Login successful",
  "token_type": "Bearer",
  "access_token": "1|abcdefghijklmnopqrstuvwxyz",
  "user": { ... }
}
```

## 🚫 What You DON'T Need

- ❌ Service Account JSON file
- ❌ File uploads
- ❌ Complex SDK setup
- ❌ Additional packages

## 📚 Full Documentation

- **Detailed Setup:** `docs/FIREBASE_WEB_API_KEY_SETUP.md`
- **Migration Guide:** `docs/FIREBASE_MIGRATION_TO_WEB_API_KEY.md`
- **Environment Vars:** `docs/ENVIRONMENT_VARIABLES.md`

---

**Method:** Firebase REST API  
**Status:** ✅ Production Ready  
**Updated:** November 1, 2025

