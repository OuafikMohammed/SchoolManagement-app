## Installation & Setup

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 8 or PostgreSQL 15
- Node.js 18+ (for asset compilation)

### Step 1: Clone & Install Dependencies
```bash
cd c:\Users\omrac\Desktop\my_projet
composer install
npm install
```

### Step 2: Configure Environment
Create `.env.local`:
```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/school_management?serverVersion=8.0"
MAILER_DSN="smtp://localhost:1025"
```

### Step 3: Create Database & Migrate
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### Step 4: Build Assets
```bash
npm run build
```

### Step 5: Start Development Server
```bash
php -S localhost:8000 -t public
```

Visit: http://localhost:8000

### Test Accounts (from fixtures)
**Teacher:**
- Email: `teacher0@school.test`
- Password: `password`

**Student:**
- Email: `student0@school.test`
- Password: `password`

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
