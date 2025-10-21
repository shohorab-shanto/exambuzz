# 📦 Package-Wise Archive Filter - Complete Guide

**Date:** October 21, 2025  
**Status:** ✅ Newly Implemented

---

## ✅ **Feature Overview**

Students can now filter their archived exams by package. This allows them to see only the exams that belong to a specific package they purchased.

---

## 🎯 **What's Been Added**

### **1. Database Changes:**
- ✅ Added `package_id` column to `exams` table
- ✅ Added `package_id` column to `writtens` table
- ✅ Both columns are nullable and indexed

### **2. Model Relationships:**
- ✅ `Exam` → `belongsTo(Package)`
- ✅ `Written` → `belongsTo(Package)`
- ✅ `Package` → `hasMany(Exam)`
- ✅ `Package` → `hasMany(Written)`

### **3. API Enhancement:**
- ✅ Archive API now accepts `package_id` parameter
- ✅ Filters work for both Preliminary and Written exams

---

## 🚀 **API Usage**

### **Endpoint:**
```
POST /api/exam/archive
```

### **New Parameter:**
```
package_id (optional, integer)
```

### **Request Example:**
```json
{
  "category": "BCS",
  "subcategory": "Preliminary",
  "childcategory": "",
  "subject_id": "",
  "search": "",
  "package_id": 5  // ← NEW PARAMETER
}
```

---

## 📋 **Complete Filter Options**

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `category` | String | No | Exam category | "BCS", "Bank", "Primary" |
| `subcategory` | String | Yes | Exam type | "Preliminary", "Written" |
| `childcategory` | String | No | Sub-type | "Cadre", "Non-Cadre" |
| `subject_id` | Integer | No | Filter by subject | 1 |
| `search` | String | No | Search exam name | "Mock Test" |
| **`package_id`** | **Integer** | **No** | **Filter by package** | **5** ⭐ NEW |

---

## 💡 **Use Cases**

### **1. "My Package Exams" View**
```javascript
// Show only exams from user's purchased package
const userPackage = getUserCurrentPackage(); // Get active package

fetch('/api/exam/archive', {
  method: 'POST',
  body: JSON.stringify({
    subcategory: 'Preliminary',
    package_id: userPackage.package_id  // Filter by package
  })
});
```

### **2. Package-Specific Archive**
```javascript
// When user clicks "View BCS Package Exams"
showPackageArchive(packageId) {
  fetch('/api/exam/archive', {
    method: 'POST',
    body: JSON.stringify({
      category: 'BCS',
      subcategory: 'Preliminary',
      package_id: packageId
    })
  });
}
```

### **3. Compare Packages**
```javascript
// Show exams from different packages side by side
Promise.all([
  getArchive({ package_id: 1 }),  // Basic Package
  getArchive({ package_id: 5 })   // Premium Package
]).then(([basic, premium]) => {
  console.log('Basic has', basic.length, 'exams');
  console.log('Premium has', premium.length, 'exams');
});
```

---

## 📊 **Response Format**

### **With Package Filter:**
```json
{
  "status": true,
  "message": "",
  "data": {
    "exam": {
      "current_page": 1,
      "data": [
        {
          "id": 123,
          "name": "BCS Preliminary Mock Test 1",
          "category": "BCS",
          "subcategory": "Preliminary",
          "package_id": 5,  // ← Package ID included
          "expired_at": "2025-10-20T23:59:59",
          "questions_count": 100,
          "user_answer": {
            "obtained_marks": 75.00
          }
        }
      ],
      "total": 25
    }
  }
}
```

---

## 🎨 **Frontend Implementation**

### **Example 1: Package Filter Dropdown**
```html
<div class="archive-filters">
  <label>Filter by Package:</label>
  <select id="package-filter" onchange="filterArchive()">
    <option value="">All Packages</option>
    <option value="1">Basic Package</option>
    <option value="5">Premium BCS Package</option>
    <option value="12">Bank Job Package</option>
  </select>
</div>

<script>
async function filterArchive() {
  const packageId = document.getElementById('package-filter').value;
  
  const response = await fetch('/api/exam/archive', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      subcategory: 'Preliminary',
      package_id: packageId || undefined  // Only include if selected
    })
  });
  
  const data = await response.json();
  displayExams(data.data.exam.data);
}
</script>
```

