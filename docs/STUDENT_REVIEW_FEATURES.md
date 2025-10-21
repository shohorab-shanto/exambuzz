# 📱 Student Review Features - Complete Guide

**Date:** October 16, 2025  
**Status:** ✅ All Features Fully Implemented

---

## ✅ **YES! Students Can Do Everything**

Students have **full access** to:
1. ✅ **Submit** initial review
2. ✅ **View** their review and all replies (teacher + admin)
3. ✅ **Edit/Update** their review rating and comment
4. ✅ **Add more messages** to continue the conversation
5. ✅ **View full conversation thread** at any time

---

## 🎯 **Complete Feature Overview**

| Feature | API Endpoint | Status |
|---------|--------------|--------|
| Submit Review | `POST /api/answer/submit-review` | ✅ Working |
| Update Review | `POST /api/answer/submit-review` (same endpoint) | ✅ Working |
| View Review & Replies | `POST /api/answer/show-preliminary-answer` | ✅ Working |
| Add Message | `POST /api/answer/add-conversation` | ✅ Working |
| Get Conversation | `POST /api/answer/get-conversation` | ✅ Working |

---

## 📋 **1. Submit Initial Review**

### **Endpoint:**
```
POST /api/answer/submit-review
```

### **Request:**
```json
{
  "written_answer_id": 123,
  "rating": 5,
  "comment": "Excellent evaluation! Very detailed feedback."
}
```

