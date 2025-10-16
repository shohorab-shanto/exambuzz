# Written Answer Review System (Google Play Store Style)

## 📱 Overview

This system allows students to rate and review teachers' evaluations, and teachers can reply to student reviews - similar to the Google Play Store review system.

---

## 📊 Database Schema

### `written_answer_reviews` Table
```sql
- id (primary key)
- written_answer_id (which answer sheet)
- user_id (student who wrote the review)
- teacher_id (teacher who was reviewed)
- rating (1-5 stars)
- comment (student's review text)
- teacher_reply (teacher's response - nullable)
- replied_at (when teacher replied - nullable)
- created_at, updated_at
```

### `written_answers` Table (Updated)
```sql
- teacher_id (tracks which teacher evaluated - already exists)
```

---

## 🚀 API Endpoints

### 1. **Student Submits Review**

**Endpoint:** `POST /api/answer/submit-review`

**Authentication:** Required (Bearer Token - Student)

**Request Body:**
```json
{
  "written_answer_id": 123,
  "rating": 5,
  "comment": "Excellent evaluation! Very detailed feedback and helpful comments."
}
```

**Validation Rules:**
- `written_answer_id`: Required, must exist in written_answers table
- `rating`: Required, integer between 1-5
- `comment`: Required, string, max 1000 characters

**Response Success (201):**
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
    "comment": "Excellent evaluation! Very detailed feedback and helpful comments.",
    "teacher_reply": null,
    "replied_at": null,
    "created_at": "2025-10-16T17:50:00.000000Z",
    "updated_at": "2025-10-16T17:50:00.000000Z"
  }
}
```

**Response Errors:**
```json
// Answer sheet not found or not owned by student
{
  "status": false,
  "message": "Answer sheet not found or you do not have permission."
}

// Evaluation not complete
{
  "status": false,
  "message": "Cannot submit review before teacher evaluation is complete."
}

// No teacher assigned
{
  "status": false,
  "message": "No teacher assigned to this evaluation."
}
```

**Note:** If student already reviewed, it will UPDATE the existing review instead of creating new one.

---

### 2. **Teacher Replies to Review**

**Endpoint:** `POST /api/answer/reply-review`

**Authentication:** Required (Bearer Token - Teacher)

**Request Body:**
```json
{
  "review_id": 1,
  "reply": "Thank you for your feedback! I'm glad you found my evaluation helpful. Keep up the good work!"
}
```

**Validation Rules:**
- `review_id`: Required, must exist in written_answer_reviews table
- `reply`: Required, string, max 1000 characters

**Response Success (200):**
```json
{
  "status": true,
  "message": "Reply posted successfully",
  "data": {
    "id": 1,
    "written_answer_id": 123,
    "user_id": 1909,
    "teacher_id": 5,
    "rating": 5,
    "comment": "Excellent evaluation! Very detailed feedback and helpful comments.",
    "teacher_reply": "Thank you for your feedback! I'm glad you found my evaluation helpful. Keep up the good work!",
    "replied_at": "2025-10-16T18:00:00.000000Z",
    "created_at": "2025-10-16T17:50:00.000000Z",
    "updated_at": "2025-10-16T18:00:00.000000Z",
    "user": {
      "id": 1909,
      "name": "John Doe",
      "phone": "01700000000"
    },
    "teacher": {
      "id": 5,
      "name": "Mr. Rahman",
      "phone": "01800000000"
    }
  }
}
```

**Response Error:**
```json
// Review not found
{
  "status": false,
  "message": "Review not found."
}

// Not the teacher who was reviewed
{
  "status": false,
  "message": "You can only reply to your own reviews."
}
```

---

### 3. **View Review in Answer Details**

**Endpoint:** `POST /api/answer/show-preliminary-answer`

**Authentication:** Required (Bearer Token)

**Request Body:**
```json
{
  "written_id": 1
}
```

**Response (Written Answer with Review):**
```json
{
  "status": true,
  "message": "ok",
  "data": {
    "total_examinee": 150,
    "total_passed_examinee": 80,
    "my_position": 25,
    "subjects": [...],
    "sources": [...],
    "answer": {
      "id": 123,
      "user_id": 1909,
      "exam_id": 1,
      "teacher_id": 5,
      "obtained_mark": 75.00,
      "is_checked": 1,
      "created_at": "2025-10-16T17:00:00.000000Z",
      "updated_at": "2025-10-16T17:30:00.000000Z",
      "user": {...},
      "teacher": {
        "id": 5,
        "name": "Mr. Rahman",
        "phone": "01800000000"
      },
      "review": {
        "id": 1,
        "written_answer_id": 123,
        "user_id": 1909,
        "teacher_id": 5,
        "rating": 5,
        "comment": "Excellent evaluation! Very detailed feedback and helpful comments.",
        "teacher_reply": "Thank you for your feedback! I'm glad you found my evaluation helpful. Keep up the good work!",
        "replied_at": "2025-10-16T18:00:00.000000Z",
        "created_at": "2025-10-16T17:50:00.000000Z",
        "updated_at": "2025-10-16T18:00:00.000000Z",
        "user": {
          "id": 1909,
          "name": "John Doe"
        },
        "teacher": {
          "id": 5,
          "name": "Mr. Rahman"
        }
      },
      "writtenAnswerQuestion": [...]
    }
  }
}
```

**Note:** If no review exists, `review` will be `null`.

---

## 🎨 Frontend Implementation Example

### Student Review Form (After Viewing Evaluated Answer)

```html
<!-- Display Teacher Info -->
<div class="teacher-info">
  <h3>Evaluated by: {{ teacher.name }}</h3>
  <p>Marks: {{ obtained_mark }}</p>
