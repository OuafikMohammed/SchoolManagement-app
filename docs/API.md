## API Reference

### Authentication Endpoints

#### POST /login
Login with email and password.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:** Redirect to dashboard or home

---

#### POST /register
Register a new student account.

**Request:**
```json
{
  "registration": {
    "email": "newuser@example.com",
    "name": "John Doe",
    "plainPassword": "password123",
    "plainPassword": {
      "first": "password123",
      "second": "password123"
    }
  }
}
```

**Response:** User created, redirect to login

---

#### GET /logout
Logout current user. Requires authentication.

---

### Course Endpoints

#### GET /teacher/courses
List all courses for current teacher.

**Response:**
```json
[
  {
    "id": 1,
    "title": "Mathematics",
    "description": "Basic mathematics course",
    "teacher": "teacher@example.com",
    "enrollmentCount": 25
  }
]
```

**Required Role:** ROLE_TEACHER

---

#### GET /teacher/courses/{id}
Get course details.

**Response:**
```json
{
  "id": 1,
  "title": "Mathematics",
  "description": "...",
  "teacher": {...},
  "enrollments": [...],
  "grades": [...]
}
```

---

#### POST /teacher/courses/new
Create new course.

**Request:**
```json
{
  "course": {
    "title": "Physics",
    "description": "Physics fundamentals"
  }
}
```

**Response:** Course created, redirect to show page

**Required Role:** ROLE_TEACHER

---

#### POST /teacher/courses/{id}/edit
Update course.

**Request:**
```json
{
  "course": {
    "title": "Updated Title",
    "description": "Updated description"
  }
}
```

**Required Role:** ROLE_TEACHER (owner)

---

#### POST /teacher/courses/{id}/delete
Delete course.

**Required Role:** ROLE_TEACHER (owner)

---

### Enrollment Endpoints

#### GET /student/enroll
List available courses for student.

**Response:**
```json
[
  {
    "id": 2,
    "title": "Physics",
    "description": "...",
    "teacher": "...",
    "enrollmentCount": 20,
    "isEnrolled": false
  }
]
```

**Required Role:** ROLE_STUDENT

---

#### POST /student/enroll
Enroll student in course.

**Request:**
```json
{
  "course_id": 2,
  "_token": "csrf_token"
}
```

**Required Role:** ROLE_STUDENT

---

#### POST /student/drop
Drop course enrollment.

**Request:**
```json
{
  "enrollment_id": 5,
  "_token": "csrf_token"
}
```

**Required Role:** ROLE_STUDENT

---

### Grade Endpoints

#### GET /teacher/grades
List all grades for courses taught.

**Response:**
```json
[
  {
    "id": 1,
    "student": "student@example.com",
    "course": "Mathematics",
    "value": 18,
    "type": "exam",
    "coefficient": 2,
    "createdAt": "2025-01-15T10:30:00Z"
  }
]
```

**Required Role:** ROLE_TEACHER

---

#### POST /teacher/grades/add
Add grade to student.

**Request:**
```json
{
  "grade": {
    "student": 1,
    "course": 1,
    "value": 16,
    "type": "homework",
    "coefficient": 1
  }
}
```

**Required Role:** ROLE_TEACHER (course owner)

---

#### POST /teacher/grades/{id}/edit
Update grade.

**Request:**
```json
{
  "grade": {
    "value": 17,
    "type": "exam",
    "coefficient": 2
  }
}
```

---

#### POST /teacher/grades/{id}/delete
Delete grade.

**Required Role:** ROLE_TEACHER (course owner)

---

### Statistics Endpoints

#### GET /teacher/statistics
List statistics for all courses.

**Response:**
```json
{
  "courseStats": [
    {
      "course": {...},
      "studentCount": 25,
      "ranking": [
        {
          "student": "...",
          "average": 18.5
        }
      ]
    }
  ]
}
```

**Required Role:** ROLE_TEACHER

---

#### GET /teacher/statistics/{courseId}
Get detailed statistics for course.

**Response:**
```json
{
  "course": {...},
  "enrollmentCount": 25,
  "gradeCount": 150,
  "gradeDistribution": [...],
  "gradesByType": {...}
}
```

---

#### GET /student/grades
Get student's grades.

**Response:**
```json
{
  "enrollments": [
    {
      "course": {...},
      "grades": [
        {
          "value": 16,
          "type": "exam",
          "coefficient": 2,
          "createdAt": "2025-01-15"
        }
      ],
      "average": 17.2
    }
  ]
}
```

**Required Role:** ROLE_STUDENT

---

### PDF Endpoints

#### GET /pdf/bulletin/{courseId}
Download grade bulletin PDF for student in specific course.

**Response:** PDF file (attachment)

**Required Role:** ROLE_STUDENT (must be enrolled in course)

**Response Headers:**
```
Content-Type: application/pdf
Content-Disposition: attachment; filename="bulletin.pdf"
```

**Example:**
```bash
curl -u student0@school.test:password http://localhost:8000/pdf/bulletin/1
```

---

#### GET /pdf/bulletin/{courseId}/view
View grade bulletin PDF in browser (inline).

**Response:** PDF file (inline)

**Required Role:** ROLE_STUDENT (must be enrolled in course)

**Response Headers:**
```
Content-Type: application/pdf
Content-Disposition: inline; filename="bulletin.pdf"
```

---

#### GET /pdf/course-report/{courseId}
Download course report PDF for teacher.

**Response:** PDF file (attachment) with:
- Course title and info
- All enrolled students with grades
- Students ranked by average grade
- Grade statistics

**Required Role:** ROLE_TEACHER (must own course)

**Response Headers:**
```
Content-Type: application/pdf
Content-Disposition: attachment; filename="course_report.pdf"
```

**Example:**
```bash
curl -u teacher0@school.test:password http://localhost:8000/pdf/course-report/1
```

---

#### GET /pdf/course-report/{courseId}/view
View course report PDF in browser (inline).

**Response:** PDF file (inline)

**Required Role:** ROLE_TEACHER (must own course)

**Response Headers:**
```
Content-Type: application/pdf
Content-Disposition: inline; filename="course_report.pdf"
```

---

## PDF Content Format

### Student Bulletin PDF
- **Header**: Course title and student information
- **Student Info**: Name, Email, Class Ranking
- **Grades Table**: All grades with type, value, coefficient
- **Summary**: Weighted average score
- **Footer**: Generated timestamp and auto-signature

### Course Report PDF
- **Header**: Course title and report metadata
- **Course Info**: Teacher name, total students, total grades
- **Student Rankings**: All students sorted by average grade descending
- **Details per Student**:
  - Student name and email
  - Average grade
  - Total number of grades
- **Statistics**: Class average, grade distribution
- **Footer**: Generated timestamp

---

### Error Responses

#### 401 Unauthorized
```json
{
  "error": "Authentication required"
}
```

#### 403 Forbidden
```json
{
  "error": "You do not have permission to access this resource"
}
```

#### 404 Not Found
```json
{
  "error": "Resource not found"
}
```

#### 422 Validation Error
```json
{
  "errors": {
    "field": "Error message"
  }
}
```

---

## Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 204 | No Content |
| 301 | Moved Permanently |
| 302 | Found (Redirect) |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Unprocessable Entity |
| 500 | Server Error |