### **Example 2: User's Active Package**
```javascript
// Automatically filter by user's current package
async function showMyPackageExams() {
  // Get user's active package
  const history = await fetch('/api/package-history', {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` },
    body: JSON.stringify({ user_id: currentUserId })
  }).then(r => r.json());
  
  const activePackage = history.data.present_subscribtion;
  
  if (activePackage) {
    // Filter archive by this package
    const exams = await fetch('/api/exam/archive', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}` },
      body: JSON.stringify({
        subcategory: 'Preliminary',
        package_id: activePackage.package_id
      })
    }).then(r => r.json());
    
    showExams(exams.data.exam.data);
  }
}
```

### **Example 3: Package Comparison View**
```javascript
async function comparePackages() {
  const packages = [
    { id: 1, name: 'Basic' },
    { id: 5, name: 'Premium' }
  ];
  
  for (const pkg of packages) {
    const exams = await fetch('/api/exam/archive', {
      method: 'POST',
      body: JSON.stringify({
        subcategory: 'Preliminary',
        package_id: pkg.id
      })
    }).then(r => r.json());
    
    console.log(`${pkg.name} Package: ${exams.data.exam.total} exams`);
  }
}
```

---

## 🔍 **How It Works**

### **Database Query:**
```php
// In ExamManageController@archive()

// For Preliminary exams:
if ($request->package_id) {
    $examQuery = $examQuery->where('package_id', $request->package_id);
}

// For Written exams:
if ($request->package_id) {
    $exam = $exam->where('package_id', $request->package_id);
}
```

### **Relationships:**
```php
// Exam Model
public function package() {
    return $this->belongsTo(Package::class);
}

// Package Model
public function exams() {
    return $this->hasMany(Exam::class);
}

public function writtens() {
    return $this->hasMany(Written::class);
}
```

---

## 🧪 **Testing**

### **Test Case 1: Filter by Package**
```bash
curl -X POST "http://localhost:8000/api/exam/archive" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "subcategory": "Preliminary",
    "package_id": 5
  }'
```

**Expected:** Only exams with `package_id = 5` are returned.

### **Test Case 2: No Package Filter**
```bash
curl -X POST "http://localhost:8000/api/exam/archive" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d "subcategory=Preliminary"
```

**Expected:** All preliminary exams (regardless of package) are returned.

### **Test Case 3: Combined Filters**
```bash
curl -X POST "http://localhost:8000/api/exam/archive" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d "subcategory=Preliminary&category=BCS&package_id=5"
```

**Expected:** Only BCS preliminary exams from package 5.

---

## 📊 **Benefits**

### **For Students:**
- ✅ See only exams from purchased package
- ✅ Better organization of archived exams
- ✅ Easy to track package-specific progress
- ✅ Clear understanding of package value

### **For Admins:**
- ✅ Track which exams belong to which packages
- ✅ Organize content by package
- ✅ Better package management
- ✅ Data-driven package improvements

---

## 🔄 **Backward Compatibility**

### **✅ Fully Backward Compatible**

- Old API calls without `package_id` work exactly as before
- `package_id` is nullable in database (existing exams not affected)
- Filter is optional - doesn't break existing functionality

### **Migration Impact:**
- ✅ Safe to run on production
- ✅ No data loss
- ✅ No breaking changes

---

## 📝 **How to Assign Packages to Exams**

### **Backend (Admin Panel):**
When creating/editing exams, there should be a package dropdown:
```php
// In exam create/edit form
<select name="package_id">
  <option value="">No Package</option>
  @foreach($packages as $package)
    <option value="{{ $package->id }}">{{ $package->name }}</option>
  @endforeach
</select>
```

### **Bulk Assignment (If Needed):**
```php
// Assign all BCS Preliminary exams to package 5
Exam::where('category', 'BCS')
    ->where('subcategory', 'Preliminary')
    ->update(['package_id' => 5]);
```

---

## 📊 **Database Schema**

### **Exams Table:**
```sql
ALTER TABLE exams 
ADD COLUMN package_id BIGINT UNSIGNED NULL 
COMMENT 'Package this exam belongs to',
ADD INDEX idx_package_id (package_id);
```

### **Writtens Table:**
```sql
ALTER TABLE writtens 
ADD COLUMN package_id BIGINT UNSIGNED NULL 
COMMENT 'Package this written exam belongs to',
ADD INDEX idx_package_id (package_id);
```

---

## 🎯 **Common Queries**

### **Q: What if an exam doesn't have a package_id?**
**A:** It will show in archive when no package filter is applied. When filtering by package, it won't appear (since it doesn't belong to that package).

### **Q: Can an exam belong to multiple packages?**
**A:** Currently no. Each exam can belong to one package. If needed, this can be enhanced with a many-to-many relationship.

### **Q: Will old exams break?**
**A:** No. `package_id` is nullable, so existing exams continue working.

### **Q: How do I know which exams belong to a package?**
**A:** Use the Package model relationships:
```php
$package = Package::find(5);
$exams = $package->exams; // All exams in this package
$writtens = $package->writtens; // All written exams
```

---

## ✅ **Implementation Checklist**

- ✅ Database migrations created
- ✅ Migrations run successfully
- ✅ Models updated with relationships
- ✅ Archive API enhanced
- ✅ Postman collection updated
- ✅ JSON validated
- ✅ No linter errors
- ✅ Cache cleared
- ✅ Backward compatible
- ✅ Documentation complete

---

## 📞 **Quick Reference**

### **API Call:**
```javascript
POST /api/exam/archive
Body: {
  subcategory: 'Preliminary',
  package_id: 5  // Filter by package
}
```

### **Filter All Exams:**
```javascript
package_id: undefined  // or don't send parameter
```

### **Filter by Package:**
```javascript
package_id: 5  // Show only package 5 exams
```

---

## 🚀 **Ready to Use!**

The package-wise archive filter is **fully implemented** and ready for:
- Mobile app integration
- Frontend "My Package" views
- Package comparison features
- Better content organization

**Test it now with your package IDs!** 🎉

---

**Last Updated:** October 21, 2025  
**Feature Status:** ✅ **Production Ready**  
**Breaking Changes:** ❌ None (Fully backward compatible)

