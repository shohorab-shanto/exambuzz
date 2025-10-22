# Social Login Setup Guide (Google & Facebook)

## Overview
This project supports social authentication via Google and Facebook OAuth. Users can log in using their Google or Facebook accounts through both web-based OAuth flow and mobile API endpoints.

## Current Status ✅
- ✅ Laravel Socialite package installed (v5.16.0)
- ✅ Google & Facebook OAuth configured
- ✅ Controllers implemented
- ✅ Routes registered
- ✅ Database columns (`google_id`, `facebook_id`) exist
- ✅ API endpoints for mobile apps ready

## Prerequisites
Before enabling Google and Facebook login, you need to:

### 1. Google OAuth Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable Google+ API
4. Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client ID"
5. Configure OAuth consent screen
6. Set Authorized redirect URIs:
   - For web: `https://yourdomain.com/api/auth/google/callback`
   - For local testing: `http://localhost/api/auth/google/callback`
7. Copy your **Client ID** and **Client Secret**

### 2. Facebook OAuth Setup
1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create a new app or select an existing one
3. Add "Facebook Login" product
4. Configure OAuth redirect URIs:
   - For web: `https://yourdomain.com/api/auth/facebook/callback`
   - For local testing: `http://localhost/api/auth/facebook/callback`
5. Copy your **App ID** and **App Secret**

## Configuration

### Step 1: Update `.env` file
Replace the placeholder values in your `.env` file with your actual credentials:

```env
# Google OAuth Configuration
GOOGLE_CLIENT_ID=your-actual-google-client-id-here
GOOGLE_CLIENT_SECRET=your-actual-google-client-secret-here
GOOGLE_REDIRECT_URL="${APP_URL}/api/auth/google/callback"

# Facebook OAuth Configuration
FACEBOOK_CLIENT_ID=your-actual-facebook-app-id-here
FACEBOOK_CLIENT_SECRET=your-actual-facebook-app-secret-here
FACEBOOK_REDIRECT_URL="${APP_URL}/api/auth/facebook/callback"
```

### Step 2: Clear configuration cache
After updating `.env`, run:
```bash
php artisan config:clear
php artisan config:cache
```

## Available Routes

### Web OAuth Flow (Browser-based)

#### Google Login
- **Start OAuth**: `GET /api/auth/google`
- **Callback**: `GET /api/auth/google/callback`

#### Facebook Login
- **Start OAuth**: `GET /api/auth/facebook`
- **Callback**: `GET /api/auth/facebook/callback`

**Usage:**
1. User clicks "Login with Google/Facebook"
2. Redirect to: `https://yourdomain.com/api/auth/google`
3. User authorizes the app
4. Google/Facebook redirects back to callback URL
5. User is automatically logged in and redirected to homepage

### API Endpoints (Mobile Apps)

#### Google Login (API)
**Endpoint:** `POST /api/login/google`

**Request Body:**
```json
{
    "google_id": "123456789",
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "01712345678"
}
```

**Response:**
```json
{
    "status": true,
    "token_type": "Bearer",
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "google_id": "123456789",
        ...
    }
}
```

#### Facebook Login (API)
**Endpoint:** `POST /api/login/facebook`

**Request Body:**
```json
{
    "facebook_id": "987654321",
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "01798765432"
}
```

**Response:**
```json
{
    "status": true,
    "token_type": "Bearer",
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "facebook_id": "987654321",
        ...
    }
}
```

## How It Works

### Web Flow
1. User clicks "Login with Google/Facebook"
2. System redirects to OAuth provider (Google/Facebook)
3. User authorizes the application
4. OAuth provider redirects back with user data
5. System checks if user exists (by `google_id` or `facebook_id`)
6. If user exists: Log them in
7. If new user: Create account automatically with:
   - Name from OAuth provider
   - Email from OAuth provider
   - Profile photo from OAuth provider
   - Auto-generated password
   - `google_id` or `facebook_id` stored
8. User is logged in with a session token

