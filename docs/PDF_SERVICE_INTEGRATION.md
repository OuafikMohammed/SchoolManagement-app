# PDF Service Integration Guide

## Overview

The PDF Service provides functionality for generating and downloading PDF documents in the School Management App. It integrates between the frontend (Twig templates) and backend (Symfony services) with proper security checks and role-based access control.

## Architecture

### Components

1. **PDF Controller** (`App\Controller\Shared\PdfController`)
   - Handles HTTP requests for PDF generation
   - Implements security checks and access control
   - Provides both download and view endpoints

2. **PDF Generator Service** (`App\Service\PdfGeneratorService`)
   - Generates PDF content from Twig templates
   - Uses DomPDF for PDF rendering
   - Supports bulletin and course report templates

3. **Security Layer**
   - Role-based access control (ROLE_STUDENT, ROLE_TEACHER)
   - Enrollment validation for students
   - Course ownership validation for teachers

## API Endpoints

### Student Routes

#### Download Student Bulletin
```
Route: GET /pdf/bulletin/{courseId}
Name:  pdf_student_bulletin
Role:  ROLE_STUDENT
Content-Type: application/pdf (attachment)
Description: Downloads student's grade bulletin for a specific course
```

**Request Example:**
```
GET /pdf/bulletin/1
```

**Response:**
- HTTP Status: 200 OK
- Content-Type: application/pdf
- Content-Disposition: attachment; filename="bulletin.pdf"

**Error Responses:**
- 404: Course not found
- 403: Student not enrolled in course
- 401: Not authenticated / Not a student

#### View Student Bulletin in Browser
```
Route: GET /pdf/bulletin/{courseId}/view
Name:  pdf_student_bulletin_view
Role:  ROLE_STUDENT
Content-Type: application/pdf (inline)
Description: Opens student's bulletin PDF in browser
```

**Request Example:**
```
GET /pdf/bulletin/1/view
```

**Response:**
- HTTP Status: 200 OK
- Content-Type: application/pdf
- Content-Disposition: inline; filename="bulletin.pdf"

### Teacher Routes

#### Download Course Report
```
Route: GET /pdf/course-report/{courseId}
Name:  pdf_course_report
Role:  ROLE_TEACHER
Content-Type: application/pdf (attachment)
Description: Downloads comprehensive course report PDF
```

**Request Example:**
```
GET /pdf/course-report/1
```

**Response:**
- HTTP Status: 200 OK
- Content-Type: application/pdf
- Content-Disposition: attachment; filename="course_report.pdf"

**Error Responses:**
- 404: Course not found
- 403: Teacher does not own this course
- 401: Not authenticated / Not a teacher

#### View Course Report in Browser
```
Route: GET /pdf/course-report/{courseId}/view
Name:  pdf_course_report_view
Role:  ROLE_TEACHER
Content-Type: application/pdf (inline)
Description: Opens course report PDF in browser
```

**Request Example:**
```
GET /pdf/course-report/1/view
```

**Response:**
- HTTP Status: 200 OK
- Content-Type: application/pdf
- Content-Disposition: inline; filename="course_report.pdf"

## Frontend Implementation

### Student Dashboard Integration

**Location:** `templates/student/dashboard.html.twig`

**Features:**
- Download button for each enrolled course
- Toast notifications for download status
- Loading state with spinner
- Error handling with validation messages

**HTML:**
```html
<a href="{{ path('pdf_student_bulletin', {courseId: enrollment.course.id}) }}" 
   class="btn btn-outline-success pdf-download-student"
   data-course="{{ enrollment.course.title }}"
   title="Download Bulletin">
    <i class="bi bi-download"></i> Bulletin
</a>
```

### Student Grades Page Integration

**Location:** `templates/student/grade/my_grades.html.twig`

**Features:**
- Download and view buttons for each course
- Detailed grade statistics
- Individual course grade cards
- Quick PDF access from grades view

**HTML:**
```html
<div class="btn-group" role="group">
    <a href="{{ path('pdf_student_bulletin', {courseId: courseGrade.course.id}) }}" 
       class="btn btn-sm btn-outline-success pdf-download"
       data-course="{{ courseGrade.course.title }}">
        <i class="bi bi-download"></i> Download Bulletin
    </a>
    <a href="{{ path('pdf_student_bulletin_view', {courseId: courseGrade.course.id}) }}" 
       class="btn btn-sm btn-outline-info pdf-view"
       target="_blank"
       data-course="{{ courseGrade.course.title }}">
        <i class="bi bi-eye-fill"></i> View PDF
    </a>
</div>
```

