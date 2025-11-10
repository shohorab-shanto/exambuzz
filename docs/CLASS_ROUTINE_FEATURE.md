# Class Routine Feature Documentation

## Overview

Simple class routine management system for Preliminary and Written exam preparation. Admin can upload PDF routines that students can view/download via mobile app.

---

## Features

✅ **Admin Panel:**
- Upload PDF routine for Preliminary exam
- Upload PDF routine for Written exam
- Update/Replace existing routines
- Delete routines
- Toggle active/inactive status
- Only one routine per type (latest only)

✅ **Mobile App API:**
- Get all active routines
- Get routine by type (preliminary/written)
- Download/View PDF

---

## Database Schema

### Table: `class_routines`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| type | enum('preliminary', 'written') | Exam type (unique) |
| title | varchar(255) | Optional title |
| pdf_file | varchar(255) | PDF file path |
| status | boolean | 1=Active, 0=Inactive |
| created_at | timestamp | Created timestamp |
| updated_at | timestamp | Updated timestamp |

**Note:** Only ONE routine per type (enforced by unique constraint on `type` column)

---

## Admin Panel

### Access URL
```
/class-routine
```

### Features

#### 1. View All Routines
- Dashboard shows both routine types (Preliminary & Written)
- Display current file, title, status
- Action buttons: Edit, Activate/Deactivate, Delete

#### 2. Upload/Update Routine
```
GET /class-routine/create-edit/{type}
```
- Upload new PDF or replace existing
- Set optional title
- Set status (Active/Inactive)
- Max file size: 10MB
- Allowed format: PDF only

#### 3. Delete Routine
```
DELETE /class-routine/delete/{type}
```
- Deletes routine record
- Removes PDF file from storage

#### 4. Toggle Status
```
POST /class-routine/toggle-status/{type}
```
- Activate/Deactivate routine
- Only active routines visible to students

---

## API Endpoints

### Base URL
```
http://your-domain.com/api
```

### 1. Get All Routines

**Endpoint:** `GET /api/class-routines`

**Description:** Get all active class routines (both types)

**Authentication:** Not required

**Request:**
```http
GET /api/class-routines
Accept: application/json
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "Class routines retrieved successfully",
  "data": [
    {
      "id": 1,
      "type": "preliminary",
      "title": "BCS 47th Preliminary Exam Routine",
      "pdf_file": "http://domain.com/storage/class_routines/class_routine_preliminary_1699520000.pdf",
      "status": true,
      "created_at": "2025-11-10T15:30:00.000000Z",
      "updated_at": "2025-11-10T15:30:00.000000Z"
    },
    {
      "id": 2,
      "type": "written",
      "title": "BCS 46th Written Exam Routine",
      "pdf_file": "http://domain.com/storage/class_routines/class_routine_written_1699520100.pdf",
      "status": true,
      "created_at": "2025-11-10T15:35:00.000000Z",
      "updated_at": "2025-11-10T15:35:00.000000Z"
    }
  ]
}
```

**Empty Response (200):**
```json
{
  "status": true,
  "message": "Class routines retrieved successfully",
  "data": []
}
```

---

### 2. Get Routine by Type

**Endpoint:** `GET /api/class-routines/{type}`

**Description:** Get specific routine by type (preliminary or written)

**Authentication:** Not required

**Path Parameters:**
- `type` (required): "preliminary" or "written"

**Request:**
```http
GET /api/class-routines/preliminary
Accept: application/json
```

**Success Response (200):**
```json
{
  "status": true,
  "message": "Preliminary exam routine retrieved successfully",
  "data": {
    "id": 1,
    "type": "preliminary",
    "title": "BCS 47th Preliminary Exam Routine",
    "pdf_file": "http://domain.com/storage/class_routines/class_routine_preliminary_1699520000.pdf",
    "status": true,
    "created_at": "2025-11-10T15:30:00.000000Z",
    "updated_at": "2025-11-10T15:30:00.000000Z"
  }
}
```

**Not Found Response (404):**
```json
{
  "status": false,
  "message": "Preliminary exam routine not found"
}
```

