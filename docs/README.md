# Exam App API Documentation

## 🔴 BREAKING CHANGES - October 22, 2025

### Multiple Live Exams Support

**Endpoint**: `POST /api/exam/check-live-exam`

The system now returns **ALL live exams** instead of just the first one when multiple exams are scheduled simultaneously in the same section.

#### What Changed:
| Before | After |
|--------|-------|
| `data.exam` (single object) | `data.exams` (array) |
| N/A | `data.total_live_exams` (integer) |
| Single exam returned | All concurrent exams returned |

#### Migration Guide:
```javascript
// ❌ OLD CODE (Will break)
if (response.data.is_live_exam && response.data.exam) {
    showExam(response.data.exam);
}

// ✅ NEW CODE (Required)
if (response.data.is_live_exam && response.data.exams.length > 0) {
    if (response.data.exams.length === 1) {
        showExam(response.data.exams[0]);
    } else {
        // Show selection UI for multiple exams
        showExamSelectionList(response.data.exams);
    }
}
```

**📖 See**: `CHANGELOG_LIVE_EXAMS.md` for detailed documentation

---

## 🆕 NEW FEATURE - November 10, 2025

### bKash Payment Gateway Integration

**Mobile app users can now purchase packages directly using bKash!**

#### Features:
- ✅ Complete automated payment flow
- ✅ Create payment and redirect to bKash
- ✅ Automatic callback handling
- ✅ Package activation on successful payment
- ✅ Payment status checking
- ✅ Transaction search and verification

#### Quick Start:
```kotlin
// 1. Create Payment
POST /api/bkash/create-payment
Body: { package_id: 1, user_id: 1, amount: 1000 }

// 2. Open bkashURL in WebView
val intent = Intent(Intent.ACTION_VIEW, Uri.parse(bkashURL))
startActivity(intent)

// 3. User completes payment → Package activated automatically
```

#### Test Credentials (Sandbox):
- Wallet: `01770618567`
- OTP: `123456`
- PIN: `12345`

#### New Endpoints:
- `POST /api/bkash/create-payment` - Create payment
- `POST /api/bkash/check-payment-status` - Check payment status
- `POST /api/bkash/search-transaction` - Search transaction
- `GET /api/bkash/callback` - Auto callback (handled by bKash)

**📖 Documentation:**
- Complete Guide: `docs/BKASH_PAYMENT_INTEGRATION.md`
- Quick Start: `docs/BKASH_QUICK_START.md`
- Postman: `docs/bKash_Payment_API.postman_collection.json`
- Summary: `BKASH_IMPLEMENTATION_SUMMARY.md`

---

## Postman Collections

This directory contains comprehensive Postman collections for the Exam App APIs.

### Files

1. **Exam App.postman_collection.json** (Original)
   - Size: 1.1MB (6,907 lines)
   - Legacy collection with detailed examples

2. **Exam App Complete API.postman_collection.json** (New - 2025)
   - Size: 37KB (1,859 lines)
   - Complete API collection with all 65+ endpoints
   - Updated with latest changes (subject and topic support)
   - Clean structure with proper organization

---

## What's New in 2025 Update

### ✅ Added Features

1. **Subject and Topic Support for Questions**
   - All question APIs now return `subject` and `topic` objects
   - Questions can be filtered and organized by subject/topic
   - Better analytics and performance tracking

2. **Enhanced Answer Statistics**
   - `positive_count` - Total correct answers
   - `negative_count` - Total wrong answers
   - `empty_count` - Total unanswered questions
   - `obtained_marks` - Final calculated score
   - `result_status` - Pass/Fail status
   - Merit list positions

3. **Subject and Topic Based Performance Breakdown** ⭐ NEW
   - `subject_breakdown` - Performance analysis per subject
     - Total questions per subject
     - Correct answers per subject
     - Wrong answers per subject
     - Skipped questions per subject
   - `topic_breakdown` - Detailed performance per topic
     - Total questions per topic
     - Correct answers per topic
     - Wrong answers per topic
     - Skipped questions per topic
   - Helps identify weak areas for targeted improvement

4. **Updated Endpoints**
   - `/api/exam/check-live-exam` - **🔴 BREAKING CHANGE (Oct 22, 2025)** - Returns ALL live exams as array (`exams`) instead of single object (`exam`). Added `total_live_exams` field.
   - `/api/exam/archive-exam-question-details` - Questions with subject/topic info
   - `/api/answer/show-preliminary-answer` - Enhanced statistics + subject/topic breakdown ⭐
   - `/api/answer/preliminary-answer-script` - Questions with subject/topic
   - `/api/exam/favorite-list` - Favorites with subject/topic info

---

## API Categories

### 1. Authentication (7 endpoints)
- Register
- Login
- Verify OTP
- Resend OTP
- Forgot Password
- Reset Password
- Logout

### 2. Exam Management (15 endpoints)
- Check Live Exam ⭐ (with subject/topic)
- Get Routine (Upcoming)
- Get All Routine
- Get Archive Exams
- Archive Exam Question Details ⭐ (with subject/topic)
- Get Syllabus
- Get Subject List
- Get Present Live Exam
- Toggle Favorite
- Get Favorite List ⭐ (with subject/topic)
- Get Result List
- Get Merit List
- Get Merit List V2

