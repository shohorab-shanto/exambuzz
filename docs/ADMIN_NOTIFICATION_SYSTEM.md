# Admin Notification System

## Overview
Administrators can now send custom notifications to users through two methods:
1. **Notice Board** - Create notices that automatically notify users
2. **Custom Notifications** - Send direct announcements with targeting options

---

## Features

### ✅ Notice Board with Notifications
- Create notices that send push notifications
- Toggle notification sending on/off
- Target specific audiences (all, students, teachers)
- Track if notifications were sent
- Update notices and resend notifications if needed

### ✅ Custom Notification Center
- Send custom announcements without creating notices
- Flexible targeting: all users, students only, teachers only, or specific users
- Real-time statistics dashboard
- User list for targeted messaging
- Comprehensive logging

### ✅ Automatic Tracking
- Notification sent status
- Timestamp of when notifications were sent
- Success/failure counts
- Comprehensive logging

---

## Database Changes

### Notice Boards Table (New Fields)

```sql
ALTER TABLE notice_boards ADD COLUMN send_notification BOOLEAN DEFAULT TRUE;
ALTER TABLE notice_boards ADD COLUMN target_audience VARCHAR(255) DEFAULT 'all';
ALTER TABLE notice_boards ADD COLUMN notification_sent BOOLEAN DEFAULT FALSE;
ALTER TABLE notice_boards ADD COLUMN notification_sent_at TIMESTAMP NULL;
```

**Field Descriptions:**
- `send_notification`: Whether to send push notification to users
- `target_audience`: Who to notify (`all`, `user`, `teacher`)
- `notification_sent`: Track if notification was successfully sent
- `notification_sent_at`: When the notification was sent

---

## API Endpoints

### 1. Notice Board Endpoints

#### Create Notice (With Notification)
```
POST /admin/notice-board
Content-Type: multipart/form-data

Parameters:
- title (required): Notice title
- description (required): Notice content
- status (required): 0=Inactive, 1=Active
- send_notification (optional): true/false, default: true
- target_audience (optional): all/user/teacher, default: all

Response:
Redirects to notice-board.index with success message
```

**Behavior:**
- If `send_notification=true` AND `status=1`, notifications are sent immediately
- Notifications are sent to all active users matching `target_audience`
- Both push notifications (FCM) and in-app notifications are created

#### Update Notice (With Notification)
```
PUT /admin/notice-board/{id}
Content-Type: multipart/form-data

Parameters:
- title (required): Notice title
- description (required): Notice content
- status (required): 0=Inactive, 1=Active
- send_notification (optional): true/false
- target_audience (optional): all/user/teacher

Response:
Redirects to notice-board.index with success message
```

**Notification Logic:**
Notifications are sent if ALL conditions are met:
1. `send_notification=true`
2. `status=1`
3. Either:
   - Notice was previously inactive, OR
   - Notification was never sent before

---

### 2. Custom Notification Endpoints

#### Send Custom Notification
```
POST /admin/notifications/send
Content-Type: multipart/form-data

Parameters:
- title (required, string, max:255): Notification title
- message (required, string, max:500): Notification message
- target_audience (required): all|user|teacher|specific
- user_ids[] (required if target_audience=specific): Array of user IDs

Response:
{
  "status": true,
  "message": "Notification sent to X users. Push: Y successful, Z failed."
}
```

**Example - Send to All Users:**
```json
{
  "title": "System Maintenance",
  "message": "The system will be under maintenance tonight.",
  "target_audience": "all"
}
```

**Example - Send to Specific Users:**
```json
{
  "title": "Private Message",
  "message": "This is a targeted message.",
  "target_audience": "specific",
  "user_ids": [1, 5, 10, 23]
}
```

#### Get Notification Statistics
```
GET /admin/notifications/statistics

Response:
{
  "status": true,
  "data": {
    "total_users": 1500,
    "users_with_fcm": 1200,
    "total_students": 1300,
    "total_teachers": 200,
    "notifications_today": 50,
    "notifications_this_week": 250,
    "notifications_this_month": 980
  }
}
```

#### Get User List for Targeting
```
GET /admin/notifications/users?type=all

Query Parameters:
- type (optional): all|user|teacher, default: all
- page (optional): Page number for pagination

Response:
{
  "status": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "01700000000",
        "type": "user"
      }
    ],
    "per_page": 50,
    "total": 1500
  }
}
```

---

## Web Routes

