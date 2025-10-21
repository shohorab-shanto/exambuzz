# 📋 Postman Collection Updates - Review System

**Date:** October 16, 2025  
**File:** `docs/Exam App Complete API.postman_collection.json`

---

## ✅ **Updates Made**

The Postman collection has been updated with comprehensive documentation showing:
1. ✅ Students can **view** reviews and all replies
2. ✅ Students can **edit/update** reviews using the same endpoint
3. ✅ Students can **add messages** to continue conversations
4. ✅ Complete conversation thread examples

---

## 📝 **1. Submit Review Endpoint** (Enhanced)

**Endpoint:** `POST /api/answer/submit-review`

### **Updated Description:**
Now clearly documents that this endpoint serves **TWO purposes**:
- Submit NEW review (first time)
- UPDATE existing review (call again with same written_answer_id)

### **Added Information:**
```
**IMPORTANT:** This endpoint serves TWO purposes:
1. **Submit NEW review** - First time submission
2. **UPDATE existing review** - Call again with same written_answer_id

**How Update Works:**
- Use the SAME endpoint (submit-review)
- Use the SAME written_answer_id
- System automatically detects existing review
- Updates rating and comment (doesn't create duplicate)
- Response message changes to "Review updated successfully"

**Security:**
- Student must own the answer sheet
- Answer must be evaluated (is_checked = 1)
- Teacher must be assigned

**Validation:**
- rating: Required, integer, 1-5
- comment: Required, string, max 1000 characters
```

### **New Response Example Added:**
**"Update Review (Same Endpoint)"**

Shows what happens when student updates their review:
```json
{
  "status": true,
  "message": "Review updated successfully",  // ← Notice: "updated"
  "data": {
    "id": 1,  // ← Same review ID
    "rating": 4,  // ← Updated rating
    "comment": "Good evaluation, but I think question #5 was marked incorrectly.",
    "updated_at": "2025-10-16T18:30:00Z"  // ← New timestamp
  }
}
```

---

## 💬 **2. Add Conversation Message** (Enhanced)

**Endpoint:** `POST /api/answer/add-conversation`

### **Updated Description:**
```
Add message to review conversation thread. Creates ongoing conversation 
between student, teacher, and admin.

**Who Can Use:**
- ✅ Students: Can add messages to THEIR OWN reviews
- ✅ Teachers: Can reply to reviews they're associated with
- ✅ Admins: Can reply to any review

**User Type Auto-Detection:**
System automatically detects:
- If user_id == teacher_id → user_type = 'teacher'
- If user.is_admin == 1 → user_type = 'admin'
- Otherwise → user_type = 'student'

**Use Cases:**
- Student responds to teacher's reply
- Student asks follow-up questions
- Teacher explains marking
- Admin mediates disputes

**Validation:**
- review_id: Required, must exist
- message: Required, max 2000 characters

**Note:** Students can add UNLIMITED messages to continue conversation
```

---

## 👁️ **3. Get Conversation** (Enhanced)

**Endpoint:** `POST /api/answer/get-conversation`

### **Updated Description:**
```
Get full conversation thread for a review including all messages 
from student, teacher, and admin.

**Returns:**
- Review details (rating, comment)
- Student information
- Teacher information
- Written answer details
- Complete conversation array with all messages

**Conversation Format:**
Each message includes:
- id: Message ID
- user_type: 'student' | 'teacher' | 'admin'
- message: The conversation text
- created_at: Timestamp
- user: User information (name, phone, etc.)

**Use Cases:**
- Student views full conversation history
- Display chat-style interface
- Load conversation thread

**Alternative:** Can also use `show-preliminary-answer` which includes 
review + conversations in the answer response
```

---

## 📊 **4. Show Preliminary Answer** (Major Update)

**Endpoint:** `POST /api/answer/show-preliminary-answer`

### **Updated Description:**
Now clearly separates MCQ and Written exam responses:

```
Get exam answer summary with statistics.

**For MCQ/Preliminary Exams:**
- positive_count: Total correct answers
- negative_count: Total wrong answers
- empty_count: Total unanswered
- obtained_marks: Final score
- result_status: Pass/Fail
- User's position in merit list
- subject_breakdown: Performance by subject
- topic_breakdown: Performance by topic

**For Written Exams (ADDITIONAL DATA):**
- ✅ **review**: Student's review of teacher evaluation (if submitted)
  - rating: 1-5 stars
  - comment: Review text
  - user: Student info
  - teacher: Teacher info
  - **conversations**: Full conversation thread array
    - Each message includes: user_type, message, created_at, user
    - Shows teacher replies, admin replies, and student messages
- teacher: Teacher who evaluated
- obtained_mark: Marks received
- is_checked: Evaluation status

**Use Cases:**
- Students view their answer summary
- Students see their review and all replies
- Students read full conversation with teacher/admin

**Parameters:**
- exam_id: For MCQ exams
- written_id: For written exams
```

