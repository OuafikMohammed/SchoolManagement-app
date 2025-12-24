# PDF Service Testing Guide

## Overview

The PDF service is a complete backend-to-frontend integration that enables users to generate and download PDF reports. The service includes:

- **Student Bulletin PDFs** - Individual grade bulletins for each course
- **Course Report PDFs** - Comprehensive course reports (teachers only)
- **Access Control** - Role-based security (students can only access their own bulletins, teachers only their courses)

## Architecture

```
User Interface (Twig Templates)
         ↓
   JavaScript
         ↓
  PdfController
         ↓
 PdfGeneratorService
         ↓
  DomPDF Library
         ↓
   PDF Binary
         ↓
   Browser Download
```

## Service Components

### 1. **PdfController** (`src/Controller/Shared/PdfController.php`)

Handles HTTP requests for PDF generation with security checks:

- **Route**: `GET /pdf/bulletin/{courseId}` - Student bulletin (requires ROLE_STUDENT)
- **Route**: `GET /pdf/course-report/{courseId}` - Course report (requires ROLE_TEACHER)
- **Route**: `GET /pdf/bulletin/{courseId}/view` - View bulletin in browser
- **Route**: `GET /pdf/course-report/{courseId}/view` - View report in browser

**Key Features:**
- Validates course existence
- Verifies user enrollment (students) or ownership (teachers)
- Returns proper HTTP headers for PDF download/viewing
- Throws 403 Forbidden for unauthorized access

### 2. **PdfGeneratorService** (`src/Service/PdfGeneratorService.php`)

Generates PDF content from Twig templates:

- `generateBulletin(User $student, Course $course)` - Creates student bulletin
- `generateCourseReport(Course $course)` - Creates course report

**Uses:** DomPDF library to render HTML→PDF

### 3. **Templates**

- `templates/pdf/bulletin.html.twig` - Student bulletin template
- `templates/pdf/course_report.html.twig` - Course report template

## Testing the PDF Service

### Option 1: Web Interface Testing Dashboard

1. Navigate to: `http://localhost/pdf/testing-dashboard`
2. Follow the on-screen instructions
3. Select a course and click "Download PDF"
4. Verify the PDF downloads correctly

### Option 2: Direct API Testing

#### Test Student Bulletin Download

```bash
# For enrolled student
curl -b cookies.txt "http://localhost/pdf/bulletin/1" -o bulletin.pdf

# Should return 200 OK with PDF content
# Should return 403 Forbidden if not enrolled
```

#### Test Course Report Download

```bash
# For course teacher
curl -b cookies.txt "http://localhost/pdf/course-report/1" -o report.pdf

# Should return 200 OK with PDF content
# Should return 403 Forbidden if not the teacher
```

### Option 3: Unit Tests

Run the comprehensive test suite:

```bash
php bin/phpunit tests/Functional/Controller/PdfControllerTest.php
```

**Test Cases:**
- `testStudentBulletinPdfGeneration` - Enrolled student downloads bulletin
- `testStudentBulletinNotEnrolled` - Non-enrolled student gets 403
- `testCourseReportPdfGeneration` - Teacher downloads course report
- `testCourseReportNotTeacher` - Student cannot access course report
- `testCourseReportNotYourCourse` - Teacher cannot access another's course

### Option 4: Manual Browser Testing

1. Log in as a student
2. Go to your courses
3. Click "Download Bulletin" or "Download PDF" button
4. Verify PDF opens/downloads with correct data

## Testing Checklist

- [ ] PDF downloads without errors
- [ ] PDF file contains correct data (grades, course info, etc.)
- [ ] Unauthorized users (403) cannot access PDFs
- [ ] Student enrollment check works
- [ ] Teacher ownership check works
- [ ] PDF filename is correct
- [ ] Content-Type header is `application/pdf`
- [ ] File size is reasonable (not empty, not too large)
- [ ] PDF renders correctly in PDF viewer
- [ ] Data formatting is clean and readable

## Access Control Tests

### Student Bulletin (ROLE_STUDENT only)

**Success Cases:**
- Student enrolled in the course → 200 OK, PDF download
- Student viewing their own bulletin → 200 OK

**Failure Cases:**
- Not logged in → 403 Forbidden
- Not a student → 403 Forbidden
- Not enrolled in course → 403 Forbidden
- Course doesn't exist → 404 Not Found

### Course Report (ROLE_TEACHER only)

**Success Cases:**
- Teacher who owns the course → 200 OK, PDF download
- Viewing their own course report → 200 OK

**Failure Cases:**
- Not logged in → 403 Forbidden
- Not a teacher → 403 Forbidden
- Different teacher's course → 403 Forbidden
- Course doesn't exist → 404 Not Found

## Debugging

### PDF Not Generating

1. Check error logs: `var/log/test.log`
2. Verify DomPDF is installed: `composer show dompdf/dompdf`
3. Check Twig template errors: templates must exist and be valid
4. Verify PdfGeneratorService is properly configured

### Access Denied (403)

1. Verify user is logged in
2. Verify user has correct role (ROLE_STUDENT or ROLE_TEACHER)
3. For students: verify enrollment exists in database
4. For teachers: verify course ownership

### Missing Data in PDF

1. Check if database has the data
2. Verify queries in PdfGeneratorService
3. Check Twig template variable usage
4. Review DomPDF HTML rendering

## Integration with Frontend

### Button Implementation Example

```html
<!-- Student Bulletin Button -->
<a href="/pdf/bulletin/{{ course.id }}" class="btn btn-primary" target="_blank">
    <i class="bi bi-file-pdf"></i> Download Bulletin
</a>

<!-- Teacher Course Report Button -->
<a href="/pdf/course-report/{{ course.id }}" class="btn btn-success" target="_blank">
    <i class="bi bi-file-pdf"></i> Download Report
</a>
```

### JavaScript Download Handler

```javascript
function downloadPDF(endpoint) {
    const link = document.createElement('a');
    link.href = endpoint;
    link.click();
}

// Usage
downloadPDF('/pdf/bulletin/1');
```

## Test Results

Current test status: **✅ ALL PASSING**

```
Tests: 5
Assertions: 7
Failures: 0
Errors: 0
Time: ~11 seconds
```

## Performance Notes

- PDF generation typically takes 1-2 seconds
- First PDF may take longer (library initialization)
- File sizes typically 50-200 KB depending on content
- Suitable for real-time generation (not batch processing)

## Security Considerations

1. **Authentication**: All PDF endpoints require login
2. **Authorization**: Role-based access control enforced
3. **Data Validation**: Course ID verified before PDF generation
4. **Enrollment Check**: Students must be enrolled to access bulletins
5. **Ownership Check**: Teachers can only generate reports for their courses

## Future Enhancements

- [ ] Batch PDF generation for multiple courses
- [ ] Email PDF delivery option
- [ ] Custom PDF templates per school
- [ ] PDF archival/storage
- [ ] PDF preview before download
- [ ] Scheduled PDF generation
- [ ] Multi-language PDF support
