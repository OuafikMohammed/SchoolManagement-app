# Plan d'exécution détaillé — School Management

Ce document fournit des étapes opérationnelles et commandes concrètes pour exécuter chaque session des 4 sprints.

Format: pour chaque session on donne 1) Objectif 2) Prérequis 3) Étapes pas-à-pas 4) Points de contrôle (checkpoints)

---

## Préparations globales

Prérequis locaux:
- PHP 8.2+, Composer, Node.js/npm, Docker (recommandé)
- Symfony CLI (optionnel mais utile)

Commandes utiles:

```bash
# Cloner le repo (une fois créé)
git clone <REPO_URL>
cd school-management
composer install
npm install # si assets
```

---

## Sprint 1 — Session 1.1 (S1-Dev1) : Setup & Entities

Objectif: Initialiser projet, créer entités core et exécuter migration initiale.

Prérequis: repo initialisé, PHP & Composer prêts.

Étapes:
1. Initialiser Symfony (si pas déjà fait):

```bash
composer create-project symfony/skeleton:"7.0.*" .
composer require webapp
```

2. Installer Doctrine & MakerBundle:

```bash
composer require symfony/orm-pack symfony/maker-bundle --dev
```

3. Créer `User` (exemple minimal):

```bash
php bin/console make:user
# suivre prompts -> User entity with email + roles
```

4. Créer `Course`, `Enrollment`, `Grade`:

```bash
php bin/console make:entity Course
# ajouter champs: title:string, description:text, teacher: ManyToOne(User)
php bin/console make:entity Enrollment
# champs: student ManyToOne(User), course ManyToOne(Course), enrolledAt datetime
php bin/console make:entity Grade
# champs: value float, type string, student ManyToOne(User), course ManyToOne(Course)
```

5. Générer et exécuter migration:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

Points de contrôle:
- Vérifier que les tables existent en DB
- Les relations ManyToOne sont correctement mappées

---

## Sprint 1 — Session 1.2 (S1-Dev1) : Security & Auth Backend

Objectif: Configurer `security.yaml`, créer `SecurityController` et endpoints auth.

Étapes:
1. Éditer `config/packages/security.yaml` (exemple minimal):

- Définir providers (EntityUser), password_hashers, firewalls (main), access_control.

2. Créer `SecurityController`:

```bash
php bin/console make:controller Security/SecurityController
```

Implémenter actions: login (render template + handle auth), logout (route only), register (create User, hash password, persist).

3. Hash password:

```php
// dans register action
$passwordHasher->hashPassword($user, $plainPassword)
```

4. Tests unitaires basiques: créer `tests/Unit/` et assertions sur UserRepository.

Points de contrôle:
- S'authentifier via `/login` fonctionne
- Mot de passe stocké hashé

---

## Sprint 1 — Session 1.1 (S1-Dev2) : Base Layout & Components

Objectif: Installer Bootstrap, créer `base.html.twig` et composants réutilisables.

Étapes:
1. Installer Bootstrap via npm ou CDN (sa facilité):

```bash
npm install bootstrap@5
# ou utiliser CDN dans base.html.twig
```

2. Créer `templates/base.html.twig` avec blocs (`stylesheets`, `javascripts`, `body`) et inclure `templates/components/_navbar.html.twig`.
3. Ajouter `templates/components/_flash.html.twig` et `_card.html.twig`.
4. Ajouter CSS custom `assets/styles/app.css` et compiler si nécessaire.

Points de contrôle:
- Layout rendu correctement et responsive
- Composants inclus sans erreurs

---

## Sprint 1 — Session 1.2 (S1-Dev2) : Auth Frontend

Objectif: Forms & templates pour login/register.

Étapes:
1. Créer `src/Form/RegistrationType.php` avec champs email, plainPassword, roles (choice)
2. Créer `templates/security/login.html.twig` et `templates/security/register.html.twig` extensibles depuis `base.html.twig`.
3. Ajouter validations (constraints) sur `RegistrationType` (Email, Length, NotBlank).

Points de contrôle:
- Validation côté serveur déclenche les messages d'erreur
- Soumission crée un utilisateur (voir DB)

---

## Sprint 1 — Intégration & Merge

Étapes:
1. Chaque dev push sa branche feature: `feature/s1-dev1-...` / `feature/s1-dev2-...`
2. Ouvrir PRs, faire review croisée, merge après approbation.
3. Test E2E: registration → login → logout depuis navigateur.

---

## Sprint 2 (résumé des étapes clés)

Pour garder ce document synthétique, voici les étapes actionnables pour Sprint 2:

S2-Dev1 (Repos & Services):
- `php bin/console make:repository CourseRepository` et ajouter méthodes QueryBuilder:
  - `findCoursesForStudent(User $student)`
  - `findAvailableForStudent(User $student)`
- Créer `EnrollmentService` avec méthodes `enrollStudent(Course, User)` et `dropStudent(Course, User)`.
- Écrire tests unitaires pour `EnrollmentService`.

S2-Dev1 (Voters):
- Créer `CourseVoter` avec attributes VIEW, EDIT, DELETE et logique ownership/role checks.
- Ajouter tests unitaires.

S2-Dev2 (CRUD Courses):
- `php bin/console make:form CourseType`
- `php bin/console make:controller Teacher/CourseController` (actions index, show, new, edit, delete)
- Templates sous `templates/teacher/course/`.

S2-Dev2 (Enrollments):
- Controller `Student/EnrollmentController` pour lister disponibles, action `enroll` et `drop` (POST avec CSRF token)
- Dashboards: `Student/DashboardController` et `Teacher/DashboardController`.

---

## Sprint 3 (résumé des étapes clés)

S3-Dev1 (Grades):
- Repo `GradeRepository` pour requêtes par course/student
- `GradeService` add/update/delete and business rules
- `GradeVoter` pour permissions

S3-Dev1 (Statistics):
- `StatisticService::calculateAverage(array $grades, array $coeffs = [])`
- `calculateRanking(Course $course)` returns ordered list
- `GradeListener` to update stats on grade changes

S3-Dev2 (UI):
- `GradeType`, `GradeController`, templates
- `StatisticController` with student/teacher views; optionally add charts (Chart.js)

---

## Sprint 4 (résumé des étapes clés)

S4-Dev1 (PDF & Fixtures):
- `composer require dompdf/dompdf`
- `PdfGeneratorService::generateBulletin(User $student)` that renders Twig template and returns PDF stream
- `AppFixtures` using Faker to generate dataset

S4-Dev2 (Polish & Tests):
- `PdfController` to download PDFs
- Add pagination with `knplabs/knp-paginator-bundle` or custom
- Write functional tests in `tests/Functional/`

---

## Documentation & Déploiement

- Rédiger `README.md` with install steps, env vars, Docker commands
- Add `docs/INSTALLATION.md` with Docker Compose example
- Add CI workflow `.github/workflows/ci.yml` to run `composer install` and `php bin/phpunit`

---

## Checkpoints & critères d'acceptation

- Tous les endpoints critiques ont tests unitaires et fonctionnels
- Code review passée avant chaque merge
- Coverage cible: 70% après S1, 75% après S3, 80% à la fin
- PDF generation testée sur 3 exemples

---

## Prochaine action proposée

- Je termine `docs/DETAILED_STEPS.md` (actuellement en cours) et peux ensuite: 1) générer le PDF des slides, ou 2) commencer à scaffold les entités/controllers dans le repo.

