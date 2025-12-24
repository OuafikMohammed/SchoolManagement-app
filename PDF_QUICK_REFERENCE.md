# PDF Service Quick Reference Guide

## 🎯 Quick Start

### For Students
1. Go to **My Grades** page
2. Find your course
3. Click **Download Bulletin** or **View PDF**
4. Check your downloads folder

### For Teachers
1. Go to **Teacher Dashboard** or **Course Details**
2. Click **Report** button
3. Choose **Download** or **View** option
4. Check your downloads folder

---

## 🔗 Routes at a Glance

| User Role | Action | Route | Button | Page |
|-----------|--------|-------|--------|------|
| Student | Download Bulletin | `/pdf/bulletin/{id}` | Download | Grades |
| Student | View Bulletin | `/pdf/bulletin/{id}/view` | View | Grades |
| Teacher | Download Report | `/pdf/course-report/{id}` | Report | Dashboard |
| Teacher | View Report | `/pdf/course-report/{id}/view` | View | Course |

---

## 📍 Where to Find PDF Buttons

### Student Pages
- **Dashboard** → "Bulletin" button in course row
- **My Grades** → "Download Bulletin" + "View PDF" buttons per course
- **Grades Details** → Quick links to bulletin PDFs

### Teacher Pages
- **Dashboard** → "Report" button in course row
- **Course Details** → "Download Report" + "View Report" buttons
- **Course Management** → Quick PDF access

---

## ⚙️ Configuration

### Enabling PDF Download
```yaml
# Already enabled in config/routes.yaml
pdf:
  resource: '@FrameworkBundle/Resources/config/routing/errors.xml'
  prefix: /pdf
```

### PDF Template Location
```
templates/pdf/
├── bulletin.html.twig      # Student grade bulletin
├── course_report.html.twig # Teacher course report
└── testing_dashboard.html.twig
```

### Service Classes
```php
App\Service\PdfGeneratorService    // Generates PDFs
App\Controller\Shared\PdfController // Handles routes
```

---

## 🔐 Security Rules

| Rule | Check | Response |
|------|-------|----------|
| Student Auth | Must be logged in as ROLE_STUDENT | 401 Unauthorized |
| Teacher Auth | Must be logged in as ROLE_TEACHER | 401 Unauthorized |
| Enrollment | Student must be enrolled in course | 403 Forbidden |
| Ownership | Teacher must own the course | 403 Forbidden |
| Course Exists | Course must exist in database | 404 Not Found |

---

## 💬 Message Display

### Success Messages
```
✓ Downloading PDF for "Mathematics"
✓ Opening PDF for "Physics" in a new tab
```

### Error Messages
```
✗ Failed to load PDF for "Chemistry"
✗ You are not enrolled in this course
✗ You do not own this course
```

### States
- **Loading**: Gray button with spinner
- **Disabled**: Faded appearance during generation
- **Active**: Normal state, ready to click

---

## 🧪 Test Commands

### Run all PDF tests
```bash
php bin/phpunit tests/Functional/Controller/PdfControllerTest.php
```

### Run specific test
```bash
php bin/phpunit tests/Functional/Controller/PdfControllerTest.php --filter testStudentBulletinPdfGeneration
```

### Run with coverage
```bash
php bin/phpunit tests/Functional/Controller/PdfControllerTest.php --coverage-text
```

---

## 📱 Browser Support

| Browser | Download | View | Status |
|---------|----------|------|--------|
| Chrome | ✅ | ✅ | Full |
| Firefox | ✅ | ✅ | Full |
| Safari | ✅ | ✅ | Full |
| Edge | ✅ | ✅ | Full |
| IE11 | ⚠️ | ⚠️ | Limited |

---

## 🐛 Troubleshooting

### "You are not enrolled in this course"
- **Cause**: Trying to access bulletin without enrollment
- **Fix**: Enroll in the course from available courses

### "You do not own this course"
- **Cause**: Accessing another teacher's report
- **Fix**: Only access your own courses

### "Course not found"
- **Cause**: Invalid course ID in URL
- **Fix**: Use valid course ID from your list

### PDF not downloading
- **Cause**: Browser popup blocker
- **Fix**: Allow popups for this site or disable blocker

### Empty PDF
- **Cause**: Course has no grades/data
- **Fix**: Add grades before generating PDF

---

## 🔗 API Usage

### JavaScript Direct Access
```javascript
// Download bulletin
window.location.href = `/pdf/bulletin/1`;

// View in browser
window.open(`/pdf/bulletin/1/view`, '_blank');

// Download report
window.location.href = `/pdf/course-report/1`;

// View report
window.open(`/pdf/course-report/1/view`, '_blank');
```

### Twig Template Links
```html
<!-- Download -->
<a href="{{ path('pdf_student_bulletin', {courseId: course.id}) }}">
    Download Bulletin
</a>

<!-- View -->
<a href="{{ path('pdf_student_bulletin_view', {courseId: course.id}) }}" target="_blank">
    View PDF
</a>
```

---

## 📊 Response Headers

### Download Response
```
Content-Type: application/pdf
Content-Disposition: attachment; filename="bulletin.pdf"
Cache-Control: no-cache
```

### View Response
```
Content-Type: application/pdf
Content-Disposition: inline; filename="bulletin.pdf"
Cache-Control: no-cache
```

---

## 🎨 Button Classes

### Student Pages
```html
.pdf-download      <!-- Download button -->
.pdf-view         <!-- View button -->
.pdf-download-student  <!-- Dashboard button -->
```

### Teacher Pages
```html
.pdf-download-dash  <!-- Dashboard report button -->
```

---

## 📝 Related Documentation

- **Full Guide**: `docs/PDF_SERVICE_INTEGRATION.md`
- **Implementation**: `PDF_SERVICE_IMPLEMENTATION_SUMMARY.md`
- **Testing Guide**: `docs/PDF_SERVICE_TESTING.md`

---

## ✅ Pre-Deployment Checklist

- [ ] All tests pass: `php bin/phpunit`
- [ ] PDF buttons visible in dashboard
- [ ] Download works for students
- [ ] Download works for teachers
- [ ] View in browser works
- [ ] Error messages display correctly
- [ ] Loading states show properly
- [ ] No JavaScript console errors
- [ ] Responsive on mobile
- [ ] Security checks working (403 errors)

---

## 🚀 Quick Commands

```bash
# Test PDFs
php bin/phpunit tests/Functional/Controller/PdfControllerTest.php

# Clear cache
php bin/console cache:clear

# Check routes
php bin/console debug:router | grep pdf

# List templates
find templates/pdf -type f
```

---

## 📞 Support

For issues or questions:

1. Check the **Full Integration Guide** (`docs/PDF_SERVICE_INTEGRATION.md`)
2. Review **Implementation Summary** (`PDF_SERVICE_IMPLEMENTATION_SUMMARY.md`)
3. Run **tests** to verify functionality
4. Check **browser console** for JavaScript errors
5. Verify **security settings** (authentication, roles)

---

**Last Updated**: December 24, 2025  
**Status**: ✅ Production Ready  
**Version**: 1.0