### API Flow (Mobile)
1. Mobile app handles OAuth with Google/Facebook SDK
2. Mobile app receives user data from Google/Facebook
3. Mobile app sends user data to your API endpoint
4. API checks if user exists (by `google_id` or `email`)
5. If user exists: Return access token
6. If new user: Create account and return access token
7. Mobile app stores the Bearer token for future requests

## Features

### Automatic Account Creation
New users are automatically created with:
- Registration ID (auto-generated)
- Registration number (sequential)
- Email verified timestamp (for Google login)
- Status: Active (1)
- Random OTP (for phone verification if needed)

### Account Linking
- Users are matched by `google_id` or `facebook_id`
- For Google: Also checks email if `google_id` doesn't match
- For Facebook: Only checks `facebook_id`

### Security
- Uses Laravel Sanctum for API token authentication
- Passwords are hashed using bcrypt
- OAuth tokens are not stored (only user IDs)
- SSL/HTTPS required for production

## Testing

### Test Web OAuth (Browser)
1. Update `.env` with your OAuth credentials
2. Visit: `http://localhost/api/auth/google`
3. Authorize with your Google account
4. You should be redirected to homepage and logged in

### Test API Endpoints (Postman/Mobile)
1. Make a POST request to `/api/login/google`
2. Include required fields in JSON body
3. You should receive an access token
4. Use the token in Authorization header: `Bearer {token}`

## Troubleshooting

### Error: "Class Socialite not found"
**Solution:** Run `composer install` or `composer require laravel/socialite`

### Error: "Invalid OAuth credentials"
**Solution:** 
- Double-check your Client ID and Secret in `.env`
- Run `php artisan config:clear`
- Verify redirect URLs match in Google/Facebook console

### Error: "Redirect URI mismatch"
**Solution:** 
- Ensure redirect URLs in `.env` match exactly with OAuth provider settings
- Include protocol (http:// or https://)
- For production, use HTTPS

### Error: "google_id column not found"
**Solution:** 
- The column exists in the database
- Run `php artisan migrate:fresh` if needed (WARNING: This will delete all data)

### Users can't log in after OAuth
**Solution:**
- Check if user was created: `SELECT * FROM users WHERE google_id IS NOT NULL`
- Verify session configuration in `config/session.php`
- Check logs: `storage/logs/laravel.log`

## Database Schema

The `users` table includes:
- `google_id` (string, nullable) - Google OAuth ID
- `facebook_id` (string, nullable) - Facebook OAuth ID
- `email` (string, unique) - Email from OAuth provider
- `name` (string) - Full name from OAuth provider
- `profile_photo_path` (string, nullable) - Profile picture URL
- `email_verified_at` (timestamp, nullable) - Auto-verified for Google

## Security Considerations

1. **Production Setup:**
   - Always use HTTPS in production
   - Keep OAuth secrets secure (never commit to Git)
   - Use environment-specific credentials

2. **Redirect URLs:**
   - Whitelist exact redirect URLs in OAuth console
   - Never use wildcard redirects

3. **API Rate Limiting:**
   - Implement rate limiting for login endpoints
   - Use Laravel's built-in throttling

4. **Token Management:**
   - Tokens should be stored securely on client
   - Implement token refresh if needed
   - Revoke tokens on logout

## Next Steps

1. ✅ Install package - **COMPLETED**
2. ✅ Configure services - **COMPLETED**
3. ⏳ Get OAuth credentials from Google/Facebook
4. ⏳ Update `.env` with real credentials
5. ⏳ Test web OAuth flow
6. ⏳ Test API endpoints
7. ⏳ Implement frontend "Login with Google/Facebook" buttons
8. ⏳ Update mobile app to use social login APIs

## Support

For issues or questions:
- Check Laravel Socialite docs: https://laravel.com/docs/socialite
- Check project logs: `storage/logs/laravel.log`
- Review this documentation

---

**Last Updated:** October 22, 2025
**Package Version:** Laravel Socialite v5.16.0

