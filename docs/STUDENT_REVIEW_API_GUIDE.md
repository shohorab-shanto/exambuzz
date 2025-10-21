# 📱 Student Review API - Complete Guide

**Date:** October 16, 2025  
**Status:** ✅ Already Implemented & Working

---

## ✅ **YES, API Already Exists!**

The student review submission API is **already implemented** and ready to use.

---

## 🚀 **API Endpoint**

### **Submit Review (Student)**

**Endpoint:** `POST /api/answer/submit-review`

**Authentication:** ✅ Required (Bearer Token)

**Purpose:** Students can submit ratings and comments for teacher evaluations on written exams.

---

## 📋 **Request Details**

### **URL:**
```
POST {{base_url}}/api/answer/submit-review
```

### **Headers:**
```json
{
  "Authorization": "Bearer {student_token}",
  "Content-Type": "application/json"
}
```

### **Request Body (Form-Data or JSON):**
```json
{
  "written_answer_id": 123,
  "rating": 5,
  "comment": "Excellent evaluation! Very detailed feedback and helpful comments."
}
```

### **Parameters:**

| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| `written_answer_id` | integer | ✅ Yes | Must exist in `written_answers` table | The ID of the evaluated answer sheet |
| `rating` | integer | ✅ Yes | Min: 1, Max: 5 | Star rating (1-5) |
| `comment` | string | ✅ Yes | Max: 1000 characters | Student's review comment |

---

## 📤 **Response Examples**

### **Success Response (New Review):**
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

### **Success Response (Update Existing Review):**
```json
{
  "status": true,
  "message": "Review updated successfully",
  "data": {
    "id": 1,
    "written_answer_id": 123,
    "user_id": 1909,
    "teacher_id": 5,
    "rating": 4,
    "comment": "Good evaluation, but I think question #5 was marked incorrectly.",
    "created_at": "2025-10-16T17:50:00.000000Z",
    "updated_at": "2025-10-16T18:30:00.000000Z"
  }
}
```

### **Error Responses:**

#### **1. Answer Not Found:**
```json
{
  "status": false,
  "message": "Answer sheet not found or you do not have permission.",
  "data": ""
}
```

#### **2. Not Yet Evaluated:**
```json
{
  "status": false,
  "message": "Cannot submit review before teacher evaluation is complete.",
  "data": ""
}
```

#### **3. No Teacher Assigned:**
```json
{
  "status": false,
  "message": "No teacher assigned to this evaluation.",
  "data": ""
}
```

#### **4. Validation Errors:**
```json
{
  "status": false,
  "message": "The rating field is required.",
  "data": ""
}
```

---

## 🔐 **Security & Validation**

### **Access Control:**
- ✅ Student must be authenticated (Bearer token)
- ✅ Student can only review their own answer sheets
- ✅ Answer sheet must be evaluated (`is_checked = 1`)
- ✅ Teacher must be assigned to the evaluation

### **Validation Rules:**
```php
'written_answer_id' => 'required|exists:written_answers,id',
'rating' => 'required|integer|min:1|max:5',
'comment' => 'required|string|max:1000',
```

### **Business Logic:**
1. Verifies the answer sheet belongs to the authenticated student
2. Checks if evaluation is complete
3. Checks if teacher is assigned
4. If review exists → **Updates** the existing review
5. If review doesn't exist → **Creates** new review

---

## 📝 **How It Works**

### **Flow Diagram:**

```
Student completes written exam
    ↓
Teacher evaluates the exam
    ↓
Student receives evaluated exam
    ↓
Student calls API:
POST /api/answer/submit-review
{
  "written_answer_id": 123,
  "rating": 5,
  "comment": "Great evaluation!"
}
    ↓
System validates:
  ✓ Is student authenticated?
  ✓ Does answer sheet belong to this student?
  ✓ Is evaluation complete?
  ✓ Is teacher assigned?
    ↓
If all checks pass:
  - Creates/Updates review
  - Stores rating (1-5)
  - Stores comment
  - Links to teacher_id
    ↓
Returns success response
    ↓
Review appears in teacher profile
Admin can view in backend
```

