# 📦 Upcoming Packages API - Complete Guide

**Date:** October 16, 2025  
**Status:** ✅ Newly Implemented

---

## ✅ **New API Endpoint Created**

A new endpoint has been added to fetch upcoming/scheduled packages that are not yet available for purchase.

---

## 🚀 **API Endpoint**

### **Get Upcoming Packages**

**Endpoint:** `POST /api/upcoming-packages`

**Authentication:** ❌ Not required (public)

**Method:** POST

**Purpose:** Retrieve all packages scheduled for future release or in draft status.

---

## 📋 **Request Details**

### **URL:**
```
POST {{base_url}}/upcoming-packages
```

### **Headers:**
```
Content-Type: application/json
```

### **Body:**
```
No parameters required
```

---

## 📤 **Response**

### **Success Response:**
```json
{
  "status": true,
  "message": "Upcoming packages retrieved successfully",
  "data": {
    "course_base": [
      {
        "id": 5,
        "name": "Premium BCS Course 2026",
        "details": "Complete BCS preparation package launching January 2026",
        "permission": ["bcs_preliminary", "bcs_written", "materials"],
        "amount": 5000,
        "validity": 365,
        "status": 0,
        "image": "images/packages/package5.png",
        "banner_image": "images/banners/bcs2026.jpg",
        "published_at": "2026-01-01",
        "discount_amount": 500,
        "enroll_students_count": 0,
        "created_at": "2025-10-16T10:00:00Z",
        "updated_at": "2025-10-16T10:00:00Z"
      }
    ],
    "exam_base": {
      "current_page": 1,
      "data": [
        {
          "id": 12,
          "name": "Bank Job Special Package 2026",
          "details": "20 bank job mock exams - Launching December 2025",
          "permission": ["bank_preliminary", "bank_written"],
          "amount": 2000,
          "validity": 180,
          "status": 0,
          "image": "images/packages/bank2026.png",
          "banner_image": "images/banners/bank.jpg",
          "published_at": "2025-12-01",
          "discount_amount": 200,
          "enroll_students_count": 0
        }
      ],
      "total": 1,
      "per_page": 15,
      "last_page": 1
    }
  }
}
```

---

## 🔍 **Filtering Logic**

### **What Qualifies as "Upcoming":**

Packages are considered upcoming if they meet ANY of these criteria:

1. **Scheduled for Future:**
   ```sql
   published_at > NOW()
   ```
   - Example: Package with `published_at = "2026-01-01"`

2. **Draft Status:**
   ```sql
   published_at IS NULL AND status = 0
   ```
   - Example: Package being prepared but not yet scheduled

### **Full Query Logic:**
```php
WHERE (published_at > NOW())
   OR (published_at IS NULL AND status = 0)
ORDER BY published_at ASC
```

---

## 📊 **Package Types**

| Type | Value | Description | Example |
|------|-------|-------------|---------|
| **Course-Based** | `type = 1` | Unlimited access packages | Full BCS course access |
| **Exam-Based** | `type = 2` | Limited exam packages | 20 mock exams bundle |

Both types are returned separately in the response:
- `course_base` → Array of upcoming course packages
- `exam_base` → Paginated upcoming exam packages

---

## 🎯 **Use Cases**

### **1. "Coming Soon" Section**
```javascript
// Display upcoming packages on homepage
const upcomingPackages = await fetch('/api/upcoming-packages');
showComingSoonSection(upcomingPackages.data);
```

### **2. Launch Calendar**
```javascript
// Show scheduled launches ordered by date
upcomingPackages.data.course_base.forEach(pkg => {
  console.log(`${pkg.name} - Launching ${pkg.published_at}`);
});
```

### **3. Pre-Registration**
```javascript
// Allow users to register interest in upcoming packages
if (pkg.published_at > today) {
  showNotifyMeButton(pkg.id);
}
```

### **4. Countdown Timer**
```javascript
// Show countdown to launch date
const launchDate = new Date(pkg.published_at);
const daysUntilLaunch = getDaysDifference(today, launchDate);
showCountdown(daysUntilLaunch);
```

---

## 🔄 **Difference from Regular Packages API**

