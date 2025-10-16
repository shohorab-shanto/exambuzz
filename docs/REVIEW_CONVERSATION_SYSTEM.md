# 💬 Review Conversation System (Updated)

**Version:** 2.0  
**Type:** Conversation Thread (Not Single Reply)  
**Date:** October 16, 2025

---

## 🎯 Key Changes from Version 1.0

### ❌ OLD System (v1.0):
- Student posts review → Teacher replies once → **END**

### ✅ NEW System (v2.0):
- Student posts review → Teacher/Admin replies → **Student can comment again** → Teacher replies again → **Ongoing conversation thread**

---

## 📚 Important Clarification: MCQ vs Written Exams

### **1. MCQ/Preliminary Exams** (Auto-Graded)
- ❌ **NO teacher evaluation needed**
- ✅ System automatically grades answers
- ✅ Instant results
- ✅ **NO review system** (no manual evaluation to review)

### **2. Written Exams** (Teacher-Graded)
- ✅ **Teacher manually evaluates**
- ✅ Teacher assigns marks
- ✅ **Review system available** (students can review teacher's evaluation)
- ✅ Conversation thread between student and teacher/admin

**Review System** = **Only for Written Exam Evaluations**

---

## 📊 Database Structure

### **Table 1: `written_answer_reviews`** (Main Review)
```sql
- id
- written_answer_id (which answer sheet)
- user_id (student who posted review)
- teacher_id (teacher who was reviewed)
- rating (1-5 stars)
- comment (initial review text)
- teacher_reply (LEGACY - kept for backward compatibility)
- replied_at (LEGACY)
- created_at, updated_at
```

### **Table 2: `written_answer_review_conversations`** ⭐ NEW
```sql
- id
- review_id (links to written_answer_reviews)
- user_id (who posted this message)
- user_type (student/teacher/admin)
- message (conversation text)
- created_at, updated_at
```

---

## 🚀 API Endpoints

### **1. Submit Review (Student)** ✅ UNCHANGED

**Endpoint:** `POST /api/answer/submit-review`

**Who:** Student only

**Request:**
```json
{
  "written_answer_id": 123,
  "rating": 5,
  "comment": "Excellent evaluation! Very detailed feedback."
}
```

**Response:**
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
    "comment": "Excellent evaluation!..."
  }
}
```

---

### **2. Add Conversation Message** ⭐ NEW

**Endpoint:** `POST /api/answer/add-conversation`

**Who:** Student, Teacher, or Admin

**Request:**
```json
{
  "review_id": 1,
  "message": "Thank you! I have a follow-up question..."
}
```

**Response:**
```json
{
  "status": true,
  "message": "Message posted successfully",
  "data": {
    "id": 1,
    "review_id": 1,
    "user_id": 1909,
    "user_type": "student",
    "message": "Thank you! I have a follow-up question...",
    "created_at": "2025-10-16T18:30:00Z",
    "user": {
      "id": 1909,
      "name": "John Doe"
    }
  }
}
```

**User Type Auto-Detection:**
- If `user_id == review.teacher_id` → `user_type = "teacher"`
- If `user.is_admin == 1` → `user_type = "admin"`  
- Otherwise → `user_type = "student"`

---

### **3. Get Full Conversation** ⭐ NEW

**Endpoint:** `POST /api/answer/get-conversation`

**Who:** Anyone involved in the review

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
        "user_type": "teacher",
        "message": "Good question! Let me explain...",
        "created_at": "2025-10-16T19:00:00Z",
        "user": {
          "id": 5,
          "name": "Mr. Rahman"
        }
      },
      {
        "id": 4,
        "user_type": "admin",
        "message": "System note: This evaluation was reviewed by admin.",
        "created_at": "2025-10-16T19:30:00Z",
        "user": {
          "id": 1,
          "name": "Admin"
        }
      }
    ]
  }
}
```

---

### **4. Legacy Reply Endpoint** (Backward Compatible)

**Endpoint:** `POST /api/answer/reply-review`

**Note:** Now redirects to `/add-conversation` internally

**Request:**
```json
{
  "review_id": 1,
  "reply": "Thank you for your feedback!"
}
```

**Response:** Same as `add-conversation`

---

### **5. View Answer with Review & Conversation** ✅ UPDATED

**Endpoint:** `POST /api/answer/show-preliminary-answer`

