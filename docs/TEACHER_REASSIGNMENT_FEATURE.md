# Teacher Reassignment Feature for Paper Recheck

## Overview
This feature allows admin to reassign a different teacher to recheck papers that have already been checked. This is useful when a second opinion is needed or when there are concerns about the initial checking.

## Feature Details

### What's New
- Admin can now reassign checked papers to a different teacher for rechecking
- New "Reassign Teacher" button appears for papers with `is_checked = 1` status
- Modal popup for selecting new teacher with paper details
- Automatic status change to `is_checked = 2` (Assigned for Recheck) upon reassignment

### User Flow

1. **Admin Views Assigned Papers**
   - Navigate to: `/admin/written/assign-paper/{written_id}/{category}`
   - Papers with different statuses will show different action buttons

2. **Paper Statuses**
   - **Not Checked (is_checked = 0)**: Shows "Remove Teacher" button
   - **Checked (is_checked = 1)**: Shows three buttons:
     - "Paper Checked" (status indicator)
     - "Recheck Able" (marks for recheck with same teacher)
     - **"Reassign Teacher"** (NEW - assigns to different teacher)
   - **Assigned for Recheck (is_checked = 2)**: Shows status with assigned teacher name

3. **Reassigning a Teacher**
   - Click "Reassign Teacher" button on a checked paper
   - Modal opens showing:
     - Student registration ID and name
     - Current teacher who checked the paper
     - Current obtained mark
     - Dropdown to select new teacher (excludes current teacher)
   - Select new teacher from dropdown
   - Click "Reassign for Recheck"
   - Paper is reassigned and status changes to "Assigned for Recheck"

### Technical Implementation

#### 1. Route Added
```php
Route::post('/reassign-teacher-for-recheck', 'reassignTeacherForRecheck')
    ->name('reassignTeacherForRecheck');
```

#### 2. Controller Method
**File**: `app/Http/Controllers/Backend/TeacherExamAssignController.php`

**Method**: `reassignTeacherForRecheck(Request $request)`

**Logic**:
- Validates paper_id and new_teacher_id are provided
- Checks if paper exists
- Ensures paper is already checked (is_checked = 1)
- Updates teacher_id to new teacher
- Sets is_checked = 2 (Assigned for Recheck)
- Returns success message

**Validations**:
- Paper must be selected
- New teacher must be selected
- Paper must exist in database
- Only papers with is_checked = 1 can be reassigned

#### 3. View Updates
**File**: `resources/views/backend/teacher/exam/assign-paper.blade.php`

**Changes**:
- Added "Reassign Teacher" button for checked papers
- Created modal for teacher selection
- Modal shows paper details and teacher dropdown
- Dropdown excludes current teacher from selection
- Added teacher name badge for recheck status display

### Database Changes
No database migrations required. Uses existing fields:
- `written_answers.teacher_id` - Updated to new teacher
- `written_answers.is_checked` - Set to 2 for recheck status

### API Endpoints
- **Endpoint**: `POST /admin/written/reassign-teacher-for-recheck`
- **Parameters**:
  - `paper_id` (required): ID of the WrittenAnswer
  - `new_teacher_id` (required): ID of the new teacher to assign
- **Response**: Redirects back with success/error toast message

### Status Flow
```
Initial Assignment → Teacher Checks → Admin Reassigns → New Teacher Rechecks
(is_checked = 0)    (is_checked = 1)  (is_checked = 2)    (is_checked = 1)
teacher_id = A      teacher_id = A     teacher_id = B      teacher_id = B
```

### Benefits
1. **Second Opinion**: Get another teacher's evaluation on questionable papers
2. **Quality Control**: Admin can verify marking accuracy
3. **Dispute Resolution**: Handle student appeals effectively
4. **Flexibility**: Reassign to any qualified teacher
5. **Audit Trail**: Status changes track reassignment history

### UI Components
- **Success Button**: Green "Paper Checked" status indicator
- **Warning Button**: Yellow "Recheck Able" for same-teacher recheck
- **Primary Button**: Blue "Reassign Teacher" for different-teacher recheck
- **Info Button**: Cyan "Assigned for Recheck" status with teacher badge
- **Modal**: Bootstrap modal with form for teacher selection

### Security & Validation
- Only checked papers (is_checked = 1) can be reassigned
- Current teacher is excluded from reassignment dropdown
- CSRF protection on form submission
- Validation messages for missing fields
- Database existence checks before updates

### Future Enhancements (Optional)
- Track reassignment history (original teacher, new teacher, timestamp)
- Notification to new teacher about reassignment
- Comparison view between original and recheck marks
- Reason field for reassignment
- Email notifications to both teachers
- Statistics on reassignment frequency

## Testing Checklist
- [ ] Reassign a checked paper to a new teacher
- [ ] Verify status changes to "Assigned for Recheck"
- [ ] Verify new teacher sees the paper in their panel
- [ ] Try reassigning without selecting teacher (should fail)
- [ ] Try reassigning an unchecked paper (should fail)
- [ ] Verify modal shows correct paper details
- [ ] Verify current teacher is excluded from dropdown
- [ ] Check that teacher name displays in recheck status

## Related Files
- `/routes/web.php` - Route definition
- `/app/Http/Controllers/Backend/TeacherExamAssignController.php` - Controller logic
- `/resources/views/backend/teacher/exam/assign-paper.blade.php` - UI implementation
- `/app/Models/WrittenAnswer.php` - Model for written answers

