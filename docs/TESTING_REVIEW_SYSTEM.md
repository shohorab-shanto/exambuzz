# 🧪 Testing Guide: Review System

This guide will help you test the Google Play Store-style review system for written exam evaluations.

---

## 📋 Prerequisites

Before testing, ensure you have:
1. ✅ Laravel server running (`php artisan serve`)
2. ✅ MySQL database running
3. ✅ Postman installed (or any API testing tool)
4. ✅ At least 2 user accounts:
   - **Student account** (to submit reviews)
   - **Teacher account** (to reply to reviews)

---

## 🎯 Step-by-Step Testing Process

### **Phase 1: Setup Test Data**

#### 1. Create Test Users (if not exist)

**Register Student:**
```bash
POST http://localhost:8000/api/register
{
  "name": "Test Student",
  "phone": "01700000001",
  "password": "password123",
  "password_confirmation": "password123"
}
```
**Save the student token:** `student_token`

**Register Teacher:**
```bash
POST http://localhost:8000/api/register
{
  "name": "Test Teacher",
  "phone": "01700000002",
  "password": "password123",
  "password_confirmation": "password123"
}
```
**Save the teacher token:** `teacher_token`

#### 2. Get Existing Written Answer ID

**Option A: Check your database**
```sql
SELECT id, user_id, teacher_id, is_checked 
FROM written_answers 
WHERE is_checked = 1 AND teacher_id IS NOT NULL
LIMIT 1;
```

**Option B: If no data exists, you need to:**
1. Create a written exam
2. Student submits answer
3. Teacher evaluates it (sets `is_checked = 1` and `teacher_id`)

---

### **Phase 2: Test Student Review Submission**

#### Test Case 1: ✅ Submit Valid Review

**Request:**
```bash
POST http://localhost:8000/api/answer/submit-review
Authorization: Bearer {student_token}
Content-Type: application/x-www-form-urlencoded

written_answer_id=123
rating=5
comment=Excellent evaluation! Very detailed feedback and helpful comments on each question. Really appreciate the time spent.
```

**Expected Response (200 OK):**
```json
{
  "status": true,
  "message": "Review submitted successfully",
  "data": {
    "id": 1,
    "written_answer_id": 123,
    "user_id": 1909,
    "teacher_id": 5,
    "rating": 5,
    "comment": "Excellent evaluation! Very detailed feedback...",
    "teacher_reply": null,
    "replied_at": null,
    "created_at": "2025-10-16T18:00:00.000000Z",
    "updated_at": "2025-10-16T18:00:00.000000Z"
  }
}
```

**✅ Success Criteria:**
- Status is `true`
- Message says "Review submitted successfully"
- `review_id` is returned
- `teacher_reply` is `null` (not replied yet)

---

#### Test Case 2: ✅ Update Existing Review

**Request:** (Same student, same answer sheet, different rating/comment)
```bash
POST http://localhost:8000/api/answer/submit-review
Authorization: Bearer {student_token}

written_answer_id=123
rating=4
comment=Updated my review - still very good but noticed some minor issues.
```

**Expected Response (200 OK):**
```json
{
  "status": true,
  "message": "Review updated successfully",
  "data": {
    "id": 1,  // SAME ID
    "rating": 4,  // UPDATED
    "comment": "Updated my review...",  // UPDATED
    ...
  }
}
```

**✅ Success Criteria:**
- Same `id` as before (updated, not created new)
- Rating and comment are updated
- Message says "Review updated successfully"

---

#### Test Case 3: ❌ Review Before Evaluation Complete

**Request:** (Using answer with `is_checked = 0`)
```bash
POST http://localhost:8000/api/answer/submit-review
Authorization: Bearer {student_token}

written_answer_id=999
rating=5
comment=Great job!
```

**Expected Response (400 Bad Request):**
```json
{
  "status": false,
  "message": "Cannot submit review before teacher evaluation is complete."
}
```

---

#### Test Case 4: ❌ Invalid Rating (< 1 or > 5)

**Request:**
```bash
POST http://localhost:8000/api/answer/submit-review
Authorization: Bearer {student_token}

written_answer_id=123
rating=6  ❌ INVALID
comment=Test
```

**Expected Response (422 Validation Error):**
```json
{
  "message": "The rating field must be between 1 and 5.",
  "errors": {
    "rating": [
      "The rating field must be between 1 and 5."
    ]
  }
}
```

---

#### Test Case 5: ❌ Missing Required Fields

**Request:**
```bash
POST http://localhost:8000/api/answer/submit-review
Authorization: Bearer {student_token}

written_answer_id=123
rating=5
# comment is missing ❌
```

**Expected Response (422 Validation Error):**
```json
{
  "message": "The comment field is required.",
  "errors": {
    "comment": ["The comment field is required."]
  }
}
```

---

#### Test Case 6: ❌ Review Someone Else's Answer