```php
// Notice Board (existing, enhanced)
Route::resource('/notice-board', NoticeBoardController::class);

// Admin Notification Management (new)
Route::prefix('/notifications')->name('notifications.')->group(function () {
    Route::get('/', [AdminNotificationController::class, 'index'])->name('index');
    Route::post('/send', [AdminNotificationController::class, 'sendNotification'])->name('send');
    Route::get('/statistics', [AdminNotificationController::class, 'statistics'])->name('statistics');
    Route::get('/users', [AdminNotificationController::class, 'getUserList'])->name('users');
});
```

---

## How It Works

### Scenario 1: Create Notice with Notification

1. **Admin creates a notice:**
   ```
   POST /admin/notice-board
   - title: "Exam Schedule Updated"
   - description: "BCS exam rescheduled to next week"
   - status: 1 (active)
   - send_notification: true
   - target_audience: "user" (students only)
   ```

2. **System processes:**
   - Creates notice in `notice_boards` table
   - Queries all active users where `type='user'`
   - For each user:
     - Creates record in `notifications` table
     - Sends push notification via FCM (if user has `fcm_token`)
   - Updates notice with `notification_sent=true` and `notification_sent_at=now()`

3. **Result:**
   - All students receive push notification
   - All students see notification in app
   - Admin sees success message with statistics

### Scenario 2: Send Custom Announcement

1. **Admin sends custom notification:**
   ```
   POST /admin/notifications/send
   - title: "Important: System Maintenance"
   - message: "System will be down from 2 AM to 4 AM"
   - target_audience: "all"
   ```

2. **System processes:**
   - Queries all active users (no type filter)
   - Processes in chunks of 200 users
   - For each user:
     - Creates notification record
     - Sends FCM push notification
     - Tracks success/failure
   - Logs statistics

3. **Result:**
   - All users receive notification
   - Admin sees: "Notification sent to 1500 users. Push: 1200 successful, 300 failed."
   - Detailed log entry created

### Scenario 3: Targeted Notification

1. **Admin wants to notify specific users:**
   - Uses "Get User List" endpoint to find users
   - Selects user IDs: [5, 12, 45, 67]
   - Sends notification:
   ```
   POST /admin/notifications/send
   - title: "Special Announcement"
   - message: "You have been selected for advanced training"
   - target_audience: "specific"
   - user_ids: [5, 12, 45, 67]
   ```

2. **System processes:**
   - Queries only specified user IDs
   - Sends notifications to those 4 users only

3. **Result:**
   - Only selected users receive notification
   - Precise targeting achieved

---

## Target Audience Options

| Value | Description | Users Notified |
|-------|-------------|----------------|
| `all` | All users | All active users (students + teachers + admins) |
| `user` | Students only | Users where `type='user'` |
| `teacher` | Teachers only | Users where `type='teacher'` |
| `specific` | Specific users | Only users with IDs in `user_ids[]` array |

---

## Notification Flow

```
┌─────────────────────┐
│  Admin Action       │
│  (Notice/Custom)    │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Determine Target    │
│ Audience            │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Query Users         │
│ (chunked, 200/batch)│
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ For Each User:      │
│ 1. Create in-app    │
│    notification     │
│ 2. Send FCM push    │
│    (if has token)   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Track & Log         │
│ - Success count     │
│ - Failure count     │
│ - Timestamp         │
└─────────────────────┘
```

---

## Error Handling

### Notice Board
- If notification sending fails, error is logged but notice is still created
- Admin sees success message for notice creation
- Failures are logged in `storage/logs/laravel.log`

### Custom Notifications
- If sending fails completely, admin sees error message
- Partial failures are reported: "Push: X successful, Y failed"
- All attempts are logged with details

### Logging Format
```php
// Success
Log::info('Notice notification sent', [
    'notice_id' => 1,
    'success' => 1200,
    'failed' => 300
]);

// Failure
Log::error('Failed to send notice notification', [
    'notice_id' => 1,
    'error' => 'Exception message'
]);
```

---

## Testing

### Test Notice with Notification
```bash
# Using cURL
curl -X POST http://localhost/admin/notice-board \
  -F "title=Test Notice" \
  -F "description=This is a test notice" \
  -F "status=1" \
  -F "send_notification=true" \
  -F "target_audience=user"
```

### Test Custom Notification
```bash
curl -X POST http://localhost/admin/notifications/send \
  -F "title=Test Announcement" \
  -F "message=This is a test" \
  -F "target_audience=all"
```

### Check Statistics
```bash
curl -X GET http://localhost/admin/notifications/statistics
```

### Verify Notifications Sent
```sql
-- Check latest notifications
SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10;

-- Check notice notification status
SELECT id, title, notification_sent, notification_sent_at 
FROM notice_boards 
WHERE notification_sent = 1;
```

