# School Management — Plan de projet et exécution (détaillé)

---

# Titre

- Projet: School Management
- Durée: 4 semaines (2 devs)
- Livrable: Application Symfony 7 prête production

---

# Équipe & Organisation

- Dev 1: Backend Specialist (Entities, Services, Security)
- Dev 2: Frontend/Integration Specialist (Controllers, Forms, UI)
- Méthode: Agile, 4 sprints hebdomadaires

---

## Vue d'ensemble des slides détaillées

- Chaque sprint est découpé en sessions (2 devs × 2 sessions)
- Pour chaque session: objectif, prérequis, étapes concrètes, commandes, points de contrôle

---

## Sprint 1 — Fondations (S1)

### S1 - Dev1 - Session 1.1 : Setup projet & Entities (3.5h)

- Objectif: initialiser le repo, installer Symfony/Doctrine, créer entités core
- Prérequis: PHP 8.2+, Composer, accès DB (MySQL/Postgres)

Étapes concrètes:

1. Initialiser projet (si non fait):

```bash
composer create-project symfony/skeleton:"7.0.*" school-management
cd school-management
composer require webapp
```

2. Installer dépendances Doctrine & Maker:

```bash
composer require symfony/orm-pack
composer require symfony/maker-bundle --dev
```

3. Créer `User` (make:user) — suivre prompts pour email/roles

```bash
php bin/console make:user
```

4. Créer entités `Course`, `Enrollment`, `Grade` (avec relations):

```bash
php bin/console make:entity Course
php bin/console make:entity Enrollment
php bin/console make:entity Grade
```

5. Migration initiale:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

Points de contrôle:

- Vérifier tables en DB
- Relations ManyToOne correctes
- Commit initial: `[S1-D1] feat: add base entities and initial migration`

---

### S1 - Dev1 - Session 1.2 : Security & Auth Backend (3.5h)

- Objectif: configurer `security.yaml`, créer controller auth (login/register/logout), hashing

Étapes concrètes:

1. Configurer `config/packages/security.yaml` (provider entity, password_hashers, firewall main)

2. Créer `SecurityController` et routes `/login`, `/logout`, `/register`:

```bash
php bin/console make:controller Security/SecurityController
```

3. Implémenter registration: validation, `UserPasswordHasherInterface` pour hasher

4. Implémenter login: render form, rely on Symfony authenticator (make:auth si besoin)

5. Tests unitaires simples pour `User` et `UserRepository`

Commandes utiles:

```bash
php bin/console make:auth # optionnel to scaffold authenticator
php bin/console security:hash-password
```

Points de contrôle:

- Login fonctionne via navigateur
- Passwords hashés en DB
- Add tests and commit `[S1-D1] feat: security and auth scaffold`

---

### S1 - Dev2 - Session 1.1 : Base Layout & Components (3.5h)

- Objectif: installer Bootstrap, créer `base.html.twig` et composants réutilisables

Étapes concrètes:

1. Installer Bootstrap (npm) ou utiliser CDN:

```bash
npm install bootstrap@5
```

2. Créer `templates/base.html.twig` avec blocs `stylesheets`, `javascripts`, `body` et inclure `_navbar`.

3. Ajouter composants:

- `templates/components/_navbar.html.twig`
- `templates/components/_flash.html.twig`
- `templates/components/_card.html.twig`

4. Ajouter CSS custom `assets/styles/app.css` et compiler (encore `npm run build` si asset pipeline en place)

Points de contrôle:

- Layout rendu, nav responsive, flashes fonctionnels
- Commit: `[S1-D2] feat: add base layout and components`

---

### S1 - Dev2 - Session 1.2 : Auth Frontend (3.5h)

- Objectif: forms et templates pour registration/login

Étapes concrètes:

1. Créer `RegistrationType`:

```bash
php bin/console make:form RegistrationType
```

2. Ajouter validations (Email, NotBlank, Length) et champ `plainPassword` non persisté.

3. Créer templates:

- `templates/security/login.html.twig`
- `templates/security/register.html.twig`

4. Intégrer dans `SecurityController::register()` pour persister user et rediriger.

Points de contrôle:

- Register crée user en DB
- Messages d'erreur affichés correctement
- Commit: `[S1-D2] feat: add registration form and templates`

---

## Sprint 1 — Intégration finale

