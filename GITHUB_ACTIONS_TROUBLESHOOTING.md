# GitHub Actions Troubleshooting Guide

## Quick Diagnostics

### Check Workflow Status
1. Go to **Actions** tab in GitHub repository
2. Look for failed workflow runs
3. Click on the failed run to view job summary
4. Expand the failed job to view step details
5. Look for red ❌ marks indicating failures

## Common Failures & Solutions

### 1. MySQL Connection Errors

**Error Message:**
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'127.0.0.1'
```

**Causes & Solutions:**
- ❌ MySQL service not fully started
  - **Fix**: Already handled with health check in workflow
  
- ❌ DATABASE_URL not set
  - **Fix**: Verify environment variable in workflow:
    ```yaml
    env:
      DATABASE_URL: mysql://root:root@127.0.0.1:3306/school_test
    ```

- ❌ Wrong credentials
  - **Fix**: Service uses `root:root`, verify compose.yaml matches

**Test Locally:**
```bash
docker-compose up -d mysql
sleep 10
mysqladmin ping -h 127.0.0.1 -u root -proot
```

### 2. PHP Extension Not Found

**Error Message:**
```
Call to undefined function or Missing extension "ext-xxx"
```

**Common Extensions Needed:**
- `pdo_mysql` - Database connectivity
- `intl` - Internationalization
- `zip` - Archive handling
- `gd` - Image manipulation
- `curl` - HTTP requests
- `json` - JSON operations

**Fix in ci.yml:**
```yaml
- name: Setup PHP
  uses: shivammathur/setup-php@v2
  with:
    php-version: '8.2'
    extensions: mysql, intl, zip, gd, pdo_mysql, curl, json  # Add here
    tools: composer:v2
```

### 3. Composer Dependency Conflicts

**Error Message:**
```
Your requirements could not be resolved to an installable set of packages.
```

**Diagnostic Steps:**
1. Run locally: `composer update --lock`
2. Check PHP version matches: `composer.json` requires PHP 8.2+
3. Run: `composer diagnose`
4. Look for version conflicts in output

**Common Causes:**
- Incompatible package versions
- PHP version mismatch
- Missing platform packages

**Fix:**
```bash
# Update composer lock file
composer install --lock-only

# Or update packages
composer update --with-dependencies
```

### 4. Database Already Exists

**Error Message:**
```
Database "school_test" already exists and contains tables.
```

**Why It Happens:**
- MySQL container retains state between runs (shouldn't happen)
- `--if-not-exists` flag missing

**Fix in ci.yml (already included):**
```yaml
- name: Create Database
  run: php bin/console doctrine:database:create --env=test --if-not-exists
```

### 5. Tests Fail with "Class Not Found"

**Error Message:**
```
Class "App\Entity\User" not found
```

**Causes:**
- Entity not loaded by autoloader
- Test fixtures not loaded
- Wrong bootstrap file

**Fix:**
```bash
# Verify autoloader configuration
composer dump-autoload --optimize

# Check phpunit bootstrap
grep -r "autoload" phpunit.dist.xml tests/bootstrap.php

# Ensure tests/ directory autoloads
cat composer.json | grep -A 3 "autoload-dev"
```

### 6. Cache Not Being Used

**Error Message:**
- Workflow takes 2+ minutes (should be 30-60 seconds with cache)

**Fix in ci.yml:**
```yaml
- name: Cache Composer packages
  uses: actions/cache@v3
  with:
    path: vendor
    key: ${{ runner.os }}-php-${{ hashFiles('**/composer.lock') }}
    restore-keys: |
      ${{ runner.os }}-php-
```

**Verify:**
- First run: "Cache not found"
- Second run: "Cache hit" should appear in logs

### 7. Static Analysis Timeouts

**Error Message:**
```
PHPStan/php-cs-fixer running > 5 minutes
```

**Optimization:**
```yaml
- name: Code Style Check
  run: composer cs-check
  timeout-minutes: 10  # Add timeout