</div>

<!-- Review Form -->
<div class="review-section">
  <h4>Rate This Evaluation</h4>
  
  <!-- Star Rating -->
  <div class="star-rating">
    <span class="star" data-rating="1">⭐</span>
    <span class="star" data-rating="2">⭐</span>
    <span class="star" data-rating="3">⭐</span>
    <span class="star" data-rating="4">⭐</span>
    <span class="star" data-rating="5">⭐</span>
  </div>
  
  <!-- Comment Textarea -->
  <textarea name="comment" placeholder="Write your review..." maxlength="1000"></textarea>
  
  <button onclick="submitReview()">Submit Review</button>
</div>

<!-- Existing Review Display -->
<div class="review-display" v-if="review">
  <div class="student-review">
    <div class="rating">⭐ {{ review.rating }}/5</div>
    <p>{{ review.comment }}</p>
    <small>{{ review.created_at }}</small>
  </div>
  
  <!-- Teacher Reply -->
  <div class="teacher-reply" v-if="review.teacher_reply">
    <strong>{{ review.teacher.name }} replied:</strong>
    <p>{{ review.teacher_reply }}</p>
    <small>{{ review.replied_at }}</small>
  </div>
</div>
```

### Teacher Reply Interface

```html
<!-- Student Review -->
<div class="student-review-card">
  <div class="student-info">
    <strong>{{ review.user.name }}</strong>
    <div class="rating">⭐ {{ review.rating }}/5</div>
  </div>
  <p>{{ review.comment }}</p>
  <small>{{ review.created_at }}</small>
  
  <!-- Reply Form -->
  <div class="reply-section" v-if="!review.teacher_reply">
    <textarea name="reply" placeholder="Reply to this review..." maxlength="1000"></textarea>
    <button onclick="replyToReview({{ review.id }})">Post Reply</button>
  </div>
  
  <!-- Your Reply -->
  <div class="your-reply" v-if="review.teacher_reply">
    <strong>Your Reply:</strong>
    <p>{{ review.teacher_reply }}</p>
    <small>{{ review.replied_at }}</small>
  </div>
</div>
```

---

## 🔒 Security Features

1. **Student Validation:**
   - Can only review their own answer sheets
   - Can only review after evaluation is complete
   - Can update their own reviews

2. **Teacher Validation:**
   - Can only reply to reviews about their own evaluations
   - Can update their replies

3. **Data Integrity:**
   - Foreign key constraints
   - Unique review per answer sheet (one review per student per evaluation)
   - Proper indexing for performance

---

## 🎯 Use Cases

### Student Workflow
1. Submit written exam
2. Wait for teacher evaluation
3. View evaluated answer with marks and comments
4. Submit rating (1-5 stars) and review comment
5. See teacher's reply (if any)
6. Update review if needed

### Teacher Workflow
1. Evaluate student's answer sheet
2. Receive notification of student review
3. Read student's rating and comment
4. Reply to student review
5. Update reply if needed

---

## 📊 Analytics Potential

With this system, you can now track:
- Average teacher ratings
- Most reviewed teachers
- Teacher response rate to reviews
- Student satisfaction with evaluations
- Popular feedback themes

---

## 🚀 Future Enhancements

1. **Helpful Votes:** Other students can mark reviews as helpful
2. **Report Abuse:** Flag inappropriate reviews
3. **Teacher Average Rating:** Display overall teacher rating
4. **Email Notifications:** Notify teachers of new reviews
5. **Review Guidelines:** Character limits, profanity filter
6. **Photo Attachments:** Allow images in reviews

---

## 📝 Testing

### Test Student Review Submission
```bash
curl -X POST http://localhost:8000/api/answer/submit-review \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "written_answer_id": 123,
    "rating": 5,
    "comment": "Great feedback!"
  }'
```

### Test Teacher Reply
```bash
curl -X POST http://localhost:8000/api/answer/reply-review \
  -H "Authorization: Bearer TEACHER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "review_id": 1,
    "reply": "Thank you for your feedback!"
  }'
```

---

Last Updated: October 16, 2025
Version: 1.0