| Feature | `/api/packages` | `/api/upcoming-packages` |
|---------|----------------|-------------------------|
| **Purpose** | Currently available packages | Future/scheduled packages |
| **Status Filter** | `status = 1` (active only) | `status = 0` or scheduled |
| **Published Date** | `published_at <= NOW()` or NULL | `published_at > NOW()` or NULL+inactive |
| **Purchasable** | ✅ Yes | ❌ No (not yet available) |
| **Ordering** | Latest first | Earliest (by publish date) first |
| **Use Case** | Browse & buy packages | Preview future offerings |

---

## 📱 **Frontend Implementation**

### **Example 1: Fetch Upcoming Packages**
```javascript
async function getUpcomingPackages() {
  const response = await fetch('http://localhost:8000/api/upcoming-packages', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    }
  });
  
  const data = await response.json();
  
  if (data.status) {
    console.log('Upcoming Course Packages:', data.data.course_base);
    console.log('Upcoming Exam Packages:', data.data.exam_base.data);
  }
}
```

### **Example 2: Display "Coming Soon" Badge**
```javascript
function renderUpcomingPackage(pkg) {
  const launchDate = new Date(pkg.published_at);
  const today = new Date();
  
  const isScheduled = pkg.published_at && launchDate > today;
  const isDraft = !pkg.published_at && pkg.status === 0;
  
  return `
    <div class="package-card">
      ${pkg.banner_image ? `<img src="${pkg.banner_image}">` : ''}
      
      <div class="badge coming-soon">
        ${isScheduled ? `🔔 Coming ${formatDate(launchDate)}` : '🚧 Coming Soon'}
      </div>
      
      <h3>${pkg.name}</h3>
      <p>${pkg.details}</p>
      
      <div class="price">
        <span class="original">৳${pkg.amount}</span>
        ${pkg.discount_amount > 0 ? 
          `<span class="discounted">৳${pkg.amount - pkg.discount_amount}</span>` : ''}
        <span class="discount-badge">Launch Discount: ৳${pkg.discount_amount}</span>
      </div>
      
      <div class="package-info">
        <span>⏱️ Validity: ${pkg.validity} days</span>
      </div>
      
      ${isScheduled ? `
        <div class="countdown">
          <strong>Launching in:</strong>
          <span id="countdown-${pkg.id}"></span>
        </div>
      ` : ''}
      
      <button onclick="notifyMe(${pkg.id})" class="btn-notify">
        🔔 Notify Me When Available
      </button>
    </div>
  `;
}
```

### **Example 3: Countdown Timer**
```javascript
function startCountdown(packageId, launchDate) {
  const countdownElement = document.getElementById(`countdown-${packageId}`);
  
  setInterval(() => {
    const now = new Date().getTime();
    const distance = new Date(launchDate).getTime() - now;
    
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    
    countdownElement.innerHTML = `${days}d ${hours}h ${minutes}m`;
    
    if (distance < 0) {
      countdownElement.innerHTML = "Now Available!";
      location.reload(); // Refresh to show in available packages
    }
  }, 1000);
}
```

---

## 🧪 **Testing Examples**

### **Using cURL:**
```bash
curl -X POST "http://localhost:8000/api/upcoming-packages" \
  -H "Content-Type: application/json"
```

### **Using Postman:**
1. Import: `docs/Exam App Complete API.postman_collection.json`
2. Find: "Packages & Subscription" → "Get Upcoming Packages"
3. Click "Send"

### **Using JavaScript (Fetch):**
```javascript
fetch('http://localhost:8000/api/upcoming-packages', {
  method: 'POST'
})
.then(response => response.json())
.then(data => {
  console.log('Upcoming packages:', data);
});
```

### **Using Axios:**
```javascript
import axios from 'axios';

axios.post('http://localhost:8000/api/upcoming-packages')
  .then(response => {
    console.log('Course packages:', response.data.data.course_base);
    console.log('Exam packages:', response.data.data.exam_base);
  });
```

---