### **New Response Example Added:**
**"Written Exam with Review & Conversations"**

Complete example showing:
```json
{
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
          "message": "Thank you for your feedback!",
          "user": {"name": "Mr. Rahman"}
        },
        {
          "user_type": "student",
          "message": "I have a question...",
          "user": {"name": "John Doe"}
        },
        {
          "user_type": "teacher",
          "message": "Good question! Let me explain...",
          "user": {"name": "Mr. Rahman"}
        },
        {
          "user_type": "admin",
          "message": "Verified by admin.",
          "user": {"name": "Admin"}
        }
      ]
    }
  }
}
```

---

## 📋 **Summary of Changes**

| Endpoint | What Changed | Impact |
|----------|--------------|--------|
| `submit-review` | ✅ Documented UPDATE capability<br>✅ Added "Update Review" response example | Students now know they can update reviews |
| `add-conversation` | ✅ Enhanced description<br>✅ Added user type auto-detection info<br>✅ Clarified students can add unlimited messages | Clear guidance on conversation features |
| `get-conversation` | ✅ Added detailed return format<br>✅ Added use cases<br>✅ Mentioned alternative endpoint | Better understanding of conversation structure |
| `show-preliminary-answer` | ✅ Separated MCQ vs Written exam docs<br>✅ Added "Written Exam with Review" example<br>✅ Documented review + conversations structure | Students know this endpoint returns reviews |

---

## 🎯 **Key Features Now Documented**

### **1. View Reviews & Replies** ✅
Students can view their reviews and all teacher/admin replies via:
- `show-preliminary-answer` (recommended - includes everything)
- `get-conversation` (review-specific)

### **2. Edit/Update Reviews** ✅
Students can update reviews by:
- Calling same `submit-review` endpoint
- Using same `written_answer_id`
- System auto-detects and updates

### **3. Continue Conversations** ✅
Students can add unlimited messages via:
- `add-conversation` endpoint
- Only for their own reviews
- Max 2000 characters per message

---

## 📱 **How to Use Updated Collection**

### **1. Import Collection:**
```
File → Import → Choose file:
docs/Exam App Complete API.postman_collection.json
```

### **2. Find Updated Endpoints:**
```
Collection: Exam App Complete API
Folder: Answer Management
```

### **3. Review Examples:**
- **Submit Review (Student)** - See both submit and update examples
- **Add Conversation Message** - Student continuing conversation
- **Get Review Conversation** - Full thread retrieval
- **Show Preliminary Answer** - Written exam with reviews

---

## 🔍 **Quick Reference**

### **To Submit Initial Review:**
```
POST /api/answer/submit-review
Body: {
  "written_answer_id": 123,
  "rating": 5,
  "comment": "Excellent!"
}
Response: "Review submitted successfully"
```

### **To Update Review:**
```
POST /api/answer/submit-review  // ← Same endpoint!
Body: {
  "written_answer_id": 123,  // ← Same ID!
  "rating": 4,  // ← New rating
  "comment": "Good, but..."  // ← New comment
}
Response: "Review updated successfully"  // ← Notice: "updated"
```

### **To View Review & Replies:**
```
POST /api/answer/show-preliminary-answer
Body: {
  "written_id": 1
}
Response includes: answer.review.conversations[]
```

### **To Add Message:**
```
POST /api/answer/add-conversation
Body: {
  "review_id": 1,
  "message": "I have a follow-up question..."
}
Response: New message with user_type = "student"
```

---

## ✅ **Validation Status**

- ✅ JSON syntax validated
- ✅ All endpoints documented
- ✅ Response examples added
- ✅ Student capabilities clearly shown
- ✅ Update functionality documented
- ✅ Conversation features explained

---

## 📊 **Before vs After**

### **Before:**
- ❌ Unclear if students could update reviews
- ❌ No documentation on viewing replies
- ❌ No examples of conversation threads
- ❌ Written exam review structure not shown

### **After:**
- ✅ Clear documentation: submit endpoint updates existing reviews
- ✅ show-preliminary-answer returns reviews + conversations
- ✅ Complete conversation examples with all user types
- ✅ Written exam response structure fully documented
- ✅ Students know they can continue conversations

---

## 🎉 **Summary**

The Postman collection now **completely documents** all student review capabilities:
1. ✅ Viewing reviews and all replies
2. ✅ Editing/updating reviews
3. ✅ Adding messages to continue conversations
4. ✅ Complete conversation thread examples

**Import the updated collection to see all changes!**

---

**Last Updated:** October 16, 2025  
**Collection Version:** 2.0  
**Status:** ✅ Complete & Ready to Use