**Invalid Type Response (400):**
```json
{
  "status": false,
  "message": "Invalid type. Must be \"preliminary\" or \"written\""
}
```

---

## Mobile App Integration

### Kotlin Example

```kotlin
// Data Class
data class ClassRoutine(
    val id: Int,
    val type: String,
    val title: String?,
    val pdf_file: String?,
    val status: Boolean,
    val created_at: String,
    val updated_at: String
)

data class ClassRoutineResponse(
    val status: Boolean,
    val message: String,
    val data: List<ClassRoutine>
)

// API Service
interface ApiService {
    @GET("class-routines")
    suspend fun getAllRoutines(): ClassRoutineResponse
    
    @GET("class-routines/{type}")
    suspend fun getRoutineByType(@Path("type") type: String): Response<ClassRoutine>
}

// Usage
suspend fun loadClassRoutines() {
    try {
        val response = apiService.getAllRoutines()
        if (response.status) {
            // Display routines
            response.data.forEach { routine ->
                when (routine.type) {
                    "preliminary" -> showPreliminaryRoutine(routine)
                    "written" -> showWrittenRoutine(routine)
                }
            }
        }
    } catch (e: Exception) {
        // Handle error
    }
}

// Open PDF
fun openPDF(url: String) {
    val intent = Intent(Intent.ACTION_VIEW).apply {
        setDataAndType(Uri.parse(url), "application/pdf")
        flags = Intent.FLAG_ACTIVITY_NO_HISTORY
    }
    startActivity(intent)
}
```

### React Native Example

```javascript
// Fetch routines
const fetchClassRoutines = async () => {
  try {
    const response = await fetch('http://domain.com/api/class-routines');
    const data = await response.json();
    
    if (data.status) {
      setRoutines(data.data);
    }
  } catch (error) {
    console.error('Error fetching routines:', error);
  }
};

// Open PDF
import { Linking } from 'react-native';

const openPDF = (url) => {
  Linking.openURL(url);
};

// Display
{routines.map((routine) => (
  <TouchableOpacity 
    key={routine.id}
    onPress={() => openPDF(routine.pdf_file)}
  >
    <Text>{routine.title || `${routine.type} Exam Routine`}</Text>
    <Text>View PDF</Text>
  </TouchableOpacity>
))}
```

---

## File Structure

```
app/
├── Models/
│   └── ClassRoutine.php                      # Model
├── Http/
│   └── Controllers/
│       ├── Backend/
│       │   └── ClassRoutineController.php    # Admin CRUD
│       └── Api/
│           └── ClassRoutineController.php     # API endpoints

database/
└── migrations/
    └── 2025_11_10_151506_create_class_routines_table.php

resources/
└── views/
    └── backend/
        └── class-routine/
            ├── index.blade.php               # List view
            └── create-edit.blade.php         # Upload/Edit form

routes/
├── web.php                                   # Admin routes
└── api.php                                   # API routes

storage/
└── app/
    └── public/
        └── class_routines/                   # Uploaded PDFs
            ├── class_routine_preliminary_xxx.pdf
            └── class_routine_written_xxx.pdf
```

---

## Usage Guide

### For Admins

**1. Upload Preliminary Routine:**
1. Login to admin panel
2. Go to "Class Routine" menu
3. Click "Upload Routine" under Preliminary Exam
4. Enter title (optional): "BCS 47th Preliminary Exam Routine"
5. Upload PDF file (max 10MB)
6. Set status: Active
7. Click "Upload Routine"

**2. Update Existing Routine:**
1. Click "Edit" button on the routine card
2. Change title or upload new PDF
3. Click "Update Routine"
   - Old PDF will be deleted automatically
   - New PDF will replace it

**3. Delete Routine:**
1. Click "Delete" button
2. Confirm deletion
   - Routine record deleted from database
   - PDF file removed from storage

**4. Toggle Status:**
1. Click "Activate"/"Deactivate" button
   - Only active routines visible to students

---

### For Mobile App Developers