**Request:**
```json
{
  "written_id": 1
}
```

**Response:**
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
        "name": "Mr. Rahman"
      },
      "review": {
        "id": 1,
        "rating": 5,
        "comment": "Excellent evaluation!",
        "conversations": [
          {
            "user_type": "teacher",
            "message": "Thank you!",
            "created_at": "2025-10-16T18:00:00Z",
            "user": {"name": "Mr. Rahman"}
          },
          {
            "user_type": "student",
            "message": "I have a question...",
            "created_at": "2025-10-16T18:30:00Z",
            "user": {"name": "John Doe"}
          }
        ]
      }
    }
  }
}
```

---

## 💬 Conversation Flow Example

### **Scenario: Student Reviews Teacher's Evaluation**

```
┌─────────────────────────────────────────────────────────┐
│ STEP 1: Student Posts Initial Review                    │
├─────────────────────────────────────────────────────────┤
│ POST /api/answer/submit-review                          │
│ {                                                        │
│   "written_answer_id": 123,                             │
│   "rating": 4,                                          │
│   "comment": "Good evaluation, but I think question     │
│              #5 was marked incorrectly."                 │
│ }                                                        │
└─────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────┐
│ STEP 2: Teacher Replies                                 │
├─────────────────────────────────────────────────────────┤
│ POST /api/answer/add-conversation                       │
│ {                                                        │
│   "review_id": 1,                                       │
│   "message": "Let me review question #5 again.          │
│               The answer key shows option B is correct." │
│ }                                                        │
│ [user_type: teacher]                                    │
└─────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────┐
│ STEP 3: Student Responds                                │
├─────────────────────────────────────────────────────────┤
│ POST /api/answer/add-conversation                       │
│ {                                                        │
│   "review_id": 1,                                       │
│   "message": "I understand now. Thank you for           │
│               clarifying! Could you explain why..."      │
│ }                                                        │
│ [user_type: student]                                    │
└─────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────┐
│ STEP 4: Teacher Explains Further                        │
├─────────────────────────────────────────────────────────┤
│ POST /api/answer/add-conversation                       │
│ {                                                        │
│   "review_id": 1,                                       │
│   "message": "Great question! Here's why..."            │
│ }                                                        │
│ [user_type: teacher]                                    │
└─────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────┐
│ STEP 5: Admin Adds Note (Optional)                      │
├─────────────────────────────────────────────────────────┤
│ POST /api/answer/add-conversation                       │
│ {                                                        │
│   "review_id": 1,                                       │
│   "message": "This discussion has been reviewed by      │
│               admin. Teacher's explanation is correct."  │
│ }                                                        │
│ [user_type: admin]                                      │
└─────────────────────────────────────────────────────────┘
```

**Conversation continues indefinitely!**

---

## 🔒 Security & Permissions

| User Type | Can Do | Cannot Do |
|-----------|--------|-----------|
| **Student** | • Create review<br>• Comment on OWN reviews<br>• View own conversations | • Comment on others' reviews<br>• Delete messages |
| **Teacher** | • Reply to reviews they're associated with<br>• Add messages to those reviews | • Reply to other teachers' reviews<br>• Delete messages |
| **Admin** | • Reply to ANY review<br>• Moderate conversations | • (Depends on admin permissions) |

---

## 📱 Frontend Implementation

### **Chat-Style UI Example**

```html
<div class="review-container">
  <!-- Initial Review -->
  <div class="review-header">
    <div class="rating">⭐⭐⭐⭐⭐ 5/5</div>
    <div class="student-name">John Doe</div>
    <div class="timestamp">Oct 16, 2025 6:00 PM</div>
  </div>
  <div class="review-comment">
    Excellent evaluation! Very detailed feedback.
  </div>

  <!-- Conversation Thread -->
  <div class="conversation-thread">
    <h4>💬 Conversation</h4>
    
    <!-- Teacher Message -->
    <div class="message teacher-message">
      <div class="message-header">
        <span class="badge badge-teacher">👨‍🏫 Teacher</span>
        <span class="name">Mr. Rahman</span>
        <span class="time">6:30 PM</span>
      </div>
      <div class="message-text">
        Thank you for your feedback! I'm glad you found it helpful.
      </div>
    </div>

    <!-- Student Message -->
    <div class="message student-message">
      <div class="message-header">
        <span class="badge badge-student">👨‍🎓 Student</span>
        <span class="name">John Doe</span>
        <span class="time">7:00 PM</span>
      </div>
      <div class="message-text">
        I have a question about question #5...
      </div>
    </div>

    <!-- Teacher Reply -->
    <div class="message teacher-message">
      <div class="message-header">
        <span class="badge badge-teacher">👨‍🏫 Teacher</span>
        <span class="name">Mr. Rahman</span>
        <span class="time">7:30 PM</span>
      </div>
      <div class="message-text">
        Good question! Let me explain...
      </div>
    </div>

    <!-- Admin Note -->
    <div class="message admin-message">
      <div class="message-header">
        <span class="badge badge-admin">👑 Admin</span>
        <span class="name">System Admin</span>
        <span class="time">8:00 PM</span>
      </div>
      <div class="message-text">
        This discussion has been reviewed and verified.
      </div>
    </div>
  </div>

  <!-- Add Message Form -->
  <div class="add-message-form">
    <textarea placeholder="Type your message..." maxlength="2000"></textarea>
    <button onclick="postMessage()">Send</button>
  </div>
