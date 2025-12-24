# Installation Guide

## System Requirements

- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 16 or higher (for asset compilation)
- **npm**: 7 or higher
- **Database**: MySQL 8.0+, PostgreSQL 15+, or SQLite 3
- **Git**: For cloning the repository

## Local Development Setup

### Step 1: Clone the Repository

```bash
git clone <REPOSITORY_URL>
cd SchoolManagement-app
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

This will:
- Install all Symfony 7 packages
- Install DomPDF for PDF generation
- Install testing dependencies (PHPUnit)
- Set up autoloading

### Step 3: Install Node Dependencies

```bash
npm install
```

This installs Bootstrap 5, Stimulus, and asset compilation tools.

### Step 4: Configure Environment Variables

Copy and configure the environment:

```bash
cp .env .env.local
```

Edit `.env.local` with your database credentials:

```bash
DATABASE_URL="mysql://root:password@127.0.0.1:3306/school_management"
```

### Step 5: Create Database & Run Migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Step 6: Load Sample Data

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

Loads 50 students, 8 teachers, 20 courses with 200+ grades.

### Step 7: Build Assets

```bash
npm run build
```

### Step 8: Start Development Server

```bash
php -S localhost:8000 -t public/
# or use Symfony CLI
symfony server:start
```

Visit: `http://localhost:8000`

## Test Accounts

After loading fixtures:

**Admin:**
- Email: `admin@school.test`
- Password: `password`

**Teacher:**
- Email: `teacher0@school.test`
- Password: `password`

**Student:**
- Email: `student0@school.test`
- Password: `password`

## Docker Setup

```bash
docker-compose up -d
docker-compose exec app php bin/console doctrine:migrations:migrate
docker-compose exec app php bin/console doctrine:fixtures:load
```

Access at `http://localhost:8080`

## Running Tests

```bash
php bin/phpunit                          # All tests
php bin/phpunit --testsuite=unit         # Unit tests
php bin/phpunit --testsuite=functional   # Functional tests
php bin/phpunit --testsuite=integration  # Integration tests
```

## Troubleshooting

### Database Connection Error
- Check `DATABASE_URL` in `.env.local`
- Ensure MySQL is running: `mysql -u root -p`
- Verify database exists: `php bin/console doctrine:database:create`

### Assets not loading
```bash
npm run build
php bin/console cache:clear
```

### Tests failing
```bash
php bin/console cache:clear --env=test
php bin/phpunit tests/Unit/Service/GradeServiceTest.php
```

### Permission issues
```bash
chmod -R 777 var/
```

## Production Deployment

1. Install without dev dependencies:

```bash
composer install --no-dev --optimize-autoloader
```

2. Set production environment:

```bash
APP_ENV=prod
APP_DEBUG=false
```

3. Clear and warm cache:

```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

4. Build assets:

```bash
npm run build
```

5. Set proper permissions:

```bash
chmod -R 755 public/
chmod -R 777 var/log var/cache
```

See [README.md](../README.md) for more details.
**Admin:**
- Email: `admin@school.test`
- Password: `password`

---

## Running Tests

### Unit Tests
```bash
php bin/phpunit tests/Unit
```

### Functional Tests
```bash
php bin/phpunit tests/Functional
```

### All Tests with Coverage
```bash
php bin/phpunit --coverage-html=coverage
```

---

## Docker Setup (Alternative)

### Using Docker Compose
```bash
docker-compose -f compose.yaml -f compose.override.yaml up -d
docker-compose exec app php bin/console doctrine:database:create
docker-compose exec app php bin/console doctrine:migrations:migrate
docker-compose exec app php bin/console doctrine:fixtures:load
```

Visit: http://localhost

---

## Project Structure

```
src/
├── Controller/          # HTTP controllers (routes, views)
├── Entity/             # Doctrine ORM entities
├── Repository/         # Database queries
├── Service/            # Business logic
├── Security/           # Access control, voters
├── Form/               # Symfony forms
└── DataFixtures/       # Test data

templates/
├── base.html.twig      # Layout
├── home/               # Homepage
├── security/           # Auth forms
├── student/            # Student views
└── teacher/            # Teacher views

tests/
├── Unit/               # Service & logic tests
└── Functional/         # Controller & integration tests

public/
├── index.php           # Entry point
└── assets/             # Compiled assets

config/
├── packages/           # Bundle configs
├── routes/             # Route definitions
└── services.yaml       # Dependency injection
```

---

## Key Features

### 1. Course Management
- Teachers create, edit, delete courses
- Courses have title, description, and teacher owner
- Students browse and enroll in courses

### 2. Grade Management
- Teachers assign grades to students in courses
- Grades have value (0-20), type (exam/homework/etc), and coefficient
- Weighted average calculation by coefficient

### 3. Statistics & Reporting
- View course rankings by average grade
- Per-student course statistics
- PDF generation for bulletins and reports
- Grade distribution by type

### 4. Security
- Role-based access control (ROLE_STUDENT, ROLE_TEACHER, ROLE_ADMIN)
- Custom voters for fine-grained permissions
- CSRF protection on all forms
- Password hashing with Argon2

### 5. Database
- Doctrine ORM with migrations
- 5 main entities: User, Course, Enrollment, Grade, Statistic
- Proper indexing and foreign keys

---

## Common Commands

### Database Management
```bash
# Create database
php bin/console doctrine:database:create

# Generate migration
php bin/console make:migration

# Run migrations
php bin/console doctrine:migrations:migrate

# Load fixtures
php bin/console doctrine:fixtures:load --purge-with-truncate
```

### Development
```bash
# Run tests
php bin/phpunit

# Check code style
./vendor/bin/phpstan analyse src

# Clear cache
php bin/console cache:clear

# Dump environment variables
php bin/console debug:container
```

### Production
```bash
# Build assets
npm run build

# Warmup cache
php bin/console cache:warmup --env=prod

# Clear cache
php bin/console cache:clear --env=prod
```

---

## Troubleshooting

### Database Connection Error
```bash
# Check .env.local DATABASE_URL
php bin/console dbal:run-sql "SELECT 1"
```

### Migration Issues
```bash
# Reset migrations
php bin/console doctrine:migrations:version --add YYYY_MM_DD_HHmmss_migration_name
```

### Permission Errors
```bash
# Fix var directory permissions
chmod -R 777 var/
```

### Asset Issues
```bash
# Rebuild assets
npm install
npm run build
php bin/console asset-map:compile
```

---

## Support
For issues or questions, refer to [Symfony Documentation](https://symfony.com/doc/current/index.html)