---

## Best Practices

### For Admins

1. **Use Appropriate Targeting**
   - Don't send student-specific content to teachers
   - Use `specific` for private/sensitive messages
   - Use `all` sparingly to avoid notification fatigue

2. **Keep Messages Concise**
   - Max 500 characters for custom notifications
   - Push notification body is limited to 150 characters
   - Write clear, actionable messages

3. **Check Statistics First**
   - Review user counts before sending
   - Ensure FCM token coverage is acceptable
   - Monitor delivery success rates

4. **Test Before Sending**
   - Use `specific` targeting to send to yourself first
   - Verify message content and formatting
   - Check both push and in-app appearance

### For Developers

1. **Monitor Logs**
   - Check `storage/logs/laravel.log` regularly
   - Watch for FCM failures
   - Investigate patterns in failed sends

2. **Performance**
   - System processes 200 users per chunk
   - Large sends may take time
   - Consider background jobs for > 1000 users

3. **Database Maintenance**
   - Old notifications can be archived
   - Consider soft deletes for user history
   - Monitor `notifications` table growth

---

## Migration Instructions

### 1. Run the Migration
```bash
php artisan migrate
```

This creates:
- New fields in `notice_boards` table

### 2. Update Existing Notices (Optional)
```sql
-- Set default values for existing notices
UPDATE notice_boards 
SET send_notification = true,
    target_audience = 'all',
    notification_sent = false
WHERE send_notification IS NULL;
```

### 3. Test the System
1. Create a test notice with notification enabled
2. Verify users receive notifications
3. Check logs for any errors
4. Test custom notification sending

### 4. Update Admin Panel Views (Optional)
Add form fields for new parameters in:
- `resources/views/backend/notice-board/create.blade.php`

```html
<!-- Send Notification Checkbox -->
<div class="form-group">
    <label>Send Notification</label>
    <input type="checkbox" name="send_notification" value="1" checked>
</div>

<!-- Target Audience -->
<div class="form-group">
    <label>Target Audience</label>
    <select name="target_audience" class="form-control">
        <option value="all">All Users</option>
        <option value="user">Students Only</option>
        <option value="teacher">Teachers Only</option>
    </select>
</div>
```

---

## Troubleshooting

### Notifications Not Sending

**Problem:** Created notice but users didn't receive notifications

**Solutions:**
1. Check if `send_notification=true` and `status=1`
2. Verify users have `fcm_token` set
3. Check `FCM_SERVER_KEY` in `.env`
4. Review logs: `tail -f storage/logs/laravel.log`
5. Verify notice has `notification_sent=true` in database

### Low Success Rate

**Problem:** Many notifications showing as failed

**Solutions:**
1. FCM tokens may be expired - users need to login again
2. Check FCM server key is valid
3. Review error logs for specific failures
4. Verify network connectivity to FCM servers

### Statistics Not Showing

**Problem:** Statistics endpoint returns zeros or errors

**Solutions:**
1. Check database connections
2. Verify users table has data
3. Ensure migrations ran successfully
4. Check for PHP errors in logs

---

## Security Considerations

1. **Admin Only Access**
   - All endpoints are protected by admin middleware
   - Regular users cannot access these features
   - Verify authentication before deployment

2. **Input Validation**
   - All inputs are validated
   - XSS protection on message content
   - SQL injection prevention via Eloquent

3. **Rate Limiting**
   - Consider adding rate limiting for bulk sends
   - Prevent spam/abuse of notification system
   - Monitor usage patterns

4. **Privacy**
   - Targeted notifications leave audit trail
   - User IDs are logged (for accountability)
   - Messages are not encrypted in database

---

## Future Enhancements

Potential improvements for future versions:

1. **Scheduling**
   - Schedule notifications for later
   - Recurring notifications
   - Time zone awareness

2. **Templates**
   - Predefined notification templates
   - Variable substitution (e.g., {username})
   - Multi-language support

3. **Analytics**
   - Track notification open rates
   - User engagement metrics
   - A/B testing for messages

4. **Advanced Targeting**
   - Filter by package subscriptions
   - Target by exam participation
   - Geographic targeting

5. **Delivery Optimization**
   - Queue large sends
   - Retry failed deliveries
   - Batch FCM requests

---

## Support

For questions or issues:
- Check logs: `storage/logs/laravel.log`
- Review main documentation: `docs/NOTIFICATION_SYSTEM.md`
- Test with Postman collection
- Contact development team

---

**Implementation Date:** October 22, 2025

**Status:** ✅ Production Ready

**Version:** 1.0

