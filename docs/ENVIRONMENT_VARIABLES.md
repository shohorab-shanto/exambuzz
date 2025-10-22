# Required Environment Variables

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