</div>
```

---

## 🎨 Frontend JavaScript Example

```javascript
// Post a new message to conversation
async function postMessage(reviewId, message) {
  const response = await fetch('/api/answer/add-conversation', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      review_id: reviewId,
      message: message
    })
  });
  
  const data = await response.json();
  if (data.status) {
    // Add message to UI
    appendMessageToUI(data.data);
  }
}

// Load full conversation
async function loadConversation(reviewId) {
  const response = await fetch('/api/answer/get-conversation', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      review_id: reviewId
    })
  });
  
  const data = await response.json();
  if (data.status) {
    renderConversation(data.data);
  }
}

// Render conversation thread
function renderConversation(review) {
  const container = document.getElementById('conversation');
  container.innerHTML = '';
  
  review.conversations.forEach(msg => {
    const div = document.createElement('div');
    div.className = `message ${msg.user_type}-message`;
    div.innerHTML = `
      <span class="badge">${getUserBadge(msg.user_type)}</span>
      <strong>${msg.user.name}</strong>
      <span class="time">${formatTime(msg.created_at)}</span>
      <p>${msg.message}</p>
    `;
    container.appendChild(div);
  });
}

function getUserBadge(userType) {
  const badges = {
    'student': '👨‍🎓 Student',
    'teacher': '👨‍🏫 Teacher',
    'admin': '👑 Admin'
  };
  return badges[userType] || userType;
}
```

---

## 📊 Key Differences: v1.0 vs v2.0

| Feature | v1.0 (Single Reply) | v2.0 (Conversation Thread) |
|---------|---------------------|----------------------------|
| Student posts review | ✅ | ✅ |
| Teacher replies once | ✅ | ✅ |
| Student comments again | ❌ | ✅ **NEW** |
| Teacher replies again | ❌ | ✅ **NEW** |
| Admin can join | ❌ | ✅ **NEW** |
| Ongoing conversation | ❌ | ✅ **NEW** |
| Message history | ❌ | ✅ **NEW** |
| User type tracking | ❌ | ✅ **NEW** |
| Backward compatible | N/A | ✅ (legacy endpoint works) |

---

## ✅ Summary

### **What's New:**
1. ✅ **Conversation threads** instead of single reply
2. ✅ Student can comment multiple times
3. ✅ Teacher can reply multiple times
4. ✅ Admin can join conversations
5. ✅ Full message history preserved
6. ✅ User type auto-detection (student/teacher/admin)
7. ✅ Backward compatible with v1.0

### **Exam Type Clarification:**
- **MCQ/Preliminary:** Auto-graded, NO review system
- **Written Exam:** Teacher-graded, HAS review system with conversation

### **API Endpoints:**
- `POST /api/answer/submit-review` - Create review
- `POST /api/answer/add-conversation` - Add message (NEW)
- `POST /api/answer/get-conversation` - Get full thread (NEW)
- `POST /api/answer/reply-review` - Legacy (still works)
- `POST /api/answer/show-preliminary-answer` - Includes conversations

---

**Ready for Production!** 🚀

Last Updated: October 16, 2025  
Version: 2.0 (Conversation Thread System)