### Teacher Dashboard Integration

**Location:** `templates/teacher/dashboard.html.twig`

**Features:**
- Download button for each course report
- Course list with quick report access
- Loading state and notifications
- Toast notifications at bottom-right

**HTML:**
```html
<a href="{{ path('pdf_course_report', {courseId: course.id}) }}" 
   class="btn btn-outline-success pdf-download-dash"
   data-course="{{ course.title }}"
   title="Download Course Report PDF">
    <i class="bi bi-download"></i> Report
</a>
```

### Teacher Course Details Page Integration

**Location:** `templates/teacher/course/show.html.twig`

**Features:**
- Download and view buttons for course report
- Prominent PDF action card
- Loading state feedback
- Success/error messages in card body

**HTML:**
```html
<div class="btn-group" role="group">
    <a href="{{ path('pdf_course_report', {courseId: course.id}) }}" 
       class="btn btn-primary pdf-download"
       data-course="{{ course.title }}"
       title="Download Course Report PDF">
        <i class="bi bi-download"></i> Download Report
    </a>
    <a href="{{ path('pdf_course_report_view', {courseId: course.id}) }}" 
       class="btn btn-outline-primary pdf-view"
       data-course="{{ course.title }}"
       title="View Report in Browser"
       target="_blank">
        <i class="bi bi-eye"></i> View Report
    </a>
</div>
```

## JavaScript Implementation

