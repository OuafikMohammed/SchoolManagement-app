# CI/CD Configuration Guide

## Overview

This document provides a complete guide to the CI/CD pipeline for the School Management Application. The pipeline is configured to run on every push and pull request to `main` and `develop` branches.

## Workflow Jobs

### 1. Code Quality & Standards
**Runs**: Always, on every push/PR
**Purpose**: Validate code quality and adherence to standards

- **PHP Syntax Check**: Validates all PHP files for syntax errors
- **PHP CS Fixer**: Checks code style compliance
- **PHPStan**: Performs static analysis for type checking

**Status**: ⚠️ Warnings are allowed (continue-on-error: true)

### 2. Unit & Integration Tests
**Runs**: After code quality passes
**Purpose**: Execute comprehensive test suite

- **Dependencies Installation**: Composer packages with dev dependencies
- **Test Environment Setup**: Copies `.env.test` to `.env.test.local`
- **MySQL Wait**: Ensures database is ready before running tests
- **Database Creation**: Creates test database
- **Migrations**: Runs all pending migrations
- **Fixtures**: Loads test fixtures (continues on error)
- **PHPUnit Tests**: Executes full test suite with coverage reports
- **Coverage Upload**: Uploads to Codecov

### 3. Build & Deployment Check
**Runs**: After tests pass
**Purpose**: Validate production build

- **Production Dependencies**: Installs without dev dependencies
- **Cache Optimization**: Clears and warms production cache
- **Asset Compilation**: Compiles assets for production
- **Artifact Upload**: Stores build artifacts for deployment

## Configuration Details

### PHP Version
- **Production**: PHP 8.2
- **Extensions**: mysql, intl, zip, gd, pdo_mysql, curl, json

### Database
- **Image**: MySQL 8.0
- **Test Database**: `school_test`
- **Root Password**: `root` (test environment only)
- **Port**: 3306

### Environment Variables
```env
APP_ENV=test
DATABASE_URL=mysql://root:root@127.0.0.1:3306/school_test
```

## Common Failures & Solutions

### ❌ MySQL Connection Timeout
**Symptom**: Workflow fails at "Wait for MySQL" step
**Solution**:
- Increase wait timeout in GitHub Actions
- Ensure MySQL service definition includes health check
- Add additional port wait logic

### ❌ Composer Dependency Conflicts
**Symptom**: Composer install fails
**Solution**:
- Verify PHP version matches `composer.json` requirements
- Run `composer update --lock` locally
- Check for conflicting version constraints
- Run `composer diagnose`

### ❌ Test Fixtures Load Failure
**Symptom**: Fixtures fail to load, but tests still run
**Solution**:
- Fixtures are optional (continue-on-error: true)
- Tests will run with clean database
- Check fixture syntax in `DataFixtures/`

### ❌ Database Already Exists
**Symptom**: "Database already exists" error
**Solution**:
- Use `--if-not-exists` flag (already configured)
- MySQL service always starts fresh in CI
- Safe to ignore if using this flag

### ❌ PHP Extensions Missing
**Symptom**: Extension "ext-xxx" not found
**Solution**:
- Add to `extensions` array in setup-php action
- Verify extension availability for PHP version
- Check `shivammathur/setup-php` documentation

## Secrets Configuration

Add these secrets to your GitHub repository settings (`Settings > Secrets and variables > Actions`):

| Secret | Value | Required |
|--------|-------|----------|
| `SLACK_WEBHOOK` | Slack webhook URL | No (deployment only) |
| `DOCKER_USERNAME` | Docker Hub username | No (deployment only) |
| `DOCKER_PASSWORD` | Docker Hub token | No (deployment only) |

## Caching

The workflow uses GitHub Actions cache for Composer packages:
- **Cache Key**: `${{ runner.os }}-php-${{ hashFiles('**/composer.lock') }}`
- **Restore Key**: `${{ runner.os }}-php-`
- **Path**: `vendor/`

Cache is invalidated when `composer.lock` changes.

## Docker Build

The `Dockerfile` provides:
- Multi-stage build for optimized images
- Alpine Linux for minimal size
- All required PHP extensions
- Production-ready configuration

Build locally:
```bash
docker build -t school-management:latest .
```

## Local Testing

To test the workflow locally using [act](https://github.com/nektos/act):

```bash
# List available jobs
act --list

# Run code quality job
act -j code-quality

# Run tests job
act -j tests

# Run all jobs
act
```

## Deployment

After all tests pass on `main` branch, the deployment workflow triggers:
- Builds Docker image
- Pushes to container registry
- Sends Slack notification

For manual deployment, navigate to **Actions > Deploy to Production > Run workflow**

## Monitoring

### GitHub Actions Dashboard
- Navigate to **Actions** tab in repository
- View workflow runs, logs, and artifacts
- Download coverage reports and build artifacts

### Status Badge
Add to README.md:
```markdown
[![CI/CD Pipeline](https://github.com/YOUR_USERNAME/school-management-app/actions/workflows/ci.yml/badge.svg)](https://github.com/YOUR_USERNAME/school-management-app/actions/workflows/ci.yml)
```

## Best Practices

1. **Keep Dependencies Updated**: Run `composer update` regularly
2. **Fix Warnings**: Address code quality warnings before merge
3. **Test Locally First**: Run tests locally before pushing
4. **Write Tests**: Maintain >70% code coverage
5. **Review Logs**: Check workflow logs for warnings and suggestions
6. **Cache Busting**: Clear cache if installing new extensions
7. **Security**: Never commit secrets; use GitHub Secrets only

## Performance Tips

1. **Enable Caching**: Already enabled, saves 30-60 seconds per run
2. **Use Matrix Strategy**: Run tests on multiple PHP versions (if needed)
3. **Fail Fast**: Use `fail-fast: true` in matrix builds
4. **Parallel Jobs**: Multiple jobs run simultaneously where possible
5. **Artifact Cleanup**: Remove old artifacts to save storage

## Troubleshooting Workflow

1. **Check workflow file syntax**: Use GitHub Actions linter
2. **Review runner logs**: Provides detailed execution trace
3. **Test locally**: Reproduce issues with `act` or Docker
4. **Enable debug logging**: Add `ACTIONS_STEP_DEBUG=true` secret
5. **Consult action documentation**: Link documentation from action metadata

## References

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Shivam Mathur Setup PHP](https://github.com/shivammathur/setup-php)
- [PHPUnit Documentation](https://phpunit.readthedocs.io/)
- [PHPStan Documentation](https://phpstan.org/)
- [PHP CS Fixer Documentation](https://cs.symfony.com/)
