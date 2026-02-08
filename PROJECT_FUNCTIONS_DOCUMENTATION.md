# School Management Application - Complete Functions Documentation

> **Last Updated:** January 7, 2026  
> **Project Type:** Symfony 6.4 School Management System  
> **Database:** Doctrine ORM with MySQL

---

## 📑 Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Entity & Database Layer](#entity--database-layer)
3. [Repository Layer](#repository-layer)
4. [Service Layer](#service-layer)
5. [Controller Layer & Routes](#controller-layer--routes)
6. [Form Layer](#form-layer)
7. [Template/View Layer](#templateview-layer)
8. [How It Works: Request Flow](#how-it-works-request-flow)
9. [Complete Function Mapping](#complete-function-mapping)

---

## 🏗️ Architecture Overview

### Technology Stack
- **Framework:** Symfony 6.4
- **Database:** MySQL with Doctrine ORM
- **Frontend:** Twig Templates + Bootstrap 5
- **PDF Generation:** DOMPDF
- **JavaScript:** Stimulus Controllers for interactivity
- **Authentication:** Symfony Security (Form-based)

### Directory Structure
```
src/
├── Controller/           # Route handlers & request logic
├── Entity/               # Database models
├── Repository/           # Database queries
├── Service/              # Business logic
├── Form/                 # Form types
├── Security/             # Auth & authorization
├── Validator/            # Custom validators
└── EventListener/        # Event handlers

templates/
├── base.html.twig        # Master layout
├── home/                 # Public pages
├── security/             # Auth pages
├── student/              # Student-specific pages
├── teacher/              # Teacher-specific pages
├── pdf/                  # PDF templates
└── components/           # Reusable components

assets/
├── controllers/          # Stimulus JS controllers
├── styles/               # CSS/SCSS
└── controllers.json      # Stimulus config
```

---

## 📊 Entity & Database Layer

### 1. **User Entity** (`src/Entity/User.php`)

**Purpose:** Represents users (Students & Teachers)

**Database Fields:**
| Field | Type | Description |
|-------|------|-------------|
| `id` | Integer (PK) | Unique identifier |
| `email` | String (unique) | User email, used for login |
| `password` | String | Encrypted password |
| `name` | String (nullable) | User's full name |
| `roles` | JSON Array | User roles (`ROLE_STUDENT`, `ROLE_TEACHER`, `ROLE_USER`) |

**Relationships:**
- `enrollments` (OneToMany) → Student enrolls in many courses
- `courses` (OneToMany) → Teacher creates many courses

**Key Methods:**
```php
getId(): ?int                                    // Get user ID
getEmail(): string                              // Get email (username)
setEmail(string $email): self                   // Set email
getPassword(): string                           // Get encrypted password
setPassword(string $password): self             // Set password
getRoles(): array                               // Get user roles
setRoles(array $roles): self                    // Set user roles
getUserIdentifier(): string                     // Returns email for auth
getName(): ?string                              // Get display name
setName(?string $name): self                    // Set display name
getEnrollments(): Collection                    // Get all enrollments
getCourses(): Collection                        // Get all courses (for teachers)
```

---

### 2. **Course Entity** (`src/Entity/Course.php`)

**Purpose:** Represents courses created by teachers

**Database Fields:**
| Field | Type | Description |
|-------|------|-------------|
| `id` | Integer (PK) | Unique identifier |
| `title` | String | Course name/title |
| `description` | Text (nullable) | Course details |
| `teacher` | Foreign Key → User | Course instructor |

**Relationships:**
- `teacher` (ManyToOne) → One teacher creates course
- `enrollments` (OneToMany) → Many students enroll
- `grades` (OneToMany) → Many grades in course

**Key Methods:**
```php
getId(): ?int                                    // Get course ID
getTitle(): string                              // Get course title
setTitle(string $title): self                   // Set course title
getDescription(): ?string                       // Get course description
setDescription(?string $description): self      // Set description
getTeacher(): ?User                             // Get course instructor
setTeacher(?User $teacher): self                // Set instructor
getEnrollments(): Collection                    // Get all student enrollments
getGrades(): Collection                         // Get all grades
addGrade(Grade $grade): self                    // Add a grade
```

---

### 3. **Enrollment Entity** (`src/Entity/Enrollment.php`)

**Purpose:** Represents student enrollment in courses (M:M junction)

**Database Fields:**
| Field | Type | Description |
|-------|------|-------------|
| `id` | Integer (PK) | Unique identifier |
| `student` | Foreign Key → User | Student enrolled |
| `course` | Foreign Key → Course | Course enrolled in |
| `enrolledAt` | DateTime | When student enrolled |

**Relationships:**
- `student` (ManyToOne) → User who enrolled
- `course` (ManyToOne) → Course enrolled in

**Key Methods:**
```php
getId(): ?int                                    // Get enrollment ID
getStudent(): ?User                             // Get enrolled student
setStudent(?User $student): self                // Set student
getCourse(): ?Course                            // Get course
setCourse(?Course $course): self                // Set course
getEnrolledAt(): DateTimeInterface              // Get enrollment date
setEnrolledAt(DateTimeInterface $enrolledAt)    // Set enrollment date
```

---

### 4. **Grade Entity** (`src/Entity/Grade.php`)

**Purpose:** Represents student grades in courses

**Database Fields:**
| Field | Type | Description |
|-------|------|-------------|
| `id` | Integer (PK) | Unique identifier |
| `value` | Float | Grade value (0-20 scale) |
| `type` | String | Grade type (exam, assignment, participation, project) |
| `coefficient` | Integer | Weight multiplier for average calculation |
| `student` | Foreign Key → User | Student graded |
| `course` | Foreign Key → Course | Course of grade |
| `createdAt` | DateTime | When grade was created |

**Relationships:**
- `student` (ManyToOne) → User receiving grade
- `course` (ManyToOne) → Course grade is for

**Key Methods:**
```php
getId(): ?int                                    // Get grade ID
getValue(): float                               // Get numeric grade value
setValue(float $value): self                    // Set grade value
getType(): string                               // Get grade type
setType(string $type): self                     // Set grade type
getCoefficient(): int                           // Get coefficient
setCoefficient(int $coefficient): self          // Set coefficient
getStudent(): ?User                             // Get graded student
setStudent(?User $student): self                // Set student
getCourse(): ?Course                            // Get course
setCourse(?Course $course): self                // Set course
getCreatedAt(): DateTimeInterface               // Get creation date
```

---

## 🔍 Repository Layer

### Purpose
Repositories handle database queries using Doctrine ORM Query Builder.

### 1. **UserRepository** (`src/Repository/UserRepository.php`)

**Class:** `ServiceEntityRepository<User>`

**Methods:**
```php
// Default methods (inherited from Doctrine)
findOneBy(array $criteria): ?User               // Find single user by criteria
findBy(array $criteria): array                  // Find multiple users
findAll(): array                                // Get all users
count(array $criteria): int                     // Count users matching criteria
```

**Usage Example:**
```php
// Find user by email
$user = $userRepository->findOneBy(['email' => 'student@example.com']);

// Find all users with role
$students = $userRepository->findBy(['roles' => 'ROLE_STUDENT']);
```

---

### 2. **CourseRepository** (`src/Repository/CourseRepository.php`)

**Class:** `ServiceEntityRepository<Course>`

**Custom Methods:**

```php
/**
 * Find all courses for a given student (courses they're enrolled in)
 */
public function findCoursesForStudent(User $student): array
```
**Query Logic:**
- Joins with enrollments table
- Filters where enrollment student matches given user
- Sorts by course title alphabetically
- Returns array of Course objects

**Example Usage:**
```php
$enrolledCourses = $courseRepository->findCoursesForStudent($currentUser);
// Returns: [Course1, Course2, Course3]
```

---

```php
/**
 * Find available courses for a student (not yet enrolled)
 */
public function findAvailableForStudent(User $student): array
```
**Query Logic:**
- Left joins with enrollments (to check non-enrollments)
- Filters where enrollment is NULL (not enrolled)
- Sorts by title
- Returns array of available courses

**Example Usage:**
```php
$availableCourses = $courseRepository->findAvailableForStudent($student);
```

---

```php
/**
 * Find all courses taught by a teacher
 */
public function findByTeacher(User $teacher): array
```
**Query Logic:**
- Filters courses where teacher = given user
- Sorts by title
- Returns array of Course objects

**Example Usage:**
```php
$myCourses = $courseRepository->findByTeacher($currentTeacher);
```

---

### 3. **GradeRepository** (`src/Repository/GradeRepository.php`)

**Class:** `ServiceEntityRepository<Grade>`

**Custom Methods:**

```php
/**
 * Find all grades for a student
 */
public function findByStudent(User $student): array
```
**Returns:** All grades where student matches, ordered by creation date (newest first)

---

```php
/**
 * Find all grades in a course
 */
public function findByCourse(Course $course): array
```
**Returns:** All grades in specific course, ordered by creation date

---

```php
/**
 * Find grades for a student in a course
 */
public function findByStudentAndCourse(User $student, Course $course): array
```
**Returns:** All grades for specific student in specific course

---

```php
/**
 * Find grades by type in a course
 */
public function findByCourseAndType(Course $course, string $type): array
```
**Parameters:** `$type` can be: 'exam', 'assignment', 'participation', 'project'

---

```php
/**
 * Find average grade for a student in a course with weighted coefficient
 */
public function findAverageByStudentAndCourse(User $student, Course $course): ?float
```
**Calculation:** (sum of grades * coefficients) / sum of coefficients

---

### 4. **StatisticRepository** (`src/Repository/StatisticRepository.php`)

**Custom Methods:**

```php
/**
 * Calculate average grade for a student in a course
 */
public function calculateAverageGrade(User $student, Course $course): ?float
```

---

```php
/**
 * Get ranked students in a course by average grade
 */
public function getRankedStudentsByCourse(Course $course): array
```
**Returns:** Array of students sorted by average grade (descending)

```php
[
    ['student_id' => 1, 'name' => 'John', 'email' => 'john@x.com', 'average' => 18.5, 'grade_count' => 5],
    ['student_id' => 2, 'name' => 'Jane', 'email' => 'jane@x.com', 'average' => 17.2, 'grade_count' => 4],
]
```

---

### 5. **EnrollmentRepository** (`src/Repository/EnrollmentRepository.php`)

**Custom Methods:**

```php
/**
 * Find all enrollments for a student
 */
public function findByStudent(User $student): array
```

---

## ⚙️ Service Layer

### Purpose
Services contain business logic and are reusable across controllers.

### 1. **GradeService** (`src/Service/GradeService.php`)

**Constructor Injection:**
```php
public function __construct(
    private EntityManagerInterface $em,
    private GradeRepository $gradeRepository,
) {}
```

**Constants:**
```php
GRADE_MIN = 0          // Minimum grade value
GRADE_MAX = 20         // Maximum grade value
VALID_TYPES = ['exam', 'assignment', 'participation', 'project']
```

**Public Methods:**

```php
/**
 * Add a grade for a student
 * 
 * @param User $student The student receiving grade
 * @param Course $course The course
 * @param float $value Grade value (0-20)
 * @param string $type Grade type (default: 'exam')
 * @param int $coefficient Weight multiplier (default: 1)
 * @return Grade The created grade object
 */
public function addGrade(
    User $student, 
    Course $course, 
    float $value, 
    string $type = 'exam', 
    int $coefficient = 1
): Grade
```

**Validation:** Calls validateGradeValue(), validateGradeType(), validateCoefficient()

**Database Impact:** Persists and flushes new Grade to database

---

```php
/**
 * Update an existing grade
 */
public function updateGrade(
    Grade $grade, 
    float $value, 
    ?string $type = null, 
    ?int $coefficient = null
): void
```

**Behavior:** Only updates provided fields, leaves others unchanged

---

```php
/**
 * Delete a grade
 */
public function deleteGrade(Grade $grade): void
```

---

```php
/**
 * Get all grades for a student in a course
 */
public function getGradesByStudentAndCourse(User $student, Course $course): array
```

---

```php
/**
 * Get all grades in a course
 */
public function getGradesByCourse(Course $course): array
```

---

```php
/**
 * Get average grade for a student in a course
 */
public function getAverageGrade(User $student, Course $course): ?float
```

**Calculation:** Weighted average based on coefficients

---

### 2. **StatisticService** (`src/Service/StatisticService.php`)

**Constructor Injection:**
```php
public function __construct(
    private GradeRepository $gradeRepository,
    private StatisticRepository $statisticRepository,
) {}
```

**Public Methods:**

```php
/**
 * Calculate average grade for a student in a course (weighted by coefficient)
 */
public function calculateAverageForStudentInCourse(User $student, Course $course): float
```

**Returns:** Float (0.00 format), or 0 if no grades exist

---

```php
/**
 * Calculate overall average for a student across all courses
 */
public function calculateOverallAverage(User $student): float
```

**Calculation:**
```
Total = Sum(Grade.value * Grade.coefficient) for all grades
Overall Average = Total / Sum(all coefficients)
```

---

```php
/**
 * Get course ranking (students sorted by average grade)
 */
public function getCourseRanking(Course $course): array
```

**Returns:**
```php
[
    [
        'rank' => 1,
        'student_id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'average' => 18.50,
        'grade_count' => 5
    ],
    // ... more students
]
```

---

```php
/**
 * Get student's ranking position in a course
 */
public function getStudentRankingPosition(User $student, Course $course): ?array
```

**Returns:** Same structure as single ranking entry, or NULL if not ranked

---

```php
/**
 * Get grades by type (exam, homework, etc)
 */
public function getGradesByType(User $student, Course $course, string $type): array
```

---

### 3. **EnrollmentService** (`src/Service/EnrollmentService.php`)

**Public Methods:**

```php
/**
 * Enroll a student in a course
 */
public function enrollStudent(User $student, Course $course): Enrollment
```

**Behavior:**
- Checks if already enrolled (throws exception if yes)
- Creates new Enrollment with current timestamp
- Persists to database

---

```php
/**
 * Drop a student from a course
 */
public function dropStudent(User $student, Course $course): void
```

**Behavior:**
- Finds enrollment record
- Deletes it
- Flushes to database

---

### 4. **PdfGeneratorService** (`src/Service/PdfGeneratorService.php`)

**Constructor Injection:**
```php
public function __construct(
    private Environment $twig,
    private StatisticRepository $statisticRepository
) {}
```

**Public Methods:**

```php
/**
 * Generate a student bulletin (grade report) PDF
 * 
 * @param User $student The student
 * @param Course $course The course
 * @return string PDF content (binary)
 */
public function generateBulletin(User $student, Course $course): string
```

**Data Preparation:**
1. Filters all grades for student in course
2. Calculates average using StatisticRepository
3. Gets ranked students to find rank
4. Renders `pdf/bulletin.html.twig` template
5. Converts HTML to PDF using DOMPDF

**Template Context Variables:**
- `student`: The student object
- `course`: The course object
- `grades`: Collection of grades
- `average`: Weighted average grade
- `rank`: Student's rank in class
- `totalStudents`: Number of enrolled students
- `generatedAt`: DateTime of generation

---

```php
/**
 * Generate a course report (teacher view) PDF
 * 
 * @param Course $course The course
 * @return string PDF content (binary)
 */
public function generateCourseReport(Course $course): string
```

**Data Preparation:**
1. Iterates all enrollments
2. Calculates average for each student
3. Counts grades per student
4. Sorts by average grade (descending)
5. Renders `pdf/course_report.html.twig` template

**Template Context Variables:**
- `course`: The course object
- `gradeDistribution`: Array of student performance data
- `totalStudents`: Number of enrolled students
- `generatedAt`: DateTime of generation

---

```php
/**
 * Convert HTML to PDF (private helper)
 */
private function renderToPdf(string $html, string $filename): string
```

**DOMPDF Configuration:**
```php
[
    'isRemoteEnabled' => false,        // No external resources
    'isHtml5ParserEnabled' => true,    // Support HTML5
    'defaultFont' => 'Helvetica',
    'dpi' => 96,
    'paperSize' => 'A4',
    'orientation' => 'portrait'
]
```

---

## 🛣️ Controller Layer & Routes

### 1. **HomeController** (`src/Controller/HomeController.php`)

**Route Base:** `/` (Root)

#### Route: Home Index
```php
#[Route('/', name: 'home')]
public function index(
    CourseRepository $courseRepository, 
    UserRepository $userRepository
): Response
```

**HTTP Method:** GET

**Access:** Public (anonymous users)

**Behavior:**
1. Checks if user is authenticated
2. If authenticated & has ROLE_TEACHER → redirects to teacher dashboard
3. If authenticated & has ROLE_STUDENT → redirects to student dashboard
4. If anonymous → shows homepage with stats

**Template:** `home/index.html.twig`

**Template Variables:**
- `total_courses`: Count of all courses
- `total_users`: Count of all users
- `recent_courses`: Last 6 created courses

---

### 2. **SecurityController** (`src/Controller/Security/SecurityController.php`)

**Route Base:** `/` (Root)

#### Route: Login
```php
#[Route('/login', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
```

**HTTP Method:** GET, POST

**Access:** Public (anonymous only)

**Behavior:**
1. Gets last authentication error (if exists)
2. Gets last entered username
3. Renders login form

**Template:** `security/login.html.twig`

**Template Variables:**
- `last_username`: Last attempted email
- `error`: AuthenticationException (if failed)

**Security:** Symfony handles POST via security firewall

---

#### Route: Logout
```php
#[Route('/logout', name: 'app_logout')]
public function logout(): void
```

**HTTP Method:** GET (intercepted by firewall)

**Access:** Authenticated users only

**Behavior:** Session destroyed by security firewall, redirects to login

---

#### Route: Register
```php
#[Route('/register', name: 'app_register')]
public function register(
    Request $request,
    UserPasswordHasherInterface $passwordHasher,
    EntityManagerInterface $em
): Response
```

**HTTP Method:** GET, POST

**Access:** Public (anonymous only)

**Behavior on GET:**
1. Creates new User entity
2. Creates RegistrationType form
3. Renders form

**Behavior on POST:**
1. Handles form submission
2. Validates input
3. Hashes password using UserPasswordHasher
4. Sets role to ROLE_STUDENT (default)
5. Persists and flushes to database
6. Redirects to login with success message

**Template:** `security/register.html.twig`

**Template Variables:**
- `form`: RegistrationType form view

---

### 3. **ProfileController** (`src/Controller/ProfileController.php`)

**Route Base:** `/profile`

**Authentication Required:** ROLE_USER (all authenticated users)

#### Route: View Profile
```php
#[Route('', name: 'app_profile')]
public function index(): Response
```

**HTTP Method:** GET

**Behavior:**
1. Gets current authenticated user
2. Determines if user has ROLE_STUDENT
3. Determines if user has ROLE_TEACHER
4. Renders profile page

**Template:** `profile/index.html.twig`

**Template Variables:**
- `user`: Current authenticated user
- `isStudent`: Boolean flag
- `isTeacher`: Boolean flag

---

#### Route: Edit Profile
```php
#[Route('/edit', name: 'app_profile_edit')]
public function edit(Request $request, EntityManagerInterface $em): Response
```

**HTTP Method:** GET, POST

**Behavior on GET:**
1. Creates EditProfileType form with current user data
2. Renders edit form

**Behavior on POST:**
1. Handles form submission
2. Validates changes
3. Persists and flushes to database
4. Sets success flash message
5. Redirects to profile view

**Template:** `profile/edit.html.twig`

**Template Variables:**
- `form`: EditProfileType form view
- `user`: Current user

---

### 4. **Student\DashboardController** (`src/Controller/Student/DashboardController.php`)

**Route Base:** `/student`

**Authentication Required:** ROLE_STUDENT

#### Route: Dashboard
```php
#[Route('/dashboard', name: 'app_student_dashboard')]
public function index(EnrollmentRepository $enrollmentRepository): Response
```

**HTTP Method:** GET

**Behavior:**
1. Gets current student user
2. Queries enrollments using repository
3. Renders dashboard with enrolled courses

**Template:** `student/dashboard.html.twig`

**Template Variables:**
- `enrollments`: Array of Enrollment objects

---

### 5. **Student\EnrollmentController** (`src/Controller/Student/EnrollmentController.php`)

**Route Base:** `/student/enrollments`

**Authentication Required:** ROLE_STUDENT

#### Route: List Available Courses
```php
#[Route('/available', name: 'app_enrollment_available', methods: ['GET'])]
public function available(CourseRepository $courseRepository): Response
```

**HTTP Method:** GET

**Behavior:**
1. Gets current student
2. Queries available courses (not yet enrolled)
3. Renders list of available courses

**Template:** `student/enrollment/available.html.twig`

**Template Variables:**
- `courses`: Array of available Course objects

---

#### Route: Enroll in Course
```php
#[Route('/{courseId}/enroll', name: 'app_enrollment_enroll', methods: ['POST'])]
public function enroll(
    int $courseId,
    CourseRepository $courseRepository,
    EnrollmentService $enrollmentService,
    Request $request
): Response
```

**HTTP Method:** POST

**Behavior:**
1. Validates CSRF token with key `enroll{courseId}`
2. Retrieves course by ID
3. Calls `EnrollmentService->enrollStudent()`
4. Sets success/error flash message
5. Redirects to available courses list

**Error Handling:**
- Invalid CSRF → error flash + redirect
- Course not found → 404 exception
- Already enrolled → error flash (from service)

---

#### Route: Drop Course
```php
#[Route('/{courseId}/drop', name: 'app_enrollment_drop', methods: ['POST'])]
public function drop(
    int $courseId,
    CourseRepository $courseRepository,
    EnrollmentService $enrollmentService,
    Request $request
): Response
```

**HTTP Method:** POST

**Behavior:**
1. Validates CSRF token with key `drop{courseId}`
2. Retrieves course by ID
3. Calls `EnrollmentService->dropStudent()`
4. Sets success/error flash message
5. Redirects to student dashboard

---

### 6. **Student\GradeController** (`src/Controller/Student/GradeController.php`)

**Route Base:** `/student/grades`

**Authentication Required:** ROLE_STUDENT

#### Route: My Grades
```php
#[Route('/my-grades', name: 'app_student_grade_my_grades', methods: ['GET'])]
public function myGrades(
    GradeService $gradeService,
    EnrollmentRepository $enrollmentRepository
): Response
```

**HTTP Method:** GET

**Behavior:**
1. Gets current student's enrollments
2. Iterates each enrollment to get grades
3. Renders grade listing

**Template:** `student/grade/my_grades.html.twig`

**Template Variables:**
- `enrollments`: Student's enrollments with grades

---

#### Route: Course Grades
```php
#[Route('/course/{courseId}', name: 'app_student_grade_course', methods: ['GET'])]
public function courseGrades(
    int $courseId,
    CourseRepository $courseRepository,
    GradeService $gradeService
): Response
```

**HTTP Method:** GET

**Behavior:**
1. Retrieves course by ID
2. Verifies student is enrolled (throws 403 if not)
3. Gets grades for student in course
4. Calculates average
5. Renders course-specific grades

**Template:** `student/grade/course_grades.html.twig`

**Template Variables:**
- `course`: Course object
- `grades`: Grades in course
- `average`: Weighted average

---

#### Route: Statistics
```php
#[Route('/statistics', name: 'app_student_grade_statistics', methods: ['GET'])]
public function statistics(
    GradeService $gradeService,
    StatisticService $statisticService
): Response
```

**HTTP Method:** GET

**Behavior:**
1. Calculates overall average across all courses
2. Gets ranking in each course
3. Renders statistics dashboard

**Template:** `student/grade/statistics.html.twig`

**Template Variables:**
- `overallAverage`: Student's overall average
- `courseRankings`: Rankings in each course

---

### 7. **Teacher\CourseController** (`src/Controller/Teacher/CourseController.php`)

**Route Base:** `/teacher/courses`

**Authentication Required:** ROLE_TEACHER

#### Route: List Courses
```php
#[Route('', name: 'app_course_index', methods: ['GET'])]
public function index(CourseRepository $courseRepository): Response
```

**HTTP Method:** GET

**Behavior:**
1. Gets current teacher
2. Queries all courses taught by teacher
3. Renders course list

**Template:** `teacher/course/index.html.twig`

**Template Variables:**
- `courses`: Array of teacher's courses

---

#### Route: Create Course
```php
#[Route('/new', name: 'app_course_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $em): Response
```

**HTTP Method:** GET, POST

**Behavior on GET:**
1. Creates new Course entity
2. Creates CourseType form
3. Renders form

**Behavior on POST:**
1. Handles form submission
2. Sets teacher to current user
3. Persists and flushes
4. Sets success flash message
5. Redirects to course list

**Template:** `teacher/course/new.html.twig`

**Template Variables:**
- `form`: CourseType form view

---

#### Route: View Course
```php
#[Route('/{id}', name: 'app_course_show', methods: ['GET'])]
public function show(Course $course): Response
```

**HTTP Method:** GET

**Behavior:**
1. Verifies teacher owns course via voter (VIEW permission)
2. Renders course details

**Template:** `teacher/course/show.html.twig`

**Template Variables:**
- `course`: Course object with enrollments and grades

---

#### Route: Edit Course
```php
#[Route('/{id}/edit', name: 'app_course_edit', methods: ['GET', 'POST'])]
public function edit(
    Request $request, 
    Course $course, 
    EntityManagerInterface $em
): Response
```

**HTTP Method:** GET, POST

**Behavior:**
1. Verifies EDIT permission via voter
2. On GET: Renders form with current data
3. On POST: Validates and updates database

**Template:** `teacher/course/edit.html.twig`

**Template Variables:**
- `form`: CourseType form view
- `course`: Course object

---

#### Route: Delete Course
```php
#[Route('/{id}/delete', name: 'app_course_delete', methods: ['POST'])]
public function delete(
    Request $request, 
    Course $course, 
    EntityManagerInterface $em
): Response
```

**HTTP Method:** POST

**Behavior:**
1. Verifies DELETE permission via voter
2. Validates CSRF token
3. Removes course and cascades to enrollments/grades
4. Sets success flash message
5. Redirects to course list

---

### 8. **Teacher\GradeController** (`src/Controller/Teacher/GradeController.php`)

**Route Base:** `/teacher/grades`

**Authentication Required:** ROLE_TEACHER

#### Route: List All Grades
```php
#[Route('', name: 'app_grade_index', methods: ['GET'])]
public function index(Request $request): Response
```

**HTTP Method:** GET

**Query Parameters:**
- `?course=ID` → Filter by course
- `?type=exam` → Filter by grade type
- `?sort=created` → Sort by: created, student, value, type

**Behavior:**
1. Gets teacher's courses
2. Applies filters if provided
3. Sorts results
4. Renders grade index

**Template:** `teacher/grade/index.html.twig`

**Template Variables:**
- `grades`: Filtered and sorted grades
- `courses`: Teacher's courses (for filter dropdown)
- `selected_course`: Currently selected course
- `filters`: Active filter values

---

#### Route: Add Grade
```php
#[Route('/add', name: 'app_grade_add', methods: ['GET', 'POST'])]
#[Route('/course/{courseId}/add', name: 'app_grade_add_course', methods: ['GET', 'POST'])]
public function add(?int $courseId = null, Request $request): Response
```

**HTTP Method:** GET, POST

**Behavior on GET:**
1. Creates new Grade entity
2. Pre-fills course if courseId provided
3. Verifies teacher owns course (voter: ADD permission)
4. Creates GradeType form
5. Renders form

**Behavior on POST:**
1. Validates form
2. Calls `GradeService->addGrade()`
3. Sets success flash message
4. Redirects to grade index

**Template:** `teacher/grade/add.html.twig`

**Template Variables:**
- `form`: GradeType form view
- `course`: Pre-selected course (if provided)

---

#### Route: Edit Grade
```php
#[Route('/{id}/edit', name: 'app_grade_edit', methods: ['GET', 'POST'])]
public function edit(Grade $grade, Request $request): Response
```

**HTTP Method:** GET, POST

**Behavior:**
1. Verifies EDIT permission via voter
2. On GET: Renders edit form
3. On POST: Validates and calls `GradeService->updateGrade()`

**Template:** `teacher/grade/edit.html.twig`

**Template Variables:**
- `form`: GradeType form view
- `grade`: Grade object

---

#### Route: Delete Grade
```php
#[Route('/{id}/delete', name: 'app_grade_delete', methods: ['POST'])]
public function delete(Grade $grade, Request $request): Response
```

**HTTP Method:** POST

**Behavior:**
1. Verifies DELETE permission via voter
2. Validates CSRF token
3. Calls `GradeService->deleteGrade()`
4. Sets success flash message
5. Redirects to grade index

---

#### Route: View Course Grades
```php
#[Route('/course/{courseId}', name: 'app_grade_course_view', methods: ['GET'])]
public function viewCourse(
    int $courseId,
    CourseRepository $courseRepository,
    GradeRepository $gradeRepository
): Response
```

**HTTP Method:** GET

**Behavior:**
1. Retrieves course by ID
2. Verifies teacher owns course
3. Gets all grades in course
4. Calculates statistics
5. Renders detailed view

**Template:** `teacher/grade/course_view.html.twig`

**Template Variables:**
- `course`: Course object
- `grades`: All grades in course
- `statistics`: Course statistics

---

#### Route: Export Grades
```php
#[Route('/export', name: 'app_grade_export', methods: ['GET'])]
public function export(
    GradeRepository $gradeRepository,
    Request $request
): Response
```

**HTTP Method:** GET

**Query Parameters:**
- `?course=ID` → Export specific course
- `?format=csv` or `?format=pdf` → Export format

**Behavior:**
1. Gets teacher's grades based on filters
2. Generates CSV/PDF export
3. Returns file download response

---

### 9. **Teacher\StatisticController** (`src/Controller/Teacher/StatisticController.php`)

**Route Base:** `/teacher/statistics`

**Authentication Required:** ROLE_TEACHER

#### Route: Dashboard
```php
#[Route('', name: 'app_statistic_index', methods: ['GET'])]
public function index(StatisticService $statisticService): Response
```

**HTTP Method:** GET

**Behavior:**
1. Gets teacher's courses
2. Calculates statistics for each course
3. Renders statistics dashboard

**Template:** `teacher/statistic/index.html.twig`

**Template Variables:**
- `courses`: Teacher's courses with statistics

---

#### Route: Course Statistics
```php
#[Route('/course/{courseId}', name: 'app_statistic_course', methods: ['GET'])]
public function course(
    int $courseId,
    CourseRepository $courseRepository,
    StatisticService $statisticService
): Response
```

**HTTP Method:** GET

**Behavior:**
1. Retrieves course
2. Verifies teacher owns course
3. Gets course ranking
4. Calculates distribution statistics
5. Renders detailed course statistics

**Template:** `teacher/statistic/course.html.twig`

**Template Variables:**
- `course`: Course object
- `ranking`: Students ranked by average
- `statistics`: Distribution stats

---

#### Route: Student Course Statistics
```php
#[Route('/student/{studentId}/course/{courseId}', name: 'app_statistic_student_course', methods: ['GET'])]
public function studentCourse(
    int $studentId,
    int $courseId,
    UserRepository $userRepository,
    CourseRepository $courseRepository,
    StatisticService $statisticService
): Response
```

**HTTP Method:** GET

**Behavior:**
1. Retrieves student and course
2. Verifies teacher owns course
3. Gets student's grades in course
4. Calculates individual statistics
5. Renders student performance

**Template:** `teacher/statistic/student_course.html.twig`

**Template Variables:**
- `student`: Student object
- `course`: Course object
- `grades`: Student's grades
- `statistics`: Performance stats

---

#### Route: Export Statistics
```php
#[Route('/export', name: 'app_statistic_export', methods: ['GET'])]
public function export(Request $request): Response
```

**HTTP Method:** GET

**Behavior:**
1. Exports statistics as CSV/PDF
2. Returns file download

---

### 10. **Shared\PdfController** (`src/Controller/Shared/PdfController.php`)

**Route Base:** `/pdf`

#### Route: Student Bulletin Download
```php
#[Route('/bulletin/{courseId}', name: 'pdf_student_bulletin')]
#[IsGranted('ROLE_STUDENT')]
public function studentBulletin(
    int $courseId,
    CourseRepository $courseRepository,
    PdfGeneratorService $pdfGenerator
): Response
```

**HTTP Method:** GET

**Access:** Students only

**Behavior:**
1. Retrieves course
2. Verifies student is enrolled
3. Generates bulletin PDF via PdfGeneratorService
4. Returns PDF as file download (attachment)

**Response Headers:**
```
Content-Type: application/pdf
Content-Disposition: attachment; filename="bulletin.pdf"
```

---

#### Route: Student Bulletin View
```php
#[Route('/bulletin/{courseId}/view', name: 'pdf_student_bulletin_view')]
#[IsGranted('ROLE_STUDENT')]
public function studentBulletinView(
    int $courseId,
    CourseRepository $courseRepository,
    PdfGeneratorService $pdfGenerator
): Response
```

**HTTP Method:** GET

**Access:** Students only

**Behavior:** Same as above, but returns PDF as inline view (in browser)

**Response Headers:**
```
Content-Type: application/pdf
Content-Disposition: inline; filename="bulletin.pdf"
```

---

#### Route: Course Report Download
```php
#[Route('/course-report/{courseId}', name: 'pdf_course_report')]
#[IsGranted('ROLE_TEACHER')]
public function courseReport(
    int $courseId,
    CourseRepository $courseRepository,
    PdfGeneratorService $pdfGenerator
): Response
```

**HTTP Method:** GET

**Access:** Teachers only (must own course)

**Behavior:**
1. Retrieves course
2. Verifies teacher owns course
3. Generates course report PDF
4. Returns as attachment (download)

---

#### Route: Course Report View
```php
#[Route('/course-report/{courseId}/view', name: 'pdf_course_report_view')]
#[IsGranted('ROLE_TEACHER')]
public function courseReportView(
    int $courseId,
    CourseRepository $courseRepository,
    PdfGeneratorService $pdfGenerator
): Response
```

**HTTP Method:** GET

**Access:** Teachers only (must own course)

**Behavior:** Same as above, but inline view

---

#### Route: Testing Dashboard
```php
#[Route('/testing-dashboard', name: 'testing_dashboard')]
public function testingDashboard(): Response
```

**HTTP Method:** GET

**Access:** Public

**Template:** `pdf/testing_dashboard.html.twig`

**Purpose:** Testing dashboard for PDF generation functionality

---

## 📋 Form Layer

### 1. **RegistrationType** (`src/Form/RegistrationType.php`)

**Usage Location:** `SecurityController->register()`

**Fields:**
```
email:    EmailType
password: PasswordType
name:     TextType
role:     ChoiceType ['ROLE_STUDENT', 'ROLE_TEACHER']
```

**Constraints:**
- Email: Required, Valid email format, Unique in database
- Password: Required, Minimum 6 characters, Confirmed (repeat)
- Name: Optional, Max 100 characters

---

### 2. **CourseType** (`src/Form/CourseType.php`)

**Usage Location:**
- `CourseController->new()`
- `CourseController->edit()`

**Fields:**
```
title:       TextType
description: TextareaType
```

**Constraints:**
- Title: Required, Min 3 characters, Max 255
- Description: Optional, Max 5000 characters

---

### 3. **GradeType** (`src/Form/GradeType.php`)

**Usage Location:**
- `GradeController->add()`
- `GradeController->edit()`

**Fields:**
```
student:     EntityType (User)
course:      EntityType (Course)
value:       NumberType (0-20 scale)
type:        ChoiceType ['exam', 'assignment', 'participation', 'project']
coefficient: IntegerType (1-5 range typically)
```

**Constraints:**
- Student: Required
- Course: Required
- Value: Required, Min 0, Max 20, Must be numeric
- Type: Required, Must be valid type
- Coefficient: Required, Min 1, Max 10

---

### 4. **EditProfileType** (`src/Form/EditProfileType.php`)

**Usage Location:** `ProfileController->edit()`

**Fields:**
```
email: EmailType
name:  TextType
```

**Constraints:**
- Email: Required, Valid email, Unique (excluding self)
- Name: Required, Max 100 characters

---

## 🎨 Template/View Layer

### Template Directory Structure

```
templates/
├── base.html.twig                    # Master layout
├── home/
│   └── index.html.twig              # Public homepage
├── security/
│   ├── login.html.twig              # Login form
│   └── register.html.twig           # Registration form
├── profile/
│   ├── index.html.twig              # Profile view
│   └── edit.html.twig               # Profile edit
├── student/
│   ├── dashboard.html.twig          # Student dashboard
│   ├── enrollment/
│   │   └── available.html.twig      # Available courses
│   └── grade/
│       ├── my_grades.html.twig      # Student grades
│       ├── course_grades.html.twig  # Course-specific grades
│       └── statistics.html.twig     # Student statistics
├── teacher/
│   ├── dashboard.html.twig          # Teacher dashboard
│   ├── course/
│   │   ├── index.html.twig          # Course list
│   │   ├── new.html.twig            # Create course
│   │   ├── show.html.twig           # Course details
│   │   └── edit.html.twig           # Edit course
│   ├── grade/
│   │   ├── index.html.twig          # Grade list
│   │   ├── add.html.twig            # Add grade
│   │   ├── edit.html.twig           # Edit grade
│   │   └── course_view.html.twig    # Course grades
│   └── statistic/
│       ├── index.html.twig          # Statistics dashboard
│       ├── course.html.twig         # Course statistics
│       └── student_course.html.twig # Student performance
├── pdf/
│   ├── bulletin.html.twig           # Student bulletin template
│   ├── course_report.html.twig      # Course report template
│   └── testing_dashboard.html.twig  # PDF testing page
└── components/
    └── various_components.html.twig # Reusable parts
```

### Key Template Variables by Route

| Route | Template | Context Variables |
|-------|----------|-------------------|
| home | home/index.html.twig | `total_courses`, `total_users`, `recent_courses` |
| login | security/login.html.twig | `last_username`, `error` |
| register | security/register.html.twig | `form` |
| profile | profile/index.html.twig | `user`, `isStudent`, `isTeacher` |
| profile/edit | profile/edit.html.twig | `form`, `user` |
| student/dashboard | student/dashboard.html.twig | `enrollments` |
| student/enrollments/available | student/enrollment/available.html.twig | `courses` |
| student/grades/my-grades | student/grade/my_grades.html.twig | `enrollments` |
| student/grades/course/{id} | student/grade/course_grades.html.twig | `course`, `grades`, `average` |
| student/grades/statistics | student/grade/statistics.html.twig | `overallAverage`, `courseRankings` |
| teacher/courses | teacher/course/index.html.twig | `courses` |
| teacher/courses/new | teacher/course/new.html.twig | `form` |
| teacher/courses/{id} | teacher/course/show.html.twig | `course` |
| teacher/courses/{id}/edit | teacher/course/edit.html.twig | `form`, `course` |
| teacher/grades | teacher/grade/index.html.twig | `grades`, `courses`, `selected_course`, `filters` |
| teacher/grades/add | teacher/grade/add.html.twig | `form`, `course` |
| teacher/grades/{id}/edit | teacher/grade/edit.html.twig | `form`, `grade` |
| teacher/statistics | teacher/statistic/index.html.twig | `courses` |
| teacher/statistics/course/{id} | teacher/statistic/course.html.twig | `course`, `ranking`, `statistics` |

---

## 🔄 How It Works: Request Flow

### Example 1: Student Views Their Grades

**User Action:** Student clicks "My Grades" button on dashboard

**HTTP Request:**
```
GET /student/grades/my-grades HTTP/1.1
```

**Request Flow:**

1. **Routing Layer** (config/routes.yaml)
   - Matches URL to route named `app_student_grade_my_grades`
   - Routes to `Student\GradeController->myGrades()`

2. **Security Check** (@IsGranted)
   - Verifies user has ROLE_STUDENT
   - If not → 403 Forbidden

3. **Controller** (Student\GradeController->myGrades())
   ```php
   $enrollments = $enrollmentRepository->findByStudent($currentUser);
   return $this->render('student/grade/my_grades.html.twig', [
       'enrollments' => $enrollments,
   ]);
   ```

4. **Repository Layer** (EnrollmentRepository->findByStudent())
   ```sql
   SELECT * FROM enrollment 
   WHERE student_id = ? 
   ORDER BY enrolled_at DESC
   ```
   - Executes Doctrine Query Builder
   - Returns array of Enrollment objects (lazy-loads related Courses)

5. **Service Layer** (GradeService)
   - Not directly involved in this simple read operation

6. **Template Rendering** (Twig)
   - Loops through enrollments
   - For each enrollment, accesses Course and Grades
   - Calculates averages using GradeService->getAverageGrade()
   - Renders HTML

7. **HTTP Response**
   ```
   HTTP/1.1 200 OK
   Content-Type: text/html; charset=utf-8
   ```
   - Returns rendered HTML to browser

---

### Example 2: Teacher Adds a Grade

**User Action:** Teacher fills form and submits grade

**HTTP Request:**
```
POST /teacher/grades/course/5/add HTTP/1.1
Content-Type: application/x-www-form-urlencoded

grade[student]=3&grade[value]=18.5&grade[type]=exam&grade[coefficient]=2&_token=abc123
```

**Request Flow:**

1. **Security Checks:**
   - IsGranted('ROLE_TEACHER')
   - Voter checks: Can teacher ADD grades to this course?
   - Teacher must own course (Voter: `CourseVoter->voteOnAttribute('ADD')`)

2. **Form Handling** (GradeType)
   ```php
   $form->handleRequest($request);
   if ($form->isSubmitted() && $form->isValid()) { ... }
   ```
   - Validates CSRF token
   - Validates field types and constraints
   - If invalid → Re-renders form with errors

3. **Service Layer Call** (GradeController->add())
   ```php
   $this->gradeService->addGrade(
       student: $grade->getStudent(),      // User #3
       course: $grade->getCourse(),        // Course #5
       value: 18.5,                        // Grade value
       type: 'exam',                       // Type
       coefficient: 2                      // Weight
   )
   ```

4. **Business Logic** (GradeService->addGrade())
   ```php
   // Validation
   $this->validateGradeValue(18.5);           // ✓ Between 0-20
   $this->validateGradeType('exam');          // ✓ Valid type
   $this->validateCoefficient(2);             // ✓ Valid coefficient
   
   // Create entity
   $grade = new Grade();
   $grade->setStudent($student);
   $grade->setCourse($course);
   $grade->setValue(18.5);
   $grade->setType('exam');
   $grade->setCoefficient(2);
   $grade->setCreatedAt(new DateTime());      // Auto-set
   
   // Persist to database
   $this->em->persist($grade);
   $this->em->flush();
   ```

5. **Database Operation** (Doctrine ORM)
   ```sql
   INSERT INTO grade (student_id, course_id, value, type, coefficient, created_at)
   VALUES (3, 5, 18.5, 'exam', 2, NOW())
   ```

6. **Response**
   ```php
   $this->addFlash('success', 'Grade of 18.5 added for John Doe in Mathematics');
   return $this->redirectToRoute('app_grade_index');
   ```
   - HTTP 302 Redirect
   - Flash message stored in session
   - Redirects to grade list page

7. **Next Request** (Redirect)
   - Browser follows redirect to `/teacher/grades`
   - Gets list of all grades (now including new grade)
   - Displays flash message

---

### Example 3: Generating a PDF Report

**User Action:** Student clicks "Download Bulletin"

**HTTP Request:**
```
GET /pdf/bulletin/5 HTTP/1.1
```

**Request Flow:**

1. **Routing & Security**
   - Route: `pdf_student_bulletin`
   - Controller: `PdfController->studentBulletin($courseId=5)`
   - Security: IsGranted('ROLE_STUDENT')

2. **Enrollment Verification**
   ```php
   $isEnrolled = false;
   foreach ($course->getEnrollments() as $enrollment) {
       if ($enrollment->getStudent() === $this->getUser()) {
           $isEnrolled = true;
           break;
       }
   }
   if (!$isEnrolled) {
       throw $this->createAccessDeniedException();
   }
   ```

3. **PDF Generation** (PdfGeneratorService->generateBulletin())
   ```php
   // Get data
   $grades = $course->getGrades()->filter(
       fn($g) => $g->getStudent() === $student
   );
   $average = $this->statisticRepository
       ->calculateAverageGrade($student, $course);  // Query
   $rankedStudents = $this->statisticRepository
       ->getRankedStudentsByCourse($course);        // Query
   
   // Find rank
   $rank = array_search($student->getId(), $rankedStudents);
   
   // Render HTML from Twig template
   $html = $this->twig->render('pdf/bulletin.html.twig', [
       'student' => $student,
       'course' => $course,
       'grades' => $grades,
       'average' => $average,
       'rank' => $rank,
       'totalStudents' => count($rankedStudents),
       'generatedAt' => new DateTime(),
   ]);
   
   // Convert HTML → PDF
   $dompdf = new Dompdf();
   $dompdf->loadHtml($html);
   $dompdf->setPaper('A4', 'portrait');
   $dompdf->render();
   return $dompdf->output();  // Binary PDF string
   ```

4. **HTTP Response**
   ```
   HTTP/1.1 200 OK
   Content-Type: application/pdf
   Content-Disposition: attachment; filename="bulletin.pdf"
   Content-Length: 45832
   
   [Binary PDF Data]
   ```

5. **Browser**
   - Receives PDF file
   - Downloads to disk or opens in PDF viewer

---

## 📚 Complete Function Mapping

### Function → Package → Repository → Entity → Template

#### Student Viewing Grades
```
Controller:   Student\GradeController->myGrades()
↓
Service:      (GradeService->getAverageGrade() [optional])
↓
Repository:   EnrollmentRepository->findByStudent()
↓
Entity:       Enrollment, Course, Grade, User
↓
Template:     student/grade/my_grades.html.twig
↓
Flow:         Loop enrollments → Get courses → Get grades → Calculate average
```

#### Teacher Adding Grade
```
Controller:   Teacher\GradeController->add()
↓
Form:         GradeType (validates input)
↓
Service:      GradeService->addGrade() (business logic)
↓
Repository:   GradeRepository->persist() [via Doctrine]
↓
Entity:       Grade (created & inserted)
↓
Template:     teacher/grade/add.html.twig (form rendering)
↓
Flow:         Form submission → Validation → Service call → DB insert → Redirect
```

#### Student Enrolling in Course
```
Controller:   Student\EnrollmentController->enroll()
↓
Service:      EnrollmentService->enrollStudent()
↓
Repository:   EnrollmentRepository->persist() [via Doctrine]
↓
Entity:       Enrollment (created)
↓
Template:     student/enrollment/available.html.twig (list courses)
↓
Flow:         Verify not enrolled → Create enrollment → Persist → Flash → Redirect
```

#### Generating Student Bulletin PDF
```
Controller:   Shared\PdfController->studentBulletin()
↓
Service:      PdfGeneratorService->generateBulletin()
↓
Repository:   StatisticRepository->calculateAverageGrade()
↓
             StatisticRepository->getRankedStudentsByCourse()
↓
Entity:       User, Course, Grade (data aggregation)
↓
Template:     pdf/bulletin.html.twig (HTML structure)
↓
PDF Engine:   DOMPDF (HTML → PDF conversion)
↓
Response:     Binary PDF file (download or inline view)
↓
Flow:         Verify enrollment → Query data → Render HTML → Convert → Download
```

#### Getting Course Statistics
```
Controller:   Teacher\StatisticController->course()
↓
Service:      StatisticService->getCourseRanking()
↓
             StatisticService->calculateAverageForStudentInCourse()
↓
Repository:   StatisticRepository->getRankedStudentsByCourse()
↓
             GradeRepository->findByStudentAndCourse()
↓
Entity:       User, Course, Grade, Enrollment (complex queries)
↓
Template:     teacher/statistic/course.html.twig
↓
Flow:         Get course → Query rankings → Calculate stats → Render dashboard
```

---

## 🔐 Security Features

### Authentication
- **Strategy:** Form-based login via SecurityController
- **Password:** Bcrypt hashing using UserPasswordHasher
- **Session:** Symfony session management
- **CSRF Protection:** All forms include `_token` field

### Authorization
- **Role-Based:** ROLE_USER, ROLE_STUDENT, ROLE_TEACHER
- **Route Guards:** @IsGranted attribute on controller methods
- **Voter System:** Custom voters for fine-grained permissions
  - `CourseVoter`: VIEW, EDIT, DELETE, ADD permissions
  - Based on course ownership (teacher)
- **Access Denied:** Throws `AccessDeniedException` → HTTP 403

### Data Validation
- **Form Validation:** Symfony Validator + Constraints
- **Entity Validation:** Custom validators in src/Validator/
- **Type Safety:** Type hints on all methods
- **Database Constraints:** Unique emails, foreign keys

---

## 📊 Data Relationships

### Entity Relationship Diagram

```
┌──────────┐
│   User   │
├──────────┤
│ id (PK)  │
│ email    │
│ password │
│ roles    │
│ name     │
└──────────┘
    │
    ├─ (1:M) → Enrollment (as student)
    ├─ (1:M) → Course (as teacher)
    └─ (1:M) → Grade (assigned grades)

┌──────────────┐
│   Course     │
├──────────────┤
│ id (PK)      │
│ title        │
│ description  │
│ teacher_id   │
└──────────────┘
    │
    ├─ (1:M) → Enrollment
    └─ (1:M) → Grade

┌──────────────┐
│ Enrollment   │
├──────────────┤
│ id (PK)      │
│ student_id   │
│ course_id    │
│ enrolled_at  │
└──────────────┘
    │
    ├─ (M:1) → User (student)
    └─ (M:1) → Course

┌──────────────┐
│    Grade     │
├──────────────┤
│ id (PK)      │
│ value        │
│ type         │
│ coefficient  │
│ student_id   │
│ course_id    │
│ created_at   │
└──────────────┘
    │
    ├─ (M:1) → User (student)
    └─ (M:1) → Course
```

---

## 🚀 Process Summary

### Create Course (Teacher)
1. Teacher navigates to `/teacher/courses/new`
2. Submits `CourseType` form
3. `CourseController->new()` receives POST
4. Validates form via `CourseType` constraints
5. Sets teacher to current user
6. Calls `EntityManager->persist()` and `flush()`
7. Database INSERT into courses table
8. Redirects to course list with success message

### Enroll Student
1. Student navigates to `/student/enrollments/available`
2. Sees available courses from `CourseRepository->findAvailableForStudent()`
3. Clicks "Enroll" button (POST with CSRF token)
4. `EnrollmentController->enroll()` receives request
5. Validates CSRF token
6. Calls `EnrollmentService->enrollStudent()`
7. Service creates `Enrollment` entity
8. Persists to database (insert into enrollments table)
9. Redirects with success message

### Add Grade
1. Teacher at `/teacher/grades/add`
2. Fills `GradeType` form (student, course, value, type, coefficient)
3. Submits (POST)
4. `GradeController->add()` validates form
5. Calls `GradeService->addGrade()`
6. Service validates grade value (0-20), type, coefficient
7. Creates `Grade` entity with automatic timestamp
8. Persists to database
9. Redirects to grade list

### Get Student Grades
1. Student at `/student/grades/my-grades`
2. `Student\GradeController->myGrades()` runs
3. Gets enrollments: `EnrollmentRepository->findByStudent($student)`
4. For each enrollment, accesses related Grades (Doctrine lazy loading)
5. Renders template with grades organized by course
6. Template loops and displays

### Generate PDF
1. Student clicks "Download Bulletin" 
2. GET `/pdf/bulletin/{courseId}`
3. `PdfController->studentBulletin()` verifies enrollment
4. Calls `PdfGeneratorService->generateBulletin()`
5. Service queries grades and statistics from repositories
6. Renders `pdf/bulletin.html.twig` with data
7. DOMPDF converts HTML to PDF binary
8. Returns with `Content-Disposition: attachment`
9. Browser downloads PDF file

---

**End of Documentation**

> This document provides complete mapping of all functions, packages, repositories, entities, and templates used throughout the School Management Application. For specific implementation details, refer to the source code files referenced throughout this document.