### 3. Answer Management (9 endpoints)
- Store Preliminary Answer
- Show Preliminary Answer ⭐ (with statistics + review)
- Preliminary Answer Script ⭐ (with subject/topic)
- Preliminary Answer Merit List
- Preliminary Answer Merit List V2
- Store Written Answer
- Store Written Answer V2
- Submit Review (Student) ⭐ NEW
- Reply to Review (Teacher) ⭐ NEW

### 4. User Profile (3 endpoints)
- Get User Profile
- Update Profile
- Ask Query

### 5. Packages & Subscription (9 endpoints)
- Get Packages
- Get Upcoming Packages
- Purchase Package
- Get Package History
- Get Package History V2
- **bKash Create Payment** ⭐ NEW
- **bKash Check Payment Status** ⭐ NEW
- **bKash Search Transaction** ⭐ NEW
- **bKash Callback (Auto)** ⭐ NEW

### 6. Revision Module (6 endpoints)
- Get Revision Subject List
- Get Revision Topic List
- Get Revision Question List
- Get Revision Question List by Subject
- Toggle Revision Question Favorite
- Mark Revision Question as Read

### 7. Material & Study Resources (2 endpoints)
- Get Materials
- Get Materials V2

### 8. Notifications (4 endpoints)
- Get Notifications
- Mark Notification as Seen
- Store FCM Token
- Get Notice Board

### 9. Teacher Panel (6 endpoints)
- Get Exam and Papers
- Store Exam Paper Assessment
- Get Teacher Wallet
- Withdrawal Request
- Get Teacher Dashboard
- Get Top 3 Students (Written)

### 10. Miscellaneous (4 endpoints)
- Get Categories
- Privacy Policy
- Contact Us
- Account Deletion

---

## How to Use

### Setup in Postman

1. **Import the Collection**
   ```
   File → Import → Select "Exam App Complete API.postman_collection.json"
   ```

2. **Configure Variables**
   - `base_url`: Default is `http://localhost:8000/api/`
   - `token`: Set after login (automatically used for authenticated endpoints)

3. **Authentication Flow**
   ```
   1. Register OR Login
   2. Copy the token from response
   3. Set it in Collection Variables: token = YOUR_TOKEN
   4. All authenticated endpoints will use it automatically
   ```

### Example Response Structure

#### Questions with Subject & Topic (New Feature)
```json
{
  "questions": [
    {
      "id": 1,
      "exam_id": 384,
      "subject_id": 1,
      "topic_id": 5,
      "question_name": "What is...?",
      "subject": {
        "id": 1,
        "name": "English"
      },
      "topic": {
        "id": 5,
        "topic": "Grammar",
        "source": "Cambridge",
        "subject_id": 1
      },
      "questionOptions": [...]
    }
  ]
}
```

#### Answer Statistics (Enhanced)
```json
{
  "answer": {
    "positive_count": 75,
    "negative_count": 20,
    "empty_count": 5,
    "positive_marks": 75.0,
    "negative_marks": 5.0,
    "empty_marks": 0,
    "obtained_marks": 70.0,
    "result_status": 1
  },
  "subject_breakdown": [
    {
      "subject_id": 1,
      "subject_name": "Bangla",
      "total_questions": 35,
      "correct": 28,
      "wrong": 5,
      "skipped": 2
    },
    {
      "subject_id": 2,
      "subject_name": "English",
      "total_questions": 35,
      "correct": 25,
      "wrong": 8,
      "skipped": 2
    }
  ],
  "topic_breakdown": [
    {
      "topic_id": 1,
      "topic_name": "Grammar",
      "subject_id": 1,
      "subject_name": "Bangla",
      "total_questions": 15,
      "correct": 12,
      "wrong": 2,
      "skipped": 1
    },
    {
      "topic_id": 2,
      "topic_name": "Literature",
      "subject_id": 1,
      "subject_name": "Bangla",
      "total_questions": 20,
      "correct": 16,
      "wrong": 3,
      "skipped": 1
    }
  ]
}
```

---

## API Endpoints Summary

**Total Endpoints:** 67+

| Category | Endpoints | Auth Required |
|----------|-----------|---------------|
| Authentication | 7 | No (except Logout) |
| Exam Management | 15 | Yes |
| Answer Management | 9 | Yes |
| User Profile | 3 | Yes |
| Packages | 4 | Mixed |
| Revision | 6 | Yes |
| Material | 2 | Yes |
| Notifications | 4 | Mixed |
| Teacher Panel | 6 | Yes |
| Miscellaneous | 4 | Mixed |

---

## Database Changes (2025)

### New Column Added
- `exam_questions.topic_id` (nullable)
  - Links questions to specific topics
  - Allows better filtering and analytics

### Data Migration
- All existing questions assigned to "Others" topic via seeder
- 13,392 questions updated successfully

---

## Notes

- ⭐ indicates endpoints with major updates in 2025
- All timestamps are in Asia/Dhaka timezone
- Bearer token authentication is used for protected routes
- File uploads use `multipart/form-data`
- Pagination is available on list endpoints

---

## Support

For issues or questions:
- Check the existing responses in Postman examples
- Verify authentication token is set correctly
- Ensure base_url points to correct server
- Check request body format matches documentation

---

Last Updated: October 16, 2025
Version: 2.0

