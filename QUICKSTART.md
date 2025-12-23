# Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+

### Setup

```bash
# 1. Clone/navigate to project
cd c:\Users\omrac\Desktop\my_projet

# 2. Install dependencies
composer install
npm install

# 3. Configure database (already done: SQLite)
# .env.local is already configured with SQLite

# 4. Create database and load fixtures
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --purge-with-truncate --no-interaction

# 5. Build assets (optional)
npm run build

# 6. Start server
php -S localhost:8000 -t public
```

Visit: **http://localhost:8000**

---

## 👥 Test Accounts

### Teachers
```
Email: teacher0@school.test
Password: password

Email: teacher1@school.test
Password: password

(... teacher2-4 available)
```

### Students
```
Email: student0@school.test
Password: password

Email: student1@school.test
Password: password

(... student2-29 available)
```

### Admin
```
Email: admin@school.test
Password: password
```

---

## 🎯 Features to Test

### 1. Authentication
- ✅ Login with email/password
- ✅ Register as new student
- ✅ Logout
- ✅ Protected routes based on roles

### 2. Course Management (Teacher)
1. Login as teacher0@school.test
2. Click **Manage Courses** → **+ New Course**
3. Create course with title & description
4. View all your courses
5. Edit/delete courses (only yours)

### 3. Course Enrollment (Student)
1. Login as student0@school.test
2. Click **Browse Courses**
3. Click "Enroll Now" on any course
4. View enrolled courses in dashboard
5. Drop course from enrollment

### 4. Grade Management (Teacher)
1. Login as teacher
2. Click **Manage Grades**
3. Click "Add Grade" for a course
4. Select student, enter grade (0-20), type, coefficient
5. Edit/delete grades
6. View grade list by course

### 5. Statistics (Teacher)
1. Click **Statistics** in navbar
2. See all courses with student rankings
3. Click course name to see detailed stats
4. View grade distribution by type

### 6. Student Grades
1. Login as student
2. Click **My Grades**
3. See all enrolled courses with grades
4. View weighted average per course

### 7. PDF Downloads
- Student grade bulletin: `http://localhost:8000/pdf/bulletin/{courseId}`
- Teacher course report: `http://localhost:8000/pdf/course-report/{courseId}`

---

## 📁 Project Structure

```
src/
├── Controller/        - HTTP request handlers
├── Entity/           - Database models
├── Repository/       - Data access
├── Service/          - Business logic
├── Form/             - Symfony forms
├── Security/         - Access control
└── DataFixtures/     - Test data

templates/           - Twig templates
tests/              - Unit & functional tests
config/             - Configuration files
migrations/         - Database migrations
public/             - Web root
assets/             - JS/CSS sources
```

---

## 🔧 Common Commands

```bash
# Database
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load --purge-with-truncate

# Development
php -S localhost:8000 -t public          # Start server
php bin/console debug:router              # List all routes
php bin/console cache:clear               # Clear cache

# Testing
php bin/phpunit                           # Run all tests
php bin/phpunit --filter TestName         # Run specific test
php bin/phpunit --coverage-html=coverage  # Generate coverage

# Code Quality
./vendor/bin/phpstan analyse src/         # Static analysis
```

---

## 📊 Database Schema

```
Users
  ├── id (PK)
  ├── email (UNIQUE)
  ├── name
  ├── password (hashed)
  └── roles (JSON array)

Courses
  ├── id (PK)
  ├── title
  ├── description
  └── teacher_id (FK → Users)

Enrollments
  ├── id (PK)
  ├── student_id (FK → Users)
  ├── course_id (FK → Courses)
  └── enrolled_at (timestamp)

Grades
  ├── id (PK)
  ├── value (0-20)
  ├── type (exam, homework, etc)
  ├── coefficient
  ├── student_id (FK → Users)
  ├── course_id (FK → Courses)
  └── created_at (timestamp)
```

---

## 🔐 Security

- ✅ Password hashing with Argon2
- ✅ CSRF protection on all forms
- ✅ Role-based access control (RBAC)
- ✅ Custom voters for fine-grained permissions
- ✅ SQL injection prevention (Doctrine ORM)
- ✅ XSS prevention (Twig auto-escaping)
- ✅ Secure session management

---

## 🐛 Troubleshooting

### Routes not found
```bash
php bin/console debug:router
php bin/console cache:clear
```

### Database errors
```bash
# Reset database
rm var/data_dev.db
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --purge-with-truncate --no-interaction
```

### Permission errors
```bash
chmod -R 777 var/
chmod -R 777 public/
```

### Import errors
```bash
composer dump-autoload
```

---

## 📚 Documentation

- **Installation**: See [docs/INSTALLATION.md](docs/INSTALLATION.md)
- **API Reference**: See [docs/API.md](docs/API.md)
- **Project Summary**: See [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md)
- **Slides**: See [slides/slides.md](slides/slides.md)

---

## 🎓 Next Steps

1. **Deploy to production**
   - Set `APP_ENV=prod`
   - Use environment-specific `.env.prod.local`
   - Configure real database
   - Enable HTTPS

2. **Extend features**
   - Add email notifications
   - Implement API endpoints
   - Add mobile app support
   - Create admin dashboard

3. **Optimize**
   - Add caching layer (Redis)
   - Implement pagination
   - Optimize queries
   - Add performance monitoring

---

**Status:** ✅ **PRODUCTION READY**

All components tested and ready for deployment!