- Actions de merge: ouvrir PRs, review croisée, merger sur `main` après approbation
- Tests E2E: registration → login → logout

---

## Sprint 2 — CRUD Courses & Enrollments (détaillé)

### S2 - Dev1 - Session 2.1 : Repositories & EnrollmentService (3.5h)

- Objectif: queries custom, service d'inscription

Étapes concrètes:

1. Créer `CourseRepository` et ajouter méthodes:

- `findCoursesForStudent(User $student)`
- `findAvailableForStudent(User $student)`

Exemple QueryBuilder:

```php
public function findAvailableForStudent(User $student)
{
		return $this->createQueryBuilder('c')
				->leftJoin('c.enrollments', 'e')
				->andWhere(':student NOT MEMBER OF c.students')
				->setParameter('student', $student)
				->getQuery()
				->getResult();
}
```

2. Créer `EnrollmentService` avec méthodes `enrollStudent(Course, User)` et `dropStudent(Course, User)` — logique transactionnelle.

3. Tests unitaires pour `EnrollmentService`.

Points de contrôle:

- Méthodes retournent résultats corrects
- EnrollmentService testé

---

### S2 - Dev1 - Session 2.2 : CourseVoter & Security (3.5h)

- Objectif: protection des actions CRUD via voter

Étapes concrètes:

1. Créer `src/Security/Voter/CourseVoter.php` — attributes VIEW, EDIT, DELETE

2. Logique: owner (teacher) can EDIT/DELETE, admins can do all, students can VIEW if enrolled or public

3. Ajouter tests unitaires pour voter

Points de contrôle:

- Voter bloque correctement les actions non autorisées
- Tests passent

---

### S2 - Dev2 - Session 2.1 : CRUD Courses (3.5h)

- Objectif: formulaires & controller CRUD

Étapes concrètes:

1. Créer form `CourseType` (title, description, capacity, startDate)

```bash
php bin/console make:form CourseType
```

2. Créer `Teacher/CourseController` (index, show, new, edit, delete)

3. Templates: `templates/teacher/course/{index,show,new,edit,_form}.html.twig`

4. CSRF token pour delete (form with method POST)

Points de contrôle:

- CRUD fonctionne, validation ok
- Commit: `[S2-D2] feat: course CRUD with templates`

---

### S2 - Dev2 - Session 2.2 : Enrollments & Dashboards (3.5h)

- Objectif: actions d'inscription et dashboards

Étapes concrètes:

1. Créer `Student/EnrollmentController` avec actions:

- `available()` — liste cours disponibles
- `enroll()` — POST, uses EnrollmentService
- `drop()` — POST, uses EnrollmentService

2. Dashboards:

- `Student/DashboardController::index()` — liste inscriptions, prochaines évaluations
- `Teacher/DashboardController::index()` — liste cours, actions rapides

3. Templates pour listes et boutons enroll/drop (avec CSRF)

Points de contrôle:

- Student peut s'inscrire/abandonner
- Dashboard affiche données pertinentes

---

## Sprint 3 — Grades & Statistics (détaillé)

### S3 - Dev1 - Session 3.1 : GradeRepository & GradeService (3.5h)

- Objectif: gestion CRUD notes + logique métier

Étapes concrètes:

1. `GradeRepository`: queries par course, par student, filtre date

2. `GradeService` with methods `addGrade`, `updateGrade`, `deleteGrade` — ensure permissions via GradeVoter

3. Tests unitaires GradeService

Points de contrôle:

- Grade operations transactional
- Tests unitaires couvrent les règles métiers

---

### S3 - Dev1 - Session 3.2 : StatisticService & GradeListener (3.5h)

- Objectif: engine calcul moyennes et classement

Étapes concrètes:

1. `StatisticService::calculateAverage(array $grades, array $coeffs = null)` — support coefficients

2. `calculateRanking(Course $course)` — agrégation SQL ou PHP, retourner classement

3. `GradeListener` (onGradeCreated/Updated/Deleted) -> recalculer statistiques pour affected students/courses

4. Tests unitaires pour StatisticService

Points de contrôle:

- Moyennes correctes (tests avec jeux de données)
- Listener déclenché et met à jour cache/stats

---

### S3 - Dev2 - Session 3.1 : Grade UI & Controller (3.5h)

- Objectif: formulaire de notation et liste des notes

Étapes concrètes:

1. `php bin/console make:form GradeType` — champs: value, type, coefficient, comment

