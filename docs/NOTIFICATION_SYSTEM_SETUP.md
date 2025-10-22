# Notification System - Quick Setup Guide

## 🚀 Quick Start

### 1. Run Database Migrations

```bash
php artisan migrate
```

This will create the necessary columns in your `users` and `notifications` tables.

### 2. Configure Firebase Cloud Messaging (FCM)

#### Get Your FCM Server Key:
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project (or create one)
3. Go to **Project Settings** (gear icon)
4. Navigate to **Cloud Messaging** tab
5. Copy the **Server key**

#### Add to .env file:
```env
FCM_SERVER_KEY=AAAAanr0KaY:APA91bEtYG8axKudxtbzGEaFXzZ7_CCYfLGKrzc9c4w4z9T71uz4r9h5WV74EBdhUR1gXd5I4VyP0YAV9IjBiLL6hI6fMik0ceWTShlUrZUrKHFmRosdEQHc-FlTYLSz68avGIQxtAo-
```

### 3. Set Up Laravel Scheduler

Add this to your crontab (required for automatic notifications):

```bash
crontab -e
```

Add this line:
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/path-to-your-project` with your actual project path.

### 4. Test the System

#### Test FCM Token Storage:
```bash
# Using Postman or cURL
POST http://your-domain/api/store-fcm-token
Authorization: Bearer YOUR_AUTH_TOKEN
Body: fcm_token=your_device_fcm_token
```

#### Test Notification Retrieval:
```bash
GET http://your-domain/api/notifications
Authorization: Bearer YOUR_AUTH_TOKEN
```

#### Manually Trigger Notification Command:
```bash
php artisan written:written-notification
```

## 📦 What Was Created/Updated

### New Files:
1. ✅ `app/Http/Controllers/Api/NotificationController.php` - Complete notification API controller
2. ✅ `database/migrations/YYYY_MM_DD_add_missing_columns_to_users_table.php` - User table columns
3. ✅ `database/migrations/YYYY_MM_DD_add_read_at_to_notifications_table.php` - Read tracking
4. ✅ `docs/NOTIFICATION_SYSTEM.md` - Complete documentation
5. ✅ `docs/NOTIFICATION_SYSTEM_SETUP.md` - This setup guide
6. ✅ `.env.example` - Environment variables template

### Updated Files:
1. ✅ `app/Services/FCMService.php` - Enhanced with error handling, logging, validation
2. ✅ `config/fcm.php` - Uses environment variable
3. ✅ `routes/api.php` - Updated with NotificationController routes
4. ✅ `docs/Exam App Complete API.postman_collection.json` - Updated notification endpoints

## 🎯 Features Implemented

### ✅ Push Notifications (FCM)
- Real-time notifications to mobile devices
- Automatic retry logic
- Error logging and tracking
- Support for data payloads

### ✅ In-App Notifications
- Persistent notification history
- Read/unread status tracking
- Pagination support
- Filter by status

### ✅ Notification Management
- Mark single notification as read
- Mark all notifications as read
- Delete notifications
- Get unread count badge

### ✅ Automated Notifications
- New package/batch announcements
- Written exam results ready
- Preliminary exam completion
- Scheduled every 5 minutes

### ✅ API Endpoints
All endpoints are documented in Postman collection:
- `POST /store-fcm-token` - Store device token
- `POST /remove-fcm-token` - Remove device token
- `GET /notifications` - Get paginated notifications
- `GET /notifications/unread-count` - Get unread count
- `POST /notifications/{id}/mark-as-read` - Mark as read
- `POST /notifications/mark-all-as-read` - Mark all as read
- `DELETE /notifications/{id}` - Delete notification

## 🔧 Configuration Files

### routes/api.php
```php
// Notification Routes - Using NotificationController
Route::middleware('auth:sanctum')->group(function () {
    // FCM Token Management
    Route::post('/store-fcm-token', [NotificationController::class, 'storeFcmToken']);
    Route::post('/remove-fcm-token', [NotificationController::class, 'removeFcmToken']);
    
    // Notification Management
    Route::post('/notification', [NotificationController::class, 'index']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    
    // Backward compatibility
    Route::post('/make-notification-seen', [NotificationController::class, 'markAllAsRead']);
});
```

### app/Console/Kernel.php
```php
protected function schedule(Schedule $schedule): void {
    $schedule->command('written:written-notification')->everyFiveMinutes();
}
```

## 📱 Mobile App Integration

### Step 1: Get FCM Token
In your mobile app (Flutter/React Native), get the FCM token:

**Flutter:**
```dart
String? token = await FirebaseMessaging.instance.getToken();
```

**React Native:**
```javascript
const token = await messaging().getToken();
```

### Step 2: Send Token to API
```dart
// Flutter example
final response = await http.post(
  Uri.parse('$baseUrl/store-fcm-token'),
  headers: {
    'Authorization': 'Bearer $authToken',
    'Content-Type': 'application/json',
  },
  body: jsonEncode({
    'fcm_token': token,
  }),
);
```

### Step 3: Handle Notifications
```dart
// Flutter example
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  print('Got a message: ${message.notification?.title}');
  // Show local notification or update UI
});
```

## 🔍 Testing Checklist

- [ ] Migrations run successfully
- [ ] FCM_SERVER_KEY added to .env
- [ ] Cron job set up for scheduler
- [ ] Can store FCM token via API
- [ ] Can retrieve notifications
- [ ] Can mark notifications as read
- [ ] Can delete notifications
- [ ] Scheduled command runs manually
- [ ] Push notifications send successfully
- [ ] Mobile app receives notifications

## 🐛 Troubleshooting

### Notifications not sending?
1. Check `storage/logs/laravel.log` for errors
2. Verify FCM_SERVER_KEY is correct
3. Ensure user has valid fcm_token in database
4. Test FCM service manually:
```php
use App\Services\FCMService;

$result = FCMService::send(
    'device_token_here',
    ['title' => 'Test', 'body' => 'Test message']
);

dd($result); // Should return true
```

### Scheduled notifications not working?
1. Verify cron job is running:
```bash
crontab -l
```
2. Test command manually:
```bash
php artisan written:written-notification
```
3. Check Laravel scheduler is working:
```bash
php artisan schedule:list
```

### Database errors?
1. Clear cache:
```bash
php artisan config:clear
php artisan cache:clear
```
2. Re-run migrations:
```bash
php artisan migrate:fresh
```
⚠️ **Warning**: This will delete all data!

## 📚 Additional Resources

- [Full Documentation](NOTIFICATION_SYSTEM.md)
- [Postman Collection](Exam App Complete API.postman_collection.json)
- [Firebase Cloud Messaging Docs](https://firebase.google.com/docs/cloud-messaging)
- [Laravel Task Scheduling](https://laravel.com/docs/scheduling)
- [Laravel Sanctum Authentication](https://laravel.com/docs/sanctum)

## 🎉 You're All Set!

The notification system is now fully functional. Users will receive:
- Push notifications when exams are graded
- Push notifications when new packages are released
- In-app notification history
- Real-time notification badges

For any issues or questions, refer to the full documentation or contact the development team.