### **Response:**
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
    "comment": "Excellent evaluation! Very detailed feedback.",
    "created_at": "2025-10-16T17:50:00Z"
  }
}
```

---

## ✏️ **2. Edit/Update Review** ⭐ NEW CLARIFICATION

### **How to Update:**
Students can update their review by **calling the same endpoint again** with the same `written_answer_id`.

### **Endpoint:**
```
POST /api/answer/submit-review
```

### **Request (Updated Rating & Comment):**
```json
{
  "written_answer_id": 123,
  "rating": 4,
  "comment": "Good evaluation, but I think question #5 was marked incorrectly."
}
```

### **Response:**
```json
{
  "status": true,
  "message": "Review updated successfully",  // ← Notice: "updated"
  "data": {
    "id": 1,  // ← Same review ID (not new)
    "written_answer_id": 123,
    "user_id": 1909,
    "teacher_id": 5,
    "rating": 4,  // ← Updated rating
    "comment": "Good evaluation, but I think question #5 was marked incorrectly.",  // ← Updated comment
    "updated_at": "2025-10-16T18:30:00Z"  // ← New timestamp
  }
}
```

### **Key Points:**
- ✅ Same endpoint as submit (`submit-review`)
- ✅ Automatically detects existing review
- ✅ Updates rating and comment
- ✅ Same review ID (not a duplicate)
- ✅ Message says "Review updated successfully"

---

## 👀 **3. View Review & All Replies**

### **Method 1: View Answer with Review** (Recommended)

**Endpoint:**
```
POST /api/answer/show-preliminary-answer
```

**Request:**
```json
{
  "written_id": 1
}
```

**Response (Includes Review & Conversations):**
```json
{
  "status": true,
  "message": "ok",
  "data": {
    "answer": {
      "id": 123,
      "obtained_mark": 75.00,
      "is_checked": 1,
      "teacher": {
        "id": 5,
        "name": "Mr. Rahman",
        "phone": "01700000000"
      },
      "review": {
        "id": 1,
        "rating": 5,
        "comment": "Excellent evaluation!",
        "created_at": "2025-10-16T17:50:00Z",
        "user": {
          "id": 1909,
          "name": "John Doe"
        },
        "teacher": {
          "id": 5,
          "name": "Mr. Rahman"
        },
        "conversations": [
          {
            "id": 1,
            "user_type": "teacher",
            "message": "Thank you for your feedback!",
            "created_at": "2025-10-16T18:00:00Z",
            "user": {
              "id": 5,
              "name": "Mr. Rahman"
            }
          },
          {
            "id": 2,
            "user_type": "student",
            "message": "I have a question about question #5...",
            "created_at": "2025-10-16T18:30:00Z",
            "user": {
              "id": 1909,
              "name": "John Doe"
            }
          },
          {
            "id": 3,
            "user_type": "admin",
            "message": "This has been reviewed and verified.",
            "created_at": "2025-10-16T19:00:00Z",
            "user": {
              "id": 1,
              "name": "Admin"
            }
          }
        ]
      },
      "questions": [...]
    }
  }
}
```

### **Method 2: Get Conversation Only**

**Endpoint:**
```
POST /api/answer/get-conversation
```

**Request:**
```json
{
  "review_id": 1
}
```

**Response:**
```json
{
  "status": true,
  "message": "Conversation retrieved successfully",
  "data": {
    "id": 1,
    "rating": 5,
    "comment": "Excellent evaluation!",
    "user": {
      "id": 1909,
      "name": "John Doe"
    },
    "teacher": {
      "id": 5,
      "name": "Mr. Rahman"
    },
    "conversations": [
      {
        "id": 1,
        "user_type": "teacher",
        "message": "Thank you!",
        "created_at": "2025-10-16T18:00:00Z",
        "user": {"name": "Mr. Rahman"}
      },
      {
        "id": 2,
        "user_type": "student",
        "message": "I have a question...",
        "created_at": "2025-10-16T18:30:00Z",
        "user": {"name": "John Doe"}
      }
    ]
  }
}
```

---

## 💬 **4. Add More Messages to Conversation**

Students can continue the conversation by adding more messages.

### **Endpoint:**
```
POST /api/answer/add-conversation
```

### **Request:**
```json
{
  "review_id": 1,
  "message": "Thank you for your reply! I have a follow-up question about question #5..."
}
```

### **Response:**
```json
{
  "status": true,
  "message": "Message posted successfully",
  "data": {
    "id": 3,
    "review_id": 1,
    "user_id": 1909,
    "user_type": "student",
    "message": "Thank you for your reply! I have a follow-up question...",
    "created_at": "2025-10-16T18:45:00Z",
    "user": {
      "id": 1909,
      "name": "John Doe"
    }
  }
}
```

### **Security:**
- ✅ Students can only add messages to **their own reviews**
- ✅ Auto-detects user type as "student"
- ✅ Message limit: 2000 characters

---

## 🔄 **Complete Workflow Example**

### **Scenario: Student Reviews, Updates, and Discusses**

```
┌─────────────────────────────────────────────────────────┐
│ STEP 1: Student Submits Initial Review                  │
├─────────────────────────────────────────────────────────┤
│ POST /api/answer/submit-review                          │
│ {                                                        │
│   "written_answer_id": 123,                             │
│   "rating": 5,                                          │
│   "comment": "Great evaluation!"                        │
│ }                                                        │
│ Response: "Review submitted successfully"               │
└─────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────┐
│ STEP 2: Teacher Replies (via API)                       │
├─────────────────────────────────────────────────────────┤
│ Teacher uses: POST /api/answer/add-conversation         │
│ Message: "Thank you for your feedback!"                 │
└─────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────┐
│ STEP 3: Student Views Answer & Sees Teacher Reply       │
├─────────────────────────────────────────────────────────┤
│ POST /api/answer/show-preliminary-answer                │
│ {                                                        │
│   "written_id": 1                                       │
│ }                                                        │
│                                                          │
│ Response includes:                                       │
│ - answer.review.rating: 5                               │
│ - answer.review.comment: "Great evaluation!"            │
│ - answer.review.conversations[0]:                       │
│     user_type: "teacher"                                │
│     message: "Thank you for your feedback!"             │
└─────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────┐
│ STEP 4: Student Realizes Mistake, Updates Review        │
├─────────────────────────────────────────────────────────┤
│ POST /api/answer/submit-review (SAME ENDPOINT!)         │
│ {                                                        │
│   "written_answer_id": 123,  ← Same ID                  │
│   "rating": 4,  ← Changed rating                        │
│   "comment": "Good, but question #5 seems wrong"        │
│ }                                                        │
│ Response: "Review updated successfully"                 │
└─────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────┐
│ STEP 5: Student Adds More Message to Conversation       │
├─────────────────────────────────────────────────────────┤
│ POST /api/answer/add-conversation                       │
│ {                                                        │
│   "review_id": 1,                                       │
│   "message": "Can you explain question #5 marking?"     │
│ }                                                        │
│ Response: "Message posted successfully"                 │
└─────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────┐
│ STEP 6: Teacher Explains                                │
├─────────────────────────────────────────────────────────┤
│ Teacher replies via API                                 │
│ Message: "Let me explain the marking criteria..."       │
└─────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────┐
│ STEP 7: Student Views Full Conversation Again           │
├─────────────────────────────────────────────────────────┤
│ POST /api/answer/show-preliminary-answer                │
│                                                          │
│ Now sees:                                                │
│ - Updated rating: 4 (was 5)                             │
│ - Updated comment: "Good, but..."                       │
│ - Conversation with 4 messages:                         │
│   1. Teacher: "Thank you!"                              │
│   2. Student: "Can you explain..."                      │
│   3. Teacher: "Let me explain..."                       │
│   4. Admin: "Verified" (if admin joined)                │
└─────────────────────────────────────────────────────────┘
```

**Conversation continues indefinitely!**

---

## 📱 **Frontend Implementation Example**

### **1. Display Review Section:**

```html
<div class="review-section">
  <!-- If review exists -->
  <div class="student-review">
    <h4>Your Review</h4>
    <div class="rating">
      ⭐⭐⭐⭐⭐ {{ review.rating }}/5
    </div>
    <p>{{ review.comment }}</p>
    <button onclick="showEditForm()">Edit Review</button>
  </div>

  <!-- Edit Form (hidden by default) -->
  <div id="edit-review-form" style="display: none;">
    <h5>Edit Your Review</h5>
    <select id="new-rating">
      <option value="5">⭐⭐⭐⭐⭐ 5 stars</option>
      <option value="4">⭐⭐⭐⭐ 4 stars</option>
      <option value="3">⭐⭐⭐ 3 stars</option>
      <option value="2">⭐⭐ 2 stars</option>
      <option value="1">⭐ 1 star</option>
    </select>
    <textarea id="new-comment" maxlength="1000">{{ review.comment }}</textarea>
    <button onclick="updateReview()">Update Review</button>
    <button onclick="cancelEdit()">Cancel</button>
  </div>

  <!-- Conversation Thread -->
  <div class="conversation-thread">
    <h4>Conversation</h4>
    <div v-for="message in review.conversations" :class="message.user_type + '-message'">
      <span class="badge">{{ message.user_type }}</span>
      <strong>{{ message.user.name }}</strong>
      <span class="time">{{ message.created_at }}</span>
      <p>{{ message.message }}</p>
    </div>

    <!-- Add Message Form -->
    <div class="add-message">
      <textarea id="new-message" placeholder="Type your message..." maxlength="2000"></textarea>
      <button onclick="addMessage()">Send Message</button>
    </div>
  </div>
