# Notification System Documentation

## Overview
The Exam App has a complete notification system with Firebase Cloud Messaging (FCM) integration for push notifications and database storage for notification history.

## Features

### 1. Push Notifications (FCM)
- Real-time push notifications to mobile devices
- Automatic notifications for:
  - Written exam results graded
  - Preliminary exam completion
  - New package/batch releases

### 2. In-App Notifications
- Stored in database for persistent history
- Read/unread status tracking
- Pagination support
- Filter by status (read/unread)

### 3. Notification Management
- Mark individual notifications as read
- Mark all notifications as read
- Delete individual notifications
- Get unread count badge

## Database Schema

### Users Table (Additional Columns)
```sql
fcm_token VARCHAR - Firebase Cloud Messaging device token
type VARCHAR - User type (user, teacher, admin)
status TINYINT - User status (0=inactive, 1=active)
registration_id VARCHAR - Unique registration ID
register_number INT - Sequential registration number
otp VARCHAR - One-time password for verification
amount DECIMAL - User wallet/credit amount
permission TEXT - JSON permissions data
```

### Notifications Table
```sql
id BIGINT - Primary key
user_id BIGINT - User receiving notification
written_id BIGINT - Related written exam (nullable)
package_id BIGINT - Related package (nullable)
to VARCHAR - Target audience
name VARCHAR - Notification title
details LONGTEXT - Notification message
status TINYINT - Read status (0=unread, 1=read)
read_at TIMESTAMP - When notification was read
created_at TIMESTAMP
updated_at TIMESTAMP
```

### Send Exam Notifications Table (Tracking)
```sql
id BIGINT - Primary key
type VARCHAR - Notification type
user_id BIGINT - User notified
exam_id BIGINT - Related exam
written_id BIGINT - Related written exam
created_at TIMESTAMP
updated_at TIMESTAMP
```

## API Endpoints

### FCM Token Management

#### Store FCM Token
```
POST /api/store-fcm-token
Authorization: Bearer {token}

Body:
{
  "fcm_token": "string (required, min:20)"
}

Response:
{
  "status": true,
  "message": "FCM token stored successfully",
  "data": {
    "user_id": 1,
    "fcm_token_set": true
  }
}
```

#### Remove FCM Token
```
POST /api/remove-fcm-token
Authorization: Bearer {token}

Response:
{
  "status": true,
  "message": "FCM token removed successfully"
}
```

### Notification Endpoints

#### Get Notifications (RESTful)
```
GET /api/notifications?per_page=15&status=0
Authorization: Bearer {token}

Query Parameters:
- per_page (optional): Number of items per page (default: 15)
- status (optional): Filter by status (null=all, 0=unread, 1=read)

Response:
{
  "status": true,
  "message": "Notifications retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [...],
    "per_page": 15,
    "total": 50
  },
  "unread_count": 5
}
```

#### Get Notifications (Legacy)
```
POST /api/notification
Authorization: Bearer {token}

Response: Same as above
```

#### Get Unread Count
```
GET /api/notifications/unread-count
Authorization: Bearer {token}

Response:
{
  "status": true,
  "unread_count": 5
}
```

#### Mark Notification as Read
```
POST /api/notifications/{id}/mark-as-read
Authorization: Bearer {token}

Response:
{
  "status": true,
  "message": "Notification marked as read",
  "data": {notification_object}
}
```

#### Mark All Notifications as Read
```
POST /api/notifications/mark-all-as-read
Authorization: Bearer {token}

Response:
{
  "status": true,
  "message": "All notifications marked as read",
  "updated_count": 5
}
```

#### Delete Notification
```
DELETE /api/notifications/{id}
Authorization: Bearer {token}

Response:
{
  "status": true,
  "message": "Notification deleted successfully"
}
```

## FCM Service

### FCMService Class
Located at: `app/Services/FCMService.php`

#### Methods

##### send($token, $notification, $data = [])
Send push notification to a single device.

**Parameters:**
- `$token` (string): FCM device token
- `$notification` (array): `['title' => '', 'body' => '']`
- `$data` (array): Additional data payload (optional)

**Returns:** `bool` - Success status

**Example:**
```php
use App\Services\FCMService;

$success = FCMService::send(
    $user->fcm_token,
    [
        'title' => 'নতুন প্যাকেজ',
        'body' => 'নতুন একটি ব্যাচ চালু হয়েছে।'
    ]
);
```

##### sendToMultiple($tokens, $notification, $data = [])
Send push notification to multiple devices.