**1. Fetch All Routines:**
```kotlin
GET /api/class-routines
```

**2. Display Routines:**
- Show title or fallback to "{Type} Exam Routine"
- Display "View PDF" button
- Open PDF in external viewer or in-app PDF viewer

**3. Download PDF:**
- Use `pdf_file` URL to download
- Save to device if needed
- Open in PDF viewer

---

## Testing

### Admin Panel Testing

1. **Create Preliminary Routine:**
```
- Navigate to /class-routine
- Click "Upload Routine" for Preliminary
- Fill title: "Test Preliminary Routine"
- Upload test PDF
- Set Active
- Save
- Verify appears in dashboard
```

2. **Update Routine:**
```
- Click Edit
- Upload new PDF
- Save
- Verify old PDF deleted, new PDF saved
```

3. **Toggle Status:**
```
- Click Deactivate
- Verify status changes
- API should not return this routine
- Click Activate
- API should return routine
```

4. **Delete Routine:**
```
- Click Delete
- Confirm
- Verify routine removed
- Verify PDF file deleted from storage
```

### API Testing (Postman/cURL)

**1. Get All Routines:**
```bash
curl -X GET http://localhost/api/class-routines \
  -H "Accept: application/json"
```

**2. Get Preliminary Routine:**
```bash
curl -X GET http://localhost/api/class-routines/preliminary \
  -H "Accept: application/json"
```

**3. Get Written Routine:**
```bash
curl -X GET http://localhost/api/class-routines/written \
  -H "Accept: application/json"
```

**4. Invalid Type:**
```bash
curl -X GET http://localhost/api/class-routines/invalid \
  -H "Accept: application/json"
# Should return 400 error
```

---

## Troubleshooting

### Issue: PDF not uploading

**Check:**
1. Storage link created: `php artisan storage:link`
2. Directory writable: `chmod -R 775 storage/app/public`
3. File size within limit (10MB)
4. File is valid PDF

### Issue: PDF URL not working

**Check:**
1. Storage symlink exists: `public/storage` → `storage/app/public`
2. File exists in `storage/app/public/class_routines/`
3. Web server has permission to read file

### Issue: Can't access admin panel

**Check:**
1. Logged in as admin
2. Admin middleware applied
3. Routes registered: `php artisan route:list | grep class-routine`

### Issue: API returns empty

**Check:**
1. Routine status is Active (status = 1)
2. Routine exists in database
3. Check database: `SELECT * FROM class_routines;`

---

## Security Considerations

1. **File Upload:**
   - Only PDF files allowed
   - Max size: 10MB
   - Validates MIME type
   - Unique filename with timestamp

2. **Access Control:**
   - Admin panel protected by auth middleware
   - Only admins can upload/edit/delete
   - API is public (no sensitive data)

3. **File Storage:**
   - Files stored in `storage/app/public`
   - Accessible via public symlink
   - Old files deleted when updated

---

## Future Enhancements

- [ ] Multiple routines per type (with history)
- [ ] Category-wise routines (BCS, Bank, Primary, etc.)
- [ ] Package-wise access control
- [ ] Push notification when new routine uploaded
- [ ] Support for images/docs (not just PDF)
- [ ] Routine expiry date
- [ ] View/Download count tracking

---

## Database Queries

### Get active routines:
```sql
SELECT * FROM class_routines WHERE status = 1;
```

### Get preliminary routine:
```sql
SELECT * FROM class_routines WHERE type = 'preliminary' AND status = 1;
```

### Get written routine:
```sql
SELECT * FROM class_routines WHERE type = 'written' AND status = 1;
```

---

## Summary

✅ **Simple 2-PDF system** (Preliminary + Written)  
✅ **Full CRUD in admin panel**  
✅ **Clean API for mobile app**  
✅ **Automatic file management**  
✅ **Status toggle for visibility control**  
✅ **No breaking changes to existing system**  

**Status:** READY FOR USE ✓

---

**Created:** November 10, 2025  
**Last Updated:** November 10, 2025  
**Version:** 1.0

