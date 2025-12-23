# School Management System

A professional Symfony 7 application for managing school courses, student enrollments, grades, and statistics.

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.0 or PostgreSQL 15
- Node.js 16+ (for assets)

### Installation

1. Clone the repository:

```bash
git clone <REPO_URL>
cd school-management
```

2. Install dependencies:

```bash
composer install
npm install
```

3. Create `.env.local` and configure your database:

```bash
cp .env .env.local
# Edit .env.local and set DATABASE_URL
```

4. Create the database and run migrations:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Load test fixtures (optional):

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

6. Start the development server:

```bash
symfony server:start
# or
php -S localhost:8000 -t public/
```

7. Build assets:

```bash
npm run build
```

Visit `http://localhost:8000` and register a new account.

---

## 📁 Project Structure

```
school-management/
├── src/
│   ├── Controller/     # HTTP controllers
│   ├── Entity/         # Doctrine entities
│   ├── Repository/     # Database queries
│   ├── Service/        # Business logic
│   ├── Form/           # Form types
│   └── Security/       # Voters & authentication
├── templates/          # Twig templates
├── tests/              # Unit & functional tests
├── migrations/         # Database migrations
├── public/             # Web root
├── config/             # Symfony configuration
└── docs/               # Documentation
```

---

## 🛠️ Technology Stack

- **Backend**: Symfony 7.0, PHP 8.2+
- **Database**: Doctrine ORM, MySQL 8 / PostgreSQL 15
- **Frontend**: Twig, Bootstrap 5
- **Testing**: PHPUnit, Symfony WebTestCase
- **Security**: Role-based access control (RBAC), Voters
- **PDF**: DomPDF (via KnpSnappyBundle)

---

## 👥 Features

### Sprint 1: Authentication & Foundations
- User registration with role selection (Student/Teacher)
- Secure login/logout with password hashing
- Base layout and responsive UI

### Sprint 2: Course Management
- Teachers can create and manage courses
- Students can browse and enroll in courses
- Course-level access control (voters)
- Student/Teacher dashboards

### Sprint 3: Grading & Statistics
- Teachers can add grades to students
- Automatic average calculation (weighted by coefficient)
- Student rankings per course
- Statistics dashboard for teachers

### Sprint 4: PDF & Polish
- Generate student bulletins as PDF
- Generate course reports as PDF
- Pagination and advanced filtering
- Complete test coverage (>80%)

---

## 🧪 Testing

Run all tests:

```bash
php bin/phpunit
```

Run specific test suite:

```bash
php bin/phpunit --testsuite=unit
php bin/phpunit --testsuite=functional
```

---

## 📚 Documentation

- [Installation Guide](docs/INSTALLATION.md)
- [API Documentation](docs/API.md)
- [Development Guide](docs/DETAILED_STEPS.md)
- [Slides & Architecture](slides/slides.md)

---

## 🔐 Security

- Passwords are hashed using bcrypt/argon2
- CSRF protection on all forms
- SQL injection protection via ORM
- Access control via Symfony Voters
- Role-based route protection

---

## 🚀 Deployment

### Local Docker Deployment

```bash
docker-compose up -d
# Wait for containers to start
docker-compose exec app php bin/console doctrine:migrations:migrate
docker-compose exec app php bin/console doctrine:fixtures:load
```

### Production

1. Install dependencies (no-dev):

```bash
composer install --no-dev --optimize-autoloader
```

2. Clear cache:

```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

3. Run migrations:

```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

4. Build assets:

```bash
npm run build
```

---

## 📖 Agile Workflow

This project follows a 4-sprint Agile methodology:

- **Sprint 1**: Foundations (7h)
- **Sprint 2**: CRUD & Courses (7h)
- **Sprint 3**: Grading & Statistics (7h)
- **Sprint 4**: PDF & Polish (7h)

See [detailed steps](docs/DETAILED_STEPS.md) for session-by-session breakdown.

---

## 🤝 Contributing

1. Create a feature branch: `git checkout -b feature/s1-dev1-description`
2. Commit regularly: `git commit -m "[S1-D1] feat: description"`
3. Push to origin: `git push origin feature/...`
4. Create a Pull Request with description and tests
5. Request code review from the other developer
6. Merge after approval

---

## 📞 Support

For questions or issues:
1. Check [Detailed Steps](docs/DETAILED_STEPS.md)
2. Review [Slides](slides/slides.md) for architecture
3. Check existing tests for examples

---

## 📄 License

This project is open source and available under the MIT License.

---

## 🎯 Team

- **Dev 1**: Backend Specialist (Entities, Services, Security)
- **Dev 2**: Frontend Specialist (Controllers, Forms, UI)

Last updated: December 2025
©️By Mohammed Ouafik &Abdel Wadoud Omrachi 