</div>
```

### **2. JavaScript Functions:**

```javascript
// Update existing review
async function updateReview() {
  const rating = document.getElementById('new-rating').value;
  const comment = document.getElementById('new-comment').value;
  
  const response = await fetch('/api/answer/submit-review', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      written_answer_id: answerSheet.id,  // Same ID as original
      rating: parseInt(rating),
      comment: comment
    })
  });
  
  const data = await response.json();
  
  if (data.status && data.message === 'Review updated successfully') {
    alert('Review updated successfully!');
    location.reload(); // Refresh to show updated review
  }
}

// Add message to conversation
async function addMessage() {
  const message = document.getElementById('new-message').value;
  
  if (!message.trim()) {
    alert('Please type a message');
    return;
  }
  
  const response = await fetch('/api/answer/add-conversation', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      review_id: review.id,
      message: message
    })
  });
  
  const data = await response.json();
  
  if (data.status) {
    alert('Message sent!');
    document.getElementById('new-message').value = '';
    loadConversation(); // Refresh conversation
  }
}

// Load full conversation
async function loadConversation() {
  const response = await fetch('/api/answer/get-conversation', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      review_id: review.id
    })
  });
  
  const data = await response.json();
  
  if (data.status) {
    renderConversation(data.data.conversations);
  }
}
```

---

## 🔐 **Security & Permissions**

| Action | Permission Check | Result |
|--------|-----------------|--------|
| Submit review | Student owns answer sheet | ✅ Allowed |
| Update review | Same as submit (same endpoint) | ✅ Allowed |
| View review | Student owns answer sheet | ✅ Allowed |
| Add message | Student owns the review | ✅ Allowed |
| View conversation | Student owns the review | ✅ Allowed |

### **What Students CANNOT Do:**
- ❌ View other students' reviews
- ❌ Comment on other students' reviews
- ❌ Delete reviews (only update)
- ❌ Delete messages

---

## 📊 **Key Differences: Submit vs Update**

| Aspect | First Time (Submit) | Second Time (Update) |
|--------|---------------------|----------------------|
| Endpoint | `POST /api/answer/submit-review` | `POST /api/answer/submit-review` (same) |
| written_answer_id | 123 | 123 (same) |
| Response message | "Review submitted successfully" | "Review **updated** successfully" |
| Review ID | Creates new ID (e.g., 1) | Same ID (1) |
| Database action | INSERT | UPDATE |
| Old rating | N/A | Replaced |
| Old comment | N/A | Replaced |

---

## ✅ **Summary Table**

| Feature | Can Student Do This? | API Endpoint | Notes |
|---------|---------------------|--------------|-------|
| Submit initial review | ✅ YES | `POST /api/answer/submit-review` | Required fields: written_answer_id, rating, comment |
| Edit/Update review | ✅ YES | `POST /api/answer/submit-review` | Same endpoint! Automatically updates if exists |
| View own review | ✅ YES | `POST /api/answer/show-preliminary-answer` | Includes review in response |
| View teacher replies | ✅ YES | `POST /api/answer/show-preliminary-answer` | Includes conversations array |
| View admin replies | ✅ YES | `POST /api/answer/show-preliminary-answer` | Includes all user types |
| Add more messages | ✅ YES | `POST /api/answer/add-conversation` | Max 2000 chars per message |
| Get conversation only | ✅ YES | `POST /api/answer/get-conversation` | Returns review with full thread |
| Delete review | ❌ NO | N/A | Can only update, not delete |
| Delete messages | ❌ NO | N/A | Messages are permanent |

---

## 🚀 **Quick Reference**

### **To Update Review:**
```bash
# Just call submit-review again with same written_answer_id
curl -X POST "http://localhost:8000/api/answer/submit-review" \
  -H "Authorization: Bearer $TOKEN" \
  -d "written_answer_id=123&rating=4&comment=Updated comment"
```

### **To Add Message:**
```bash
curl -X POST "http://localhost:8000/api/answer/add-conversation" \
  -H "Authorization: Bearer $TOKEN" \
  -d "review_id=1&message=I have a question..."
```

### **To View Everything:**
```bash
curl -X POST "http://localhost:8000/api/answer/show-preliminary-answer" \
  -H "Authorization: Bearer $TOKEN" \
  -d "written_id=1"
```

---

## ✅ **Final Answer**

### **Question 1: Can students view reviews and replies?**
✅ **YES!** Via `show-preliminary-answer` API
- Shows student's review
- Shows all teacher replies
- Shows all admin replies
- Shows full conversation thread

### **Question 2: Can students edit/update reviews?**
✅ **YES!** Via same `submit-review` API
- Call the same endpoint again
- Use the same `written_answer_id`
- System automatically updates (not creates duplicate)
- Message says "Review **updated** successfully"

### **Question 3: Can students continue conversation?**
✅ **YES!** Via `add-conversation` API
- Can add unlimited messages
- Can respond to teacher/admin replies
- Ongoing conversation thread

---

**All features are implemented and ready to use!** 🚀

**Last Updated:** October 16, 2025  
**Status:** ✅ Fully Functional