2. `Teacher/GradeController` — index(add)/edit/delete

3. Templates: `templates/teacher/grade/{index,add,edit,_form}.html.twig`

Points de contrôle:

- Teachers peuvent ajouter/éditer notes
- Permissions respectées via GradeVoter

---

### S3 - Dev2 - Session 3.2 : Statistics Display (3.5h)

- Objectif: pages de statistiques pour prof & étudiant

Étapes concrètes:

1. `Teacher/StatisticController::index()` — tableau des moyennes par cours, export CSV/PDF optionnel

2. `Student/GradeViewController::myGrades()` — liste notes, moyenne calculée

3. Templates with charts (Chart.js optional)

Points de contrôle:

- Statistiques visibles et correctes
- Performance acceptable sur datasets ~100 étudiants

---

## Sprint 4 — PDF, Fixtures & Polish (détaillé)

### S4 - Dev1 - Session 4.1 : DomPDF & PdfGeneratorService (3.5h)

- Objectif: génération de bulletins PDF et rapports de cours

Étapes concrètes:

1. Installer DomPDF:

```bash
composer require dompdf/dompdf
```

2. Créer `src/Service/PdfGeneratorService.php`:

- `generateBulletin(User $student)` — render Twig template `templates/pdf/bulletin.html.twig` puis `Dompdf` render
- `generateCourseReport(Course $course)` — similar

3. Tests basiques generation (ouvrir et vérifier bytes non vides)

Points de contrôle:

- PDF téléchargeable via controller
- Visuel lisible (marges pour impression)

---

### S4 - Dev1 - Session 4.2 : AppFixtures & Tests (3.5h)

- Objectif: jeux de données réalistes + tests finaux

Étapes concrètes:

1. Créer `src/DataFixtures/AppFixtures.php` avec Faker pour créer 50 users, 20 courses, 200 grades

2. Charger fixtures:

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

3. Écrire tests unitaires finaux et tests d'intégration pour repositories

Points de contrôle:

- Fixtures chargées et DB cohérente
- Tests couvrent cas critiques

---

### S4 - Dev2 - Session 4.1 : PDF Controller & UI Polish (3.5h)

- Objectif: routes pour téléchargement PDF, responsive polish, pagination

Étapes concrètes:

1. `src/Controller/Shared/PdfController.php` — routes pour `bulletin/{id}/download` et `course/{id}/report`

2. Pagination: utiliser `knplabs/knp-paginator-bundle` ou paginer manuellement

3. UI: responsive fixes, small animations, accessibility checks

Points de contrôle:

- Buttons download fonctionnels
- Pagination ok sur longues listes

---

### S4 - Dev2 - Session 4.2 : Tests fonctionnels & Documentation (3.5h)

- Objectif: tests E2E, README, guides déploiement

Étapes concrètes:

1. Écrire tests fonctionnels (PHPUnit + BrowserKit / Panther si headless)

2. Rédiger `README.md`, `docs/INSTALLATION.md`, `docs/API.md`

3. Préparer Docker production (Dockerfile + docker-compose.prod.yml)

Points de contrôle:

- Tests fonctionnels couvrent user flows critiques
- README explique install/run

---

## CI/CD suggestion

- `.github/workflows/ci.yml` to run `composer install`, `php bin/phpunit` and static analysis (PHPStan)

Example snippet:

```yaml
name: CI
on: [push, pull_request]
jobs:
	tests:
		runs-on: ubuntu-latest
		steps:
			- uses: actions/checkout@v2
			- name: Setup PHP
				uses: shivammathur/setup-php@v2
				with: { php-version: '8.2' }
			- run: composer install
			- run: php bin/phpunit --testsuite=unit
```

---

## Règles Git & Commits

- Branching: `feature/s{SPRINT}-d{DEV}-{short}`
- Commit message: `[S{n}-D{m}] feat/fix: brief description`

---

## Checkpoints & Critères d'acceptation

- Tests unit & fonctionnels existants pour features critiques
- Code review passée avant merge
- Coverage targets: S1 >70%, S3 >75%, final >80%

---

## Prochaines actions (optionnelles)

- Exporter le deck en PDF (`docs/slides.pdf`) via `decktape`/Chrome
- Générer scaffolding initial des entités/controllers (je peux créer les fichiers et migrations)

---

<!-- Fin du deck détaillé -->