```

**Or skip certain checks:**
```yaml
- name: PHPStan (Quick Check)
  run: phpstan analyse src --level=5 --memory-limit=512M
```

### 8. File Permission Errors

**Error Message:**
```
Permission denied: var/cache/
```

**Fix:**
```yaml
- name: Fix Permissions
  run: |
    chmod -R 755 public/
    chmod -R 775 var/
    chmod 644 config/services.yaml
```

### 9. Environment File Missing

**Error Message:**
```
Unable to open file: /home/runner/work/.../app/.env.test.local
```

**Fix:**
```yaml
- name: Setup Test Environment
  run: |
    if [ ! -f .env.test ]; then
      echo "ERROR: .env.test not found"
      exit 1
    fi
    cp .env.test .env.test.local
```

### 10. Coverage Report Upload Fails

**Error Message:**
```
Failed to upload coverage files
```

**Fix (already has continue-on-error):**
```yaml
- name: Upload Coverage to Codecov
  uses: codecov/codecov-action@v3
  with:
    file: ./coverage.xml
    fail_ci_if_error: false  # Don't fail on upload error
  continue-on-error: true
```

## Debugging Strategies

### 1. Enable Debug Logging

Add GitHub Actions secret:
- **Name:** `ACTIONS_STEP_DEBUG`
- **Value:** `true`

Then re-run workflow for verbose logs.

### 2. Download Workflow Logs

1. Go to Actions > Failed Run
2. Click **Download logs** (top right)
3. Extract and search for errors

### 3. Check Matrix Variables

If using matrix strategy:
```bash
echo "PHP Version: ${{ matrix.php-version }}"
echo "OS: ${{ matrix.os }}"
```

### 4. Verify Secrets

Check configured secrets:
```bash
# Go to Settings > Secrets and variables > Actions
# Verify all required secrets are set
# Common secrets needed:
# - SLACK_WEBHOOK
# - DOCKER_USERNAME
# - DOCKER_PASSWORD
```

### 5. Test Locally with act

```bash
# Install act: https://github.com/nektos/act

# Run specific job
act -j tests

# Run with verbose output
act -j tests --verbose

# See all available jobs
act --list

# Run with specific event
act push --ref develop
```

## Performance Optimization

### 1. Reduce Install Time
```yaml
# Use prebuilt images instead of building from scratch
- uses: docker://php:8.2-fpm-alpine

# Use dependency caching
- uses: actions/cache@v3
```

### 2. Parallel Jobs
Current workflow runs in parallel:
- code-quality (fast)
- tests (depends on code-quality)
- build (depends on tests)

Estimated time: 3-5 minutes

### 3. Skip Unnecessary Steps
```yaml
- name: Run Tests
  if: github.event_name == 'push'  # Skip on certain events
  run: php bin/phpunit
```

## Monitoring & Alerts

### Set Up Notifications

1. **Slack Integration:**
   - Add webhook to secrets
   - Workflow already configured

2. **GitHub Notifications:**
   - Repository > Settings > Notifications
   - Choose which events trigger notifications

3. **Email Notifications:**
   - GitHub Settings > Notifications
   - Select email delivery for actions

## Workflow Status Badge

Add to README.md:
```markdown
[![CI/CD Pipeline](https://github.com/YOUR_ORG/school-management-app/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/YOUR_ORG/school-management-app/actions/workflows/ci.yml)
```

## Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Workflow Syntax Reference](https://docs.github.com/en/actions/using-workflows/workflow-syntax-for-github-actions)
- [act - Local GitHub Actions Testing](https://github.com/nektos/act)
- [Setup PHP Action](https://github.com/shivammathur/setup-php)
- [PHPUnit Documentation](https://phpunit.readthedocs.io/)

## Still Having Issues?

1. **Check workflow file syntax** using GitHub's built-in linter
2. **Review runner logs** for detailed error messages
3. **Search GitHub Issues** in affected action repositories
4. **Create GitHub Discussion** in repository
5. **Contact action maintainers** if issue is with third-party action

For immediate help, enable debug logging and share workflow logs in discussions.