## 📊 **Response Fields Explained**

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `id` | Integer | Package ID | 5 |
| `name` | String | Package name | "Premium BCS Course 2026" |
| `details` | String | Description | "Complete preparation..." |
| `permission` | Array | Features included | `["bcs_preliminary", "materials"]` |
| `amount` | Integer | Price in BDT | 5000 |
| `validity` | Integer | Duration in days | 365 |
| `status` | Integer | 0 = Inactive, 1 = Active | 0 |
| `image` | String | Package icon | "images/packages/pkg.png" |
| `banner_image` | String | Marketing banner | "images/banners/bcs.jpg" |
| `published_at` | Date | Launch date | "2026-01-01" |
| `discount_amount` | Integer | Discount in BDT | 500 |
| `enroll_students_count` | Integer | Pre-registrations | 0 |

---

## 🎨 **UI/UX Recommendations**

### **1. Coming Soon Badge**
```html
<span class="badge badge-warning">
  🚀 Launching Jan 1, 2026
</span>
```

### **2. Countdown Display**
```html
<div class="countdown-box">
  <h4>Available In:</h4>
  <div class="countdown-timer">
    <span class="time-unit">
      <strong>30</strong>
      <small>Days</small>
    </span>
    <span class="time-unit">
      <strong>12</strong>
      <small>Hours</small>
    </span>
    <span class="time-unit">
      <strong>45</strong>
      <small>Minutes</small>
    </span>
  </div>
</div>
```

### **3. Pre-Registration Button**
```html
<button class="btn-outline-primary">
  🔔 Notify Me at Launch
</button>
```

### **4. Launch Discount Highlight**
```html
<div class="discount-banner">
  <span class="discount-label">Launch Offer</span>
  <span class="discount-value">৳500 OFF</span>
  <span class="discount-price">৳4500 only</span>
</div>
```

---

## 🔐 **Security & Access**

### **Public Access:**
- ✅ No authentication required
- ✅ Anyone can view upcoming packages
- ✅ Read-only endpoint

### **Cannot:**
- ❌ Purchase upcoming packages (not yet available)
- ❌ Modify package details
- ❌ Delete packages

---

## 📈 **Business Benefits**

### **1. Marketing:**
- Build anticipation for new launches
- Create buzz before release
- Show roadmap to users

### **2. Pre-Sales:**
- Collect interest (notify me feature)
- Gauge demand
- Plan inventory

### **3. User Engagement:**
- Keep users informed
- Reduce churn (users know what's coming)
- Build loyalty

### **4. Transparency:**
- Show development pipeline
- Manage expectations
- Build trust

---

## 🔄 **Related Endpoints**

| Endpoint | Purpose | Relation |
|----------|---------|----------|
| `POST /api/packages` | Get available packages | Shows currently purchasable |
| `POST /api/upcoming-packages` | Get upcoming packages | Shows future releases |
| `POST /api/purchase-package` | Buy a package | Only works with available packages |
| `POST /api/package-history` | User's purchases | Shows past transactions |

---

## ✅ **Summary**

### **What's New:**
- ✅ New endpoint: `/api/upcoming-packages`
- ✅ Filters upcoming/scheduled packages
- ✅ Updated existing `/api/packages` to exclude upcoming
- ✅ Postman collection updated
- ✅ Documentation complete

### **Key Features:**
- 📅 Shows packages scheduled for future launch
- 🚧 Includes draft packages (in preparation)
- 📊 Separates course-based and exam-based
- 🔄 Orders by launch date (earliest first)
- 🎯 Perfect for "Coming Soon" sections

### **Use Cases:**
- "Coming Soon" homepage section
- Launch calendar
- Pre-registration/notify me
- Countdown timers
- Marketing campaigns

---

## 📞 **Quick Reference**

### **Endpoint:**
```
POST /api/upcoming-packages
```

### **Response:**
```javascript
{
  status: true,
  message: "Upcoming packages retrieved successfully",
  data: {
    course_base: [...],  // Array
    exam_base: {         // Paginated
      data: [...],
      total: N
    }
  }
}
```

### **Filtering:**
```
WHERE (published_at > NOW())
   OR (published_at IS NULL AND status = 0)
```

---

**Last Updated:** October 16, 2025  
**Status:** ✅ **Production Ready**  
**Documentation:** Complete

