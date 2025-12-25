# CI/CD Quick Reference

## Workflows Overview

| Workflow | File | Trigger | Purpose |
|----------|------|---------|---------|
| **CI/CD Pipeline** | `ci.yml` | Push to main/develop | Main tests & quality checks |
| **Deploy** | `deploy.yml` | After CI passes on main | Docker build & deployment |
| **Matrix Tests** | `matrix.yml` | Nightly + manual | Multi-version PHP testing |
| **Security** | `security.yml` | Daily + push | Vulnerability & security scan |

## Common Commands

### Test Locally
```bash
# Run all checks
./.github/scripts/pre-commit-check.sh

# Run just tests
php bin/phpunit --testdox

# Run with coverage
php bin/phpunit --coverage-text

# Check code style
composer cs-check

# Fix code style
composer cs-fix

# Run static analysis
composer stan
```

### Test with Docker
```bash
# Start services
docker-compose up -d

# Run tests inside container
docker-compose exec php php bin/phpunit --testdox

# View logs
docker-compose logs -f
```

### Test Locally with act
```bash
# List jobs
act --list

# Run specific job
act -j code-quality
act -j tests

# Run all jobs
act
```

## Workflow Status in GitHub

### Check Status
1. Go to **Actions** tab
2. Click workflow name
3. Click latest run
4. View job results

### Re-run Failed Workflow
1. Click failed run
2. Click **Re-run jobs** (top right)
3. Monitor new run

### View Logs
1. Click job name
2. Click step to expand
3. View detailed output

## Fixing Common Failures

| Issue | Solution |
|-------|----------|
| MySQL connection fails | Check DATABASE_URL in workflow |
| Tests fail locally | Run `composer install` & `php bin/console doctrine:database:create` |
| Code style issues | Run `composer cs-fix` |
| Static analysis errors | Check PHPStan config in `phpstan.neon` |
| Cache not working | Verify `composer.lock` hasn't changed |
| Slow workflow | Check if cache is hitting |

## Debug Mode

Enable verbose logging:
1. Go to **Settings** > **Secrets**
2. Add secret: `ACTIONS_STEP_DEBUG` = `true`
3. Re-run workflow
4. View detailed logs

## Required Secrets for Deployment

| Secret | Value | Where |
|--------|-------|-------|
| `SLACK_WEBHOOK` | Slack webhook URL | Deploy notifications |
| `DOCKER_USERNAME` | Docker username | Docker image push |
| `DOCKER_PASSWORD` | Docker token | Docker image push |

Set in: **Settings** > **Secrets and variables** > **Actions**

## Key Files

| File | Purpose |
|------|---------|
| `.github/workflows/ci.yml` | Main pipeline |
| `.github/workflows/deploy.yml` | Deployment |
| `.github/workflows/matrix.yml` | Multi-version tests |
| `.github/workflows/security.yml` | Security scans |
| `composer.json` | Project dependencies |
| `phpunit.dist.xml` | Test configuration |
| `phpstan.neon` | Static analysis config |
| `.env.test` | Test environment variables |
| `Dockerfile` | Container build |

## Performance Tips

1. **Cache hits faster** - Keep `composer.lock` updated
2. **Parallel jobs** - Jobs run simultaneously where possible
3. **Skip slow jobs** - Use `continue-on-error: true` for warnings
4. **Reuse artifacts** - Download build artifacts instead of rebuilding

## Status Badges

Add to README.md:
```markdown
[![CI/CD](https://github.com/YOUR_ORG/school-management-app/actions/workflows/ci.yml/badge.svg)](https://github.com/YOUR_ORG/school-management-app/actions)

[![Security](https://github.com/YOUR_ORG/school-management-app/actions/workflows/security.yml/badge.svg)](https://github.com/YOUR_ORG/school-management-app/actions)
```

## Environment Variables

### In Workflows
```yaml
env:
  APP_ENV: test  # Global to all jobs
  DATABASE_URL: mysql://root:root@127.0.0.1:3306/school_test
```

### Per Job
```yaml
- name: Run Tests
  env:
    DATABASE_URL: ${{ env.DATABASE_URL }}
  run: php bin/phpunit
```

## Troubleshooting Links

- Full Guide: `GITHUB_ACTIONS_TROUBLESHOOTING.md`
- Validation: `WORKFLOW_VALIDATION.md`
- Details: `CI_CD_GUIDE.md`

## Need Help?

1. Check logs in GitHub Actions
2. Read `GITHUB_ACTIONS_TROUBLESHOOTING.md`
3. Run `./.github/scripts/pre-commit-check.sh` locally
4. Enable `ACTIONS_STEP_DEBUG` secret for verbose logs
5. Test with `act` locally before pushing

## Workflow Timeline

```
Push to GitHub
    ↓
Code Quality (2-3 min)
    ↓
Tests (3-5 min)
    ↓
Build (2-3 min)
    ↓
Deploy (optional, 5-10 min)
    ↓
Complete ✅
```

Total: ~7-15 minutes (first run), ~3-5 minutes (with cache)