**Parameters:**
- `$tokens` (array): Array of FCM device tokens
- `$notification` (array): Notification data
- `$data` (array): Additional data payload (optional)

**Returns:** `array` - `['success' => count, 'failed' => count]`

## Configuration

### Environment Variables
Add to your `.env` file:

```env
# Firebase Cloud Messaging
FCM_SERVER_KEY=your-fcm-server-key-here
```

### Config File
Located at: `config/fcm.php`

```php
return [
    'token' => env('FCM_SERVER_KEY', ''),
];
```

## Scheduled Commands

### Written Notification Command
Located at: `app/Console/Commands/WrittenNotification.php`

**Schedule:** Every 5 minutes (configured in `app/Console/Kernel.php`)

**Purpose:** 
- Checks for completed written exams with grading
- Checks for expired preliminary exams
- Sends notifications to participants
- Tracks sent notifications to prevent duplicates

**Manual Execution:**
```bash
php artisan written:written-notification
```

## Automatic Notification Triggers

### 1. New Package Created
**Trigger:** When admin creates a new package
**Recipients:** All active users (type='user')
**Message:** "নতুন প্যাকেজ - নতুন একটি ব্যাচ চালু হয়েছে। আপনার প্রস্তুতি যাচাই করুন।"

**Code Location:** `app/Http/Controllers/Backend/PackageController.php` (lines 275-297)

### 2. Written Exam Graded
**Trigger:** Written exam graded and expired
**Recipients:** Users who submitted answers
**Message:** "{Category} লিখিত পরীক্ষার খাতা মূল্যায়ন করা হয়েছে, ফলাফল দেখুন।"

**Code Location:** `app/Console/Commands/WrittenNotification.php` (lines 34-110)

### 3. Preliminary Exam Completed
**Trigger:** Preliminary exam expired
**Recipients:** Users who participated
**Message:** "{Category} প্রিলিমিনারি লাইভ পরীক্ষা শেষ হয়েছে, ফলাফল দেখুন।"

**Code Location:** `app/Console/Commands/WrittenNotification.php` (lines 112-186)

## Logging

The FCMService includes comprehensive logging:

- **Info Level:** Successful notifications
- **Warning Level:** Empty tokens, invalid data, FCM failures
- **Error Level:** HTTP failures, exceptions

View logs at: `storage/logs/laravel.log`

## Testing

### Test FCM Token Storage
```bash
curl -X POST http://localhost:8000/api/store-fcm-token \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "fcm_token=test_fcm_token_here"
```

### Test Notification Retrieval
```bash
curl -X GET http://localhost:8000/api/notifications \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Test Mark as Read
```bash
curl -X POST http://localhost:8000/api/notifications/1/mark-as-read \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Migration

Run migrations to set up the notification system:

```bash
php artisan migrate
```

This will create/update:
1. `users` table with FCM and user management columns
2. `notifications` table with read_at timestamp
3. `send_exam_notifications` tracking table (already exists)

## Backward Compatibility

The system maintains backward compatibility with old endpoints:
- `POST /api/notification` → Now uses NotificationController
- `POST /api/make-notification-seen` → Maps to markAllAsRead
- `POST /api/store-fcm-token` → Now has validation

## Best Practices

1. **Always validate FCM tokens** - Use the provided validation in the controller
2. **Handle notification failures gracefully** - Check FCMService return values
3. **Keep notifications concise** - Use clear, actionable messages
4. **Test with real devices** - FCM requires actual devices for testing
5. **Monitor logs** - Check for failed notifications regularly
6. **Update tokens on login** - Ensure tokens are current

## Security Considerations

1. FCM Server Key stored in environment variables (not in code)
2. Notifications only accessible by the user they belong to
3. All endpoints protected with Sanctum authentication
4. Input validation on all endpoints
5. SQL injection prevention through Eloquent ORM

## Troubleshooting

### Notifications not sending
1. Check FCM_SERVER_KEY in .env
2. Verify user has valid fcm_token
3. Check logs for errors
4. Ensure scheduled command is running

### Users not receiving notifications
1. Confirm FCM token is stored
2. Verify device has internet connection
3. Check if app has notification permissions
4. Test with manual notification send

### Scheduled command not running
1. Ensure Laravel scheduler is set up:
   ```bash
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```
2. Check cron logs
3. Test command manually

## Support

For issues or questions about the notification system, contact the development team or refer to:
- Laravel Sanctum Documentation
- Firebase Cloud Messaging Documentation
- Laravel Task Scheduling Documentation