### Button Click Handler

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const pdfButtons = document.querySelectorAll('.pdf-download, .pdf-view');
    
    pdfButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const courseName = this.getAttribute('data-course');
            const isView = this.classList.contains('pdf-view');
            
            // Show loading state
            const originalHtml = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
            this.disabled = true;
            
            // Set timeout to restore button
            setTimeout(() => {
                this.innerHTML = originalHtml;
                this.disabled = false;
                
                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
                alertDiv.role = 'alert';
                alertDiv.innerHTML = `
                    <i class="bi bi-check-circle"></i>
                    <strong>${isView ? 'Opening' : 'Downloading'} PDF for "${courseName}"</strong>
                    ${isView ? ' - Your PDF will open in a new tab.' : ' - Check your downloads folder.'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                const container = button.closest('.card-body');
                if (container) {
                    container.insertBefore(alertDiv, container.firstChild);
                    
                    // Auto-dismiss after 5 seconds
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 5000);
                }
            }, 1500);
        });
    });
});
```

### Toast Notification Handler (Dashboard)

```javascript
// Show toast notification
const alertDiv = document.createElement('div');
alertDiv.className = 'position-fixed bottom-0 end-0 m-3 alert alert-success alert-dismissible fade show';
alertDiv.role = 'alert';
alertDiv.style.zIndex = '9999';
alertDiv.innerHTML = `
    <i class="bi bi-check-circle"></i>
    <strong>Downloading</strong> bulletin for "${courseName}"
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
`;

document.body.appendChild(alertDiv);

// Auto-dismiss after 5 seconds
setTimeout(() => {
    if (alertDiv.parentNode) {
        alertDiv.remove();
    }
}, 5000);
```

## Validation & Error Handling

### Server-Side Validation

1. **Course Existence Check**
   ```php
   if (!$course) {
       throw $this->createNotFoundException('Course not found');
   }
   ```

2. **Student Enrollment Check** (Student Bulletin)
   ```php
   $isEnrolled = false;
   foreach ($course->getEnrollments() as $enrollment) {
       if ($enrollment->getStudent() === $this->getUser()) {
           $isEnrolled = true;
           break;
       }
   }
   
   if (!$isEnrolled) {
       throw $this->createAccessDeniedException('You are not enrolled in this course');
   }
   ```

3. **Course Ownership Check** (Course Report)
   ```php
   if ($course->getTeacher() !== $this->getUser()) {
       throw $this->createAccessDeniedException('You do not own this course');
   }
   ```

### Client-Side Validation Messages

| Scenario | Message | Type | Duration |
|----------|---------|------|----------|
| PDF Download Started | "Downloading PDF for '{Course}'" | Success (green) | 5 seconds |
| PDF View Started | "Opening PDF for '{Course}' in a new tab" | Success (green) | 5 seconds |
| Download Error | "Failed to load PDF for '{Course}'" | Error (red) | 5 seconds |
| Loading State | "Loading..." with spinner | Neutral | Until complete |

## Testing

### Unit Tests

**Location:** `tests/Functional/Controller/PdfControllerTest.php`

**Test Cases:**
1. ✅ Student can download bulletin if enrolled
2. ✅ Student cannot download bulletin if not enrolled (403)
3. ✅ Teacher can download course report for their course
4. ✅ Teacher cannot download report if not teacher (403)
5. ✅ Teacher cannot download report for other teacher's course (403)

**Run Tests:**
```bash
php bin/phpunit tests/Functional/Controller/PdfControllerTest.php
```

**Expected Output:**
```
PHPUnit 11.5.46 by Sebastian Bergmann

Runtime: PHP 8.2.12

.....  5 / 5 (100%)

Time: 00:10.942, Memory: 44.00 MB

OK (5 tests, 7 assertions)
```

### Manual Testing

1. **Student Bulletin Download:**
   - Log in as student
   - Go to "My Grades" page
   - Click "Download Bulletin" button for a course
   - Verify PDF downloads with correct data
   - Try accessing bulletin for non-enrolled course (should show 403)

2. **Teacher Course Report:**
   - Log in as teacher
   - Go to course details page
   - Click "Download Report" button
   - Verify PDF downloads with all course data
   - Try accessing report for other teacher's course (should show 403)

3. **View in Browser:**
   - Click "View PDF" button
   - Verify PDF opens in new tab instead of downloading

4. **Error Scenarios:**
   - Access with invalid course ID (should show 404)
   - Access without proper role (should show 401/403)
   - Access from unauthenticated session (should redirect to login)

## Troubleshooting

### Common Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| "Course not found" error | Invalid course ID in URL | Verify course ID exists in database |
| "You are not enrolled" error | Student trying to access bulletin without enrollment | Enroll student in course first |
| "You do not own this course" error | Teacher accessing report for another teacher's course | Access your own courses only |
| PDF not downloading | Browser popup blocker or JavaScript disabled | Check browser settings, enable JavaScript |
| Empty PDF | Template data issue | Check if course has grades/enrollments |
| 403 Forbidden | Missing authentication or insufficient role | Log in with correct user role |
| 401 Unauthorized | Session expired | Log in again |

### Debug Mode

Enable debug logging for PDF generation:

```yaml
# config/packages/dev/monolog.yaml
when@dev:
    monolog:
        handlers:
            pdf_debug:
                type: stream
                path: "%kernel.logs_dir%/pdf.log"
                level: debug
                channels: ['pdf']
```

## Security Considerations

1. **Authentication:** All PDF routes require `#[IsGranted()]` attribute
2. **Authorization:** Role-based checks ensure correct access
3. **Data Validation:** Course ownership and enrollment verified server-side
4. **Content-Type:** Proper headers prevent direct execution
5. **File Disposal:** Dispositions set correctly (attachment vs inline)

## Performance Optimization

1. **Caching:** PDF files cached for 5 minutes when possible
2. **Lazy Loading:** PDFs generated on-demand only
3. **Compression:** PDF output compressed when supported
4. **Async Generation:** Consider background jobs for large reports

## Browser Compatibility

| Browser | Download | View in Browser | Status |
|---------|----------|-----------------|--------|
| Chrome | ✅ | ✅ | Full Support |
| Firefox | ✅ | ✅ | Full Support |
| Safari | ✅ | ✅ | Full Support |
| Edge | ✅ | ✅ | Full Support |
| IE 11 | ⚠️ | ⚠️ | Limited Support |

## Future Enhancements

1. **Email PDF:** Send bulletins/reports via email
2. **Scheduled Reports:** Auto-generate reports on schedule
3. **Archive Storage:** Store generated PDFs for compliance
4. **Digital Signatures:** Sign PDFs for authenticity
5. **Multi-language:** Generate PDFs in student's preferred language
6. **Custom Branding:** School logo and branding in PDFs
7. **Bulk Export:** Generate multiple PDFs at once
8. **Analytics:** Track PDF download statistics
