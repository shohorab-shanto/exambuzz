# Required Environment Variables

## OTP Verification System

Controls whether OTP (One-Time Password) verification is required for user registration.

Add this variable to your `.env` file:

```env
# OTP Verification (disabled by default)
# Set to true to enable SMS OTP verification for user registration
# Set to false to auto-verify users upon registration (no SMS required)
OTP_VERIFICATION_ENABLED=false
```

### Behavior

**When ENABLED (true):**
- Users must verify their phone number with OTP after registration
- User status = 0 (inactive) until verified
- SMS with 6-digit OTP is sent to user's phone
- User must call `/auth/verify-otp` endpoint to activate account
- Cannot login until verified

**When DISABLED (false) - DEFAULT:**
- Users are automatically verified upon registration
- User status = 1 (active) immediately
- No SMS is sent
- Can login immediately after registration
- Bypass OTP verification process

### Affected Endpoints

- `POST /auth/register` - Auto-verifies when disabled
- `POST /auth/verify-otp` - Returns error when disabled
- `POST /auth/resend-otp` - Returns error when disabled
- `POST /auth/login` - Skips verification check when disabled

### Security Note

⚠️ **Password Reset OTP:** Forgot password and reset password functionality ALWAYS uses OTP regardless of this setting for security reasons.

---

## Firebase Authentication

Add these variables to your `.env` file:

```env
# Firebase Web API Key (for Google/Facebook login)
# Get this from Firebase Console > Project Settings > General > Web API Key
FIREBASE_WEB_API_KEY=AIzaSyAbCdEfGhIjKlMnOpQrStUvWxYz1234567
FIREBASE_PROJECT_ID=your-firebase-project-id
FIREBASE_ENABLED=true
```

### How to Get Firebase Web API Key

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Click ⚙️ **Settings** → **Project Settings**
4. Go to **General** tab
5. Scroll to **Your apps** section
6. Copy the **Web API Key** value
7. Paste it into your `.env` file as `FIREBASE_WEB_API_KEY`

### Enable Google Sign-In

1. Firebase Console → **Authentication** → **Sign-in method**
2. Click **Google** → Enable
3. Select support email → Save

**Note:** No JSON file needed! Just the Web API Key.

---

## Notification System

Add these variables to your `.env` file:

```env
# Firebase Cloud Messaging
# Get this from Firebase Console > Project Settings > Cloud Messaging > Server Key
FCM_SERVER_KEY=your-firebase-server-key-here
```

## How to Get FCM Server Key

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project (or create a new one)
3. Click the gear icon ⚙️ and select **Project Settings**
4. Navigate to the **Cloud Messaging** tab
5. Copy the **Server key** value
6. Paste it into your `.env` file

## Current Value

The current hardcoded FCM server key has been moved to use environment variables for better security.

**Old Location:** `config/fcm.php` (hardcoded)
**New Location:** `.env` file (FCM_SERVER_KEY)

## Important Notes

⚠️ **Security:** Never commit your `.env` file to version control. It should be listed in `.gitignore`.

⚠️ **Production:** Make sure to set this variable in your production environment configuration.

⚠️ **Testing:** You can use a test FCM key for development, but notifications won't reach real devices without a valid key.

## After Adding

After adding the FCM_SERVER_KEY to your `.env` file:

1. Clear configuration cache:
```bash
php artisan config:clear
```

2. Test the notification system:
```bash
php artisan written:written-notification
```

3. Check logs for any errors:
```bash
tail -f storage/logs/laravel.log
```

