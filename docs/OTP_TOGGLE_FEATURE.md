# OTP Verification Toggle Feature

## Overview
Added ability to enable/disable OTP verification for user registration via environment variable.

## Configuration

### Environment Variable
Add to your `.env` file:
```env
OTP_VERIFICATION_ENABLED=false
```

**Default:** `false` (disabled)

### Config File
Added to `config/app.php`:
```php
'otp_verification_enabled' => env('OTP_VERIFICATION_ENABLED', false),
```

## Implementation Details

### Modified Files

1. **config/app.php**
   - Added OTP verification configuration

2. **app/Http/Controllers/Api/UserAuthController.php**
   - Updated `register()` method
   - Updated `verifyOtp()` method
   - Updated `resendOtp()` method
   - Updated `login()` method

3. **app/Http/Controllers/Api/v2/UserAuthController.php**
   - Updated `verifyOtp()` method

4. **docs/ENVIRONMENT_VARIABLES.md**
   - Added comprehensive documentation

## Feature Behavior

### When OTP_VERIFICATION_ENABLED=false (Default)

#### Registration (`POST /auth/register`)
- Creates user with `status = 1` (active)
- Sets `email_verified_at = now()` (auto-verified)
- No OTP generation
- No SMS sent
- User can login immediately
- Response: "Your account created successfully. You can login now."

#### Verify OTP (`POST /auth/verify-otp`)
- Returns error: "OTP verification is currently disabled. Users are auto-verified upon registration."

#### Resend OTP (`POST /auth/resend-otp`)
- Returns error: "OTP verification is currently disabled. Users are auto-verified upon registration."

#### Login (`POST /auth/login`)
- Skips unverified account check
- Allows login without verification
- Works with both phone and email

### When OTP_VERIFICATION_ENABLED=true

#### Registration (`POST /auth/register`)
- Creates user with `status = 0` (inactive)
- Generates 6-digit OTP
- Stores OTP in database
- Sends SMS to user's phone
- User cannot login until verified
- Response: "Your account created. Please verify your phone with OTP."

#### Verify OTP (`POST /auth/verify-otp`)
- Validates OTP against phone number
- Updates user: `status = 1`, `email_verified_at = now()`
- Deletes OTP from database
- Response: "Your phone number verified successfully!!"

#### Resend OTP (`POST /auth/resend-otp`)
- Generates new 6-digit OTP
- Sends SMS to phone
- Response: "An 6 digit code has been sent to your email!"

#### Login (`POST /auth/login`)
- Checks verification status
- Blocks unverified users
- Returns error for unverified accounts

## Important Notes

### Password Reset Always Uses OTP
Regardless of the `OTP_VERIFICATION_ENABLED` setting:
- `POST /auth/store-forgot-password` - Always sends OTP
- `POST /auth/reset-password` - Always requires OTP

This ensures security for password reset functionality.

### SMS Function
The project uses `sendSMS($phone, $otp)` helper function for sending OTPs.

### Database Table
`forgot_password_otps` table stores OTP codes:
- `phone` - Phone number or email
- `otp` - 6-digit code
- `created_at`, `updated_at`

## Use Cases

### Development/Testing Environment
Set `OTP_VERIFICATION_ENABLED=false` to:
- Skip SMS integration during development
- Allow faster testing without OTP verification
- Reduce SMS costs

### Production Environment
Set `OTP_VERIFICATION_ENABLED=true` to:
- Enforce phone number verification
- Prevent fake registrations
- Ensure valid contact information

## Testing

### To Enable OTP
1. Update `.env`: `OTP_VERIFICATION_ENABLED=true`
2. Clear config cache: `php artisan config:clear`
3. Test registration - should require OTP

### To Disable OTP  
1. Update `.env`: `OTP_VERIFICATION_ENABLED=false`
2. Clear config cache: `php artisan config:clear`
3. Test registration - should auto-verify

## API Response Changes

### Registration Response (OTP Disabled)
```json
{
  "status": true,
  "message": "Your account created successfully. You can login now."
}
```

### Registration Response (OTP Enabled)
```json
{
  "status": true,
  "message": "Your account created. Please verify your phone with OTP."
}
```

### Verify OTP Response (OTP Disabled)
```json
{
  "status": false,
  "message": "OTP verification is currently disabled. Users are auto-verified upon registration."
}
```

## Backward Compatibility

✅ Fully backward compatible
- Default behavior: OTP disabled (auto-verify)
- Existing code works without changes
- Password reset still requires OTP for security

## Version
- Implemented: 2025-01-XX
- Laravel Version: 10.x

