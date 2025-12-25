# GitHub Actions Workflow Validation

## Quick Validation Checklist

Run these commands locally to validate your setup before pushing:

### 1. Check Composer Configuration
```bash
composer validate
composer diagnose
```

### 2. Verify PHP Version and Extensions
```bash
php --version
php -m | grep -E "(pdo|mysql|intl|zip|gd)"
php -i | grep "Extension => "
```

### 3. Check Environment Files
```bash
# Check .env.test exists
ls -la .env.test

# Verify env variables set
grep -E "^[A-Z_]+=" .env.test
```

### 4. Test Database Operations
```bash
# Create test database (if applicable)
php bin/console doctrine:database:create --env=test --if-not-exists

# Run migrations
php bin/console doctrine:migrations:migrate --env=test --no-interaction

# Verify migrations
php bin/console doctrine:migrations:list --env=test
```

### 5. Run Tests Locally
```bash
# Run all tests
php bin/phpunit

# Run tests with coverage
php bin/phpunit --coverage-text

# Run specific test suite
php bin/phpunit tests/Unit/
php bin/phpunit tests/Functional/
```

### 6. Verify Code Quality
```bash
# Check code style
composer cs-check

# Fix code style
composer cs-fix

# Run static analysis
composer stan
```

## Workflow Syntax Validation

### Using GitHub CLI
```bash
# Install GitHub CLI if not already installed
# Then validate workflow files:

gh workflow view .github/workflows/ci.yml
gh workflow list
```

### Manual YAML Validation
```bash
# Install yamllint
pip install yamllint

# Validate workflow files
yamllint .github/workflows/ci.yml
yamllint .github/workflows/deploy.yml
```

## Pre-Commit Check Script

Save this as `.github/scripts/pre-commit-check.sh`:

```bash
#!/bin/bash
set -e

echo "🔍 Running pre-commit checks..."

# 1. Composer validation
echo "✓ Validating composer.json..."
composer validate

# 2. PHP syntax check
echo "✓ Checking PHP syntax..."
php -l bin/console
find src -name "*.php" -exec php -l {} \; > /dev/null

# 3. Code style check
echo "✓ Checking code style..."
composer cs-check

# 4. Static analysis
echo "✓ Running static analysis..."
composer stan

# 5. Unit tests
echo "✓ Running tests..."
php bin/phpunit --testdox

echo "✅ All checks passed!"
```

Make executable:
```bash
chmod +x .github/scripts/pre-commit-check.sh
```

## GitHub Actions Debugging

### Enable Step Debug Logging
Add to repository secrets:
- Name: `ACTIONS_STEP_DEBUG`
- Value: `true`

Then re-run workflow for detailed logs.

### Local Testing with act

Install [act](https://github.com/nektos/act):

```bash
# macOS
brew install act

# Windows (PowerShell)
choco install act-cli

# Linux
curl https://raw.githubusercontent.com/nektos/act/master/install.sh | sudo bash
```

Run workflows locally:
```bash
# List available jobs
act --list

# Run specific job
act -j code-quality
act -j tests

# Run with specific event
act push -b main

# View logs for failed step
act -j tests --verbose
```

## Common CI/CD Issues & Solutions

### Issue: "SQLSTATE[HY000] [1045] Access denied for user 'root'"
**Cause**: Incorrect DATABASE_URL or MySQL not ready
**Fix**:
```yaml
- name: Wait for MySQL
  run: |
    for i in {1..30}; do
      if mysqladmin ping -h127.0.0.1 -uroot -proot 2>/dev/null; then
        echo "MySQL is ready!"
        exit 0
      fi
      echo "Attempt $i: Waiting for MySQL..."
      sleep 2
    done
    exit 1
```

### Issue: "Undefined function or method"
**Cause**: Missing package in dev dependencies
**Fix**: Check composer.json require-dev and verify packages are installed

### Issue: "Class not found" in tests
**Cause**: Fixtures not loaded or test bootstrap issues
**Fix**:
```bash
php bin/console doctrine:fixtures:load --env=test --append
php vendor/bin/phpunit --bootstrap tests/bootstrap.php
```

### Issue: Workflow slow/timing out
**Cause**: Missing cache or large dependencies
**Fix**: Ensure cache is properly configured:
```yaml
- uses: actions/cache@v3
  with:
    path: vendor
    key: ${{ runner.os }}-php-${{ hashFiles('**/composer.lock') }}
```

### Issue: "Permission denied" on file operations
**Cause**: Incorrect permissions in Docker or chmod issues
**Fix**:
```yaml
- name: Fix permissions
  run: |
    chmod -R 755 public/
    chmod -R 775 var/
```

## Monitoring Workflow Performance

### View Workflow Metrics
1. Go to **Actions** tab in repository
2. Click on workflow name
3. View run duration and logs
4. Compare timing between runs

### Optimization Tips
- Cache dependencies: Can save 30-60 seconds
- Parallel jobs: Run independent checks simultaneously
- Skip unnecessary steps: Use `if:` conditions for conditionalsteps
- Use artifacts: Store build results instead of rebuilding

## Post-Workflow Checks

After workflow completes successfully:

1. **Verify Test Results**
   - Check test count and pass rate
   - Review coverage percentage

2. **Check Build Artifacts**
   - Download and inspect artifacts
   - Verify asset compilation

3. **Confirm Deployment** (if applicable)
   - Check Docker image in registry
   - Verify deployment notifications

## Helpful Resources

- [GitHub Actions Syntax](https://docs.github.com/en/actions/using-workflows/workflow-syntax-for-github-actions)
- [GitHub Actions Best Practices](https://docs.github.com/en/actions/guides)
- [Setup PHP Action](https://github.com/shivammathur/setup-php#readme)
- [act - Local GitHub Actions](https://github.com/nektos/act)
- [Workflow Troubleshooting](https://docs.github.com/en/actions/guides/about-continuous-integration)