---

## 🧪 **Testing Examples**

### **1. Using cURL:**

```bash
curl -X POST "http://localhost:8000/api/answer/submit-review" \
  -H "Authorization: Bearer YOUR_STUDENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "written_answer_id": 123,
    "rating": 5,
    "comment": "Excellent evaluation! Very detailed feedback."
  }'
```

### **2. Using Postman:**

1. **Import Collection:**
   - File: `docs/Exam App Complete API.postman_collection.json`
   - Find: "Submit Review (Student)"

2. **Set Variables:**
   - `{{base_url}}` = `http://localhost:8000/api/`
   - `{{token}}` = Your student token

3. **Update Body:**
   ```
   written_answer_id: 123
   rating: 5
   comment: "Great evaluation!"
   ```

4. **Send Request**

### **3. Using JavaScript (Fetch):**

```javascript
async function submitReview(writtenAnswerId, rating, comment) {
  const token = localStorage.getItem('student_token');
  
  const response = await fetch('http://localhost:8000/api/answer/submit-review', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      written_answer_id: writtenAnswerId,
      rating: rating,
      comment: comment
    })
  });
  
  const data = await response.json();
  
  if (data.status) {
    console.log('Review submitted:', data.data);
    alert('Review submitted successfully!');
  } else {
    console.error('Error:', data.message);
    alert('Error: ' + data.message);
  }
}

// Usage:
submitReview(123, 5, "Excellent evaluation!");
```

### **4. Using Axios:**

```javascript
import axios from 'axios';

async function submitReview(writtenAnswerId, rating, comment) {
  try {
    const response = await axios.post(
      'http://localhost:8000/api/answer/submit-review',
      {
        written_answer_id: writtenAnswerId,
        rating: rating,
        comment: comment
      },
      {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('student_token')}`
        }
      }
    );
    
    if (response.data.status) {
      console.log('Review submitted:', response.data.data);
      return response.data;
    }
  } catch (error) {
    console.error('Error:', error.response.data);
    throw error;
  }
}
```

---

## 📱 **Frontend Implementation Example**

### **Review Form Component:**

```html
<div class="review-form">
  <h4>Rate Teacher's Evaluation</h4>
  
  <!-- Star Rating -->
  <div class="star-rating">
    <input type="radio" id="star5" name="rating" value="5">
    <label for="star5">⭐</label>
    
    <input type="radio" id="star4" name="rating" value="4">
    <label for="star4">⭐</label>
    
    <input type="radio" id="star3" name="rating" value="3">
    <label for="star3">⭐</label>
    
    <input type="radio" id="star2" name="rating" value="2">
    <label for="star2">⭐</label>
    
    <input type="radio" id="star1" name="rating" value="1">
    <label for="star1">⭐</label>
  </div>
  
  <!-- Comment Box -->
  <textarea 
    id="review-comment" 
    placeholder="Write your review... (max 1000 characters)"
    maxlength="1000"
    rows="4"
  ></textarea>
  
  <!-- Submit Button -->
  <button onclick="submitReview()">Submit Review</button>
</div>