**Request:** (Student A tries to review Student B's answer)
```bash
POST http://localhost:8000/api/answer/submit-review
Authorization: Bearer {different_student_token}

written_answer_id=123  ❌ Not owned by this student
rating=5
comment=Test
```

**Expected Response (403 Forbidden):**
```json
{
  "status": false,
  "message": "Answer sheet not found or you do not have permission."
}
```

---

### **Phase 3: Test Teacher Reply**

#### Test Case 7: ✅ Teacher Replies to Review

**First, get the review_id from Test Case 1 response**

**Request:**
```bash
POST http://localhost:8000/api/answer/reply-review
Authorization: Bearer {teacher_token}

review_id=1
reply=Thank you so much for your kind words! I'm glad you found my feedback helpful. Keep up the excellent work and feel free to reach out if you have any questions!
```

**Expected Response (200 OK):**
```json
{
  "status": true,
  "message": "Reply posted successfully",
  "data": {
    "id": 1,
    "rating": 5,
    "comment": "Excellent evaluation!...",
    "teacher_reply": "Thank you so much for your kind words!...",
    "replied_at": "2025-10-16T18:30:00.000000Z",  ✅ NOW SET
    "user": {
      "id": 1909,
      "name": "Test Student",
      "phone": "01700000001"
    },
    "teacher": {
      "id": 5,
      "name": "Test Teacher",
      "phone": "01700000002"
    }
  }
}
```

**✅ Success Criteria:**
- `teacher_reply` contains the reply text
- `replied_at` timestamp is set
- Both `user` and `teacher` objects are included

---

#### Test Case 8: ✅ Update Teacher Reply

**Request:** (Same teacher, same review, different reply)
```bash
POST http://localhost:8000/api/answer/reply-review
Authorization: Bearer {teacher_token}

review_id=1
reply=Updated reply: Thanks for the feedback! I've noted your suggestions for improvement.
```

**Expected Response (200 OK):**
```json
{
  "status": true,
  "message": "Reply posted successfully",
  "data": {
    "teacher_reply": "Updated reply: Thanks for the feedback!...",  ✅ UPDATED
    "replied_at": "2025-10-16T19:00:00.000000Z"  ✅ UPDATED TIMESTAMP
  }
}
```

---

#### Test Case 9: ❌ Different Teacher Tries to Reply

**Request:** (Teacher B tries to reply to Teacher A's review)
```bash
POST http://localhost:8000/api/answer/reply-review
Authorization: Bearer {different_teacher_token}

review_id=1  ❌ Not this teacher's review
reply=Test
```

**Expected Response (403 Forbidden):**
```json
{
  "status": false,
  "message": "You can only reply to your own reviews."
}
```

---

#### Test Case 10: ❌ Invalid Review ID

**Request:**
```bash
POST http://localhost:8000/api/answer/reply-review
Authorization: Bearer {teacher_token}

review_id=999999  ❌ Doesn't exist
reply=Test
```

**Expected Response (422 Validation Error):**
```json
{
  "message": "The selected review id is invalid.",
  "errors": {
    "review_id": ["The selected review id is invalid."]
  }
}
```

---

### **Phase 4: Test Review Display in Answer Details**

#### Test Case 11: ✅ View Answer with Review

**Request:**
```bash
POST http://localhost:8000/api/answer/show-preliminary-answer
Authorization: Bearer {student_token}

written_id=1
```

**Expected Response (200 OK):**
```json
{
  "status": true,
  "message": "ok",
  "data": {
    "answer": {
      "id": 123,
      "obtained_mark": 75.00,
      "teacher": {
        "id": 5,
        "name": "Test Teacher"
      },
      "review": {  ✅ REVIEW DATA INCLUDED
        "id": 1,
        "rating": 5,
        "comment": "Excellent evaluation!...",
        "teacher_reply": "Thank you so much...",
        "replied_at": "2025-10-16T18:30:00.000000Z",
        "user": {...},
        "teacher": {...}
      }
    }
  }
}
```

**✅ Success Criteria:**
- `review` object is present
- Contains rating, comment, teacher_reply
- `replied_at` is set if teacher replied

---

#### Test Case 12: ✅ View Answer WITHOUT Review

**Request:** (Answer that hasn't been reviewed yet)
```bash
POST http://localhost:8000/api/answer/show-preliminary-answer
Authorization: Bearer {student_token}

written_id=2  # Different exam with no review
```

**Expected Response (200 OK):**
```json
{
  "status": true,
  "message": "ok",
  "data": {
    "answer": {
      "id": 456,
      "obtained_mark": 65.00,
      "review": null  ✅ NULL when no review exists
    }
  }
}
```

---

## 🧪 Quick Test Script (cURL)

Save this as `test_review_system.sh`:

```bash
#!/bin/bash

# Configuration
BASE_URL="http://localhost:8000/api"
STUDENT_TOKEN="your_student_token_here"
TEACHER_TOKEN="your_teacher_token_here"
ANSWER_ID="123"

echo "🧪 Testing Review System..."
echo ""

# Test 1: Student submits review
echo "Test 1: Student submits review..."
curl -X POST "$BASE_URL/answer/submit-review" \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "written_answer_id=$ANSWER_ID&rating=5&comment=Excellent evaluation!"
echo -e "\n\n"

# Wait for user to see response
sleep 2

# Test 2: Teacher replies to review
echo "Test 2: Teacher replies to review..."
REVIEW_ID=1  # Update this with the ID from Test 1 response
curl -X POST "$BASE_URL/answer/reply-review" \
  -H "Authorization: Bearer $TEACHER_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "review_id=$REVIEW_ID&reply=Thank you for your feedback!"
echo -e "\n\n"

# Test 3: View answer with review
echo "Test 3: View answer with review..."
curl -X POST "$BASE_URL/answer/show-preliminary-answer" \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "written_id=1"
echo -e "\n\n"

echo "✅ All tests completed!"
```

Make it executable:
```bash
chmod +x test_review_system.sh
./test_review_system.sh
```

---

## 📊 Database Verification

After testing, verify data in database:

```sql
-- Check reviews table
SELECT * FROM written_answer_reviews ORDER BY id DESC LIMIT 5;

-- Check answer with teacher info
SELECT 
    wa.id,
    wa.user_id,
    wa.teacher_id,
    wa.is_checked,
    war.rating,
    war.comment,
    war.teacher_reply,
    war.replied_at
FROM written_answers wa
LEFT JOIN written_answer_reviews war ON wa.id = war.written_answer_id
WHERE wa.id = 123;

-- Check teacher's average rating
SELECT 
    teacher_id,
    COUNT(*) as total_reviews,
    AVG(rating) as average_rating
FROM written_answer_reviews
GROUP BY teacher_id;
```

---

## ✅ Expected Results Summary

| Test Case | Description | Expected Status | Expected Message |
|-----------|-------------|----------------|------------------|
| 1 | Submit valid review | 200 OK | Review submitted successfully |
| 2 | Update existing review | 200 OK | Review updated successfully |
| 3 | Review before evaluation | 400 | Cannot submit review before... |
| 4 | Invalid rating | 422 | Rating must be between 1 and 5 |
| 5 | Missing required fields | 422 | Field is required |
| 6 | Review others' answer | 403 | Not your permission |
| 7 | Teacher replies | 200 OK | Reply posted successfully |
| 8 | Update teacher reply | 200 OK | Reply posted successfully |
| 9 | Different teacher replies | 403 | Only reply to own reviews |
| 10 | Invalid review ID | 422 | Invalid review id |
| 11 | View answer with review | 200 OK | Review object included |
| 12 | View answer without review | 200 OK | Review is null |

---

## 🐛 Common Issues & Solutions

### Issue 1: "Column 'teacher_id' not found"
**Solution:** Run the migration:
```bash
php artisan migrate --path=/database/migrations/2025_10_16_175000_add_teacher_id_to_written_answers_table.php
```

### Issue 2: "Table 'written_answer_reviews' doesn't exist"
**Solution:** Run the migration:
```bash
php artisan migrate --path=/database/migrations/2025_10_16_174923_create_written_answer_reviews_table.php
```

### Issue 3: "Route [answer/submit-review] not defined"
**Solution:** Clear route cache:
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Issue 4: "No teacher assigned to this evaluation"
**Solution:** Update the written_answer record:
```sql
UPDATE written_answers 
SET teacher_id = 5  -- Use actual teacher user_id
WHERE id = 123;
```

---

## 📱 Testing with Postman

1. **Import Collection:**
   - Import `docs/Exam App Complete API.postman_collection.json`

2. **Set Variables:**
   - Go to Collection → Variables
   - Set `base_url` = `http://localhost:8000/api/`
   - Set `token` = your auth token

3. **Navigate to:**
   - Answer Management → Submit Review (Student)
   - Answer Management → Reply to Review (Teacher)

4. **Run Tests:**
   - Click "Send" on each request
   - Verify responses match expected results

---

## 🎯 Success Checklist

- [ ] Student can submit review with 1-5 star rating
- [ ] Student can write comment (up to 1000 chars)
- [ ] Student can update their review
- [ ] System prevents review before evaluation complete
- [ ] System validates rating range (1-5)
- [ ] Teacher can reply to reviews
- [ ] Teacher can update their reply
- [ ] System prevents teachers from replying to others' reviews
- [ ] Review appears in answer details API
- [ ] Review is null when not submitted
- [ ] Database stores all data correctly
- [ ] Timestamps update correctly

---

**Last Updated:** October 16, 2025  
**Version:** 1.0

🎉 Happy Testing!