<script>
async function submitReview() {
  const rating = document.querySelector('input[name="rating"]:checked')?.value;
  const comment = document.getElementById('review-comment').value;
  const writtenAnswerId = 123; // Get from context
  
  if (!rating) {
    alert('Please select a rating');
    return;
  }
  
  if (!comment.trim()) {
    alert('Please write a comment');
    return;
  }
  
  const token = localStorage.getItem('student_token');
  
  const response = await fetch('/api/answer/submit-review', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      written_answer_id: writtenAnswerId,
      rating: parseInt(rating),
      comment: comment
    })
  });
  
  const data = await response.json();
  
  if (data.status) {
    alert('Review submitted successfully!');
    // Redirect or update UI
  } else {
    alert('Error: ' + data.message);
  }
}
</script>
```

---

## 🔄 **Related APIs**

### **1. Add Conversation Message:**
After submitting review, student can add more messages:

```
POST /api/answer/add-conversation
{
  "review_id": 1,
  "message": "I have a follow-up question..."
}
```

### **2. Get Conversation:**
View full conversation thread:

```
POST /api/answer/get-conversation
{
  "review_id": 1
}
```

### **3. View Answer with Review:**
The review is included in the answer details:

```
POST /api/answer/show-preliminary-answer
{
  "written_id": 1
}

Response includes:
{
  "answer": {
    "review": {
      "rating": 5,
      "comment": "...",
      "conversations": [...]
    }
  }
}
```

---

## 📊 **Key Features**

### ✅ **What's Supported:**
- Create new review
- Update existing review
- Rating from 1-5 stars
- Comment up to 1000 characters
- Auto-links to teacher
- Conversation thread support

### ❌ **What's NOT Supported:**
- Delete review (only update)
- Review without comment
- Review before evaluation complete
- Multiple reviews per answer sheet (only one, can update)

---

## 🎯 **Use Cases**

### **1. Student Reviews Evaluation:**
```
Student → Submits review after receiving evaluated exam
System → Creates review with rating + comment
Teacher → Sees review in profile
Admin → Can view and reply in backend
```

### **2. Student Updates Review:**
```
Student → Changes mind, submits again with same written_answer_id
System → Updates existing review (not create duplicate)
Teacher → Sees updated rating and comment
```

### **3. Student Continues Conversation:**
```
Student → Submits initial review
Teacher → Replies via API
Student → Uses /add-conversation to respond
Teacher → Replies again
(Conversation continues...)
```

---

## 📞 **Where to Find Documentation**

### **1. Postman Collection:**
```
File: docs/Exam App Complete API.postman_collection.json
Endpoint: "Submit Review (Student)"
Location: Answer Management folder
```

### **2. API Documentation:**
```
File: docs/REVIEW_CONVERSATION_SYSTEM.md
Section: "Submit Review (Student)"
```

### **3. Source Code:**
```
Controller: app/Http/Controllers/Api/AnswerController.php
Method: submitReview()
Route: routes/api.php (line 271)
```

---

## ✅ **Status Summary**

| Feature | Status |
|---------|--------|
| API Endpoint | ✅ Implemented |
| Route Registered | ✅ Yes |
| Authentication | ✅ Required (Sanctum) |
| Validation | ✅ Complete |
| Error Handling | ✅ Comprehensive |
| Documentation | ✅ Available |
| Postman Collection | ✅ Updated |
| Tested | ✅ Working |

---

## 🚀 **Quick Start**

### **For Frontend Developers:**

1. **Get Student Token:**
   ```
   POST /api/login
   Save token from response
   ```

2. **Wait for Teacher Evaluation:**
   ```
   Check: answer.is_checked == 1
   ```

3. **Submit Review:**
   ```javascript
   POST /api/answer/submit-review
   {
     "written_answer_id": answer.id,
     "rating": 5,
     "comment": "Great!"
   }
   ```

4. **Done!** ✅

---

## 📝 **Important Notes**

1. ✅ **API Already Exists** - No need to create new endpoint
2. ✅ **Students can UPDATE** - Submitting again updates the existing review
3. ✅ **Only for Written Exams** - MCQ exams are auto-graded (no teacher to review)
4. ✅ **Must be Evaluated First** - Cannot review before teacher evaluation
5. ✅ **One Review per Answer** - Can update, but only one review per `written_answer_id`

---

**Last Updated:** October 16, 2025  
**API Status:** ✅ **FULLY FUNCTIONAL & PRODUCTION READY**

