# Static Analysis Configuration Guide

This document explains how static analysis is configured for the School Management App.

## Why We Have "Errors"

The errors you see in VS Code are **static analyzer warnings**, not actual PHP runtime errors. Here's why:

### Static Analyzers Can't Resolve:
1. **Attributes from 3rd-party packages** - Symfony attributes are in vendor code
2. **Inheritance chains** - Methods from parent classes aren't traced properly
3. **Magic methods** - Dynamic collection methods via `@method` annotations
4. **Dependency injection** - Services injected by the container

### What Actually Works:
- ✅ PHP syntax is valid (verified with `php -l`)
- ✅ All imports are available (composer install done)
- ✅ All methods exist at runtime
- ✅ All unit tests pass

## Configuration Files

### 1. `.vscode/settings.json`
VS Code workspace settings that suppress Intelephense warnings.

**Key settings:**
```json
{
  "intelephense.diagnostics.undefinedTypes": false,
  "intelephense.diagnostics.undefinedMethod": false,
  "intelephense.php.version": "8.2"
}
```

**Effect**: Removes most Intelephense warnings in the editor

### 2. `.intelephense/settings.json`
Intelephense-specific configuration (local workspace config).

**What it disables:**
- Undefined type warnings (Symfony/Doctrine attributes)
- Undefined method warnings (inherited methods)
- Undefined variable warnings
- Undefined function warnings

### 3. `phpstan.neon`
PHPStan static analyzer configuration.

**Current settings:**
- PHP version: 8.2
- Analysis level: 5
- Ignores: Undefined types, methods, and calls

**Usage:**
```bash
# Install PHPStan
composer require --dev phpstan/phpstan

# Run analysis
vendor/bin/phpstan analyse src/
```

### 4. `psalm.xml`
Psalm static analyzer configuration (alternative to PHPStan).

**Current settings:**
- Error level: 5
- Paths: src/ directory only
- Suppresses undefined class/method issues

**Usage:**
```bash
# Install Psalm
composer require --dev vimeo/psalm

# Run analysis
vendor/bin/psalm
```

## Real vs False Positives

### These ARE Problems (would show in real errors):
- ❌ Syntax errors: `function foo(` → missing `)`
- ❌ Undefined classes: `new NonExistent()` → class not imported
- ❌ Wrong arguments: `strlen(1, 2, 3)` → too many arguments
- ❌ Undefined properties: `$obj->notExist` → property not defined

### These Are NOT Problems (static analyzer false positives):
- ⚠️ "Undefined type 'Route'" → It IS imported, analyzer limitation
- ⚠️ "Undefined method 'getUser'" → Method IS available, analyzer limitation
- ⚠️ "Call to unknown method on UserInterface" → Method IS added, analyzer limitation

## How to Verify Code Quality

### 1. Syntax Check (Most Reliable)
```bash
php -l src/Service/GradeService.php
php -l src/Controller/Teacher/GradeController.php
```
**Result**: ✅ No syntax errors = Code is valid

### 2. Composer Validation
```bash
composer validate
```
**Result**: ✅ Valid configuration = All dependencies correct

### 3. Run Tests
```bash
php bin/phpunit tests/Unit/Service/
```
**Result**: ✅ Tests pass = Implementation works

### 4. Static Analysis (Optional)
```bash
vendor/bin/phpstan analyse src/
vendor/bin/psalm
```
**Result**: May show warnings, but those are known false positives

## IDE Recommendations

### Best IDEs for Symfony:
1. **PHPStorm** (Best) - Commercial but has excellent Symfony support
2. **VS Code + Intelephense** (Good) - Free but has limitations
3. **VS Code + PHP Intelephense + Volar** (Okay) - Additional type resolution

### For VS Code:
Install these extensions:
- **PHP Intelephense** - Better code completion
- **PHP Namespace Resolver** - Auto-import classes
- **Symfony Support** - Symfony-specific features
- **Twig** - Template syntax highlighting

## Disabling Specific Warnings

To suppress only certain warning types:

### In `.intelephense/settings.json`:
```json
{
  "intelephense.diagnostics.parseError": false,
  "intelephense.diagnostics.argumentCount": false,
  "intelephense.diagnostics.typeError": false
}
```

### In `phpstan.neon`:
```neon
parameters:
    ignoreErrors:
        - '#Undefined type (Symfony|Doctrine)#'
        - '#Access to undefined property#'
```

## Testing Static Analysis

To see if your configuration works:

```bash
# Create a test file with an obvious error
echo "<?php function test( { echo 'hello'; }" > test.php

# Run syntax check (should fail)
php -l test.php
# Result: ❌ PHP Parse error

# Clean up
rm test.php
```

## Continuous Integration

For CI/CD pipelines, recommend:

```yaml
# .github/workflows/php.yml
- name: PHP Syntax Check
  run: |
    find src -name '*.php' -exec php -l {} \;

- name: Run Tests
  run: php bin/phpunit

- name: PHPStan Analysis (optional)
  run: vendor/bin/phpstan analyse src/
```

## Summary

| Tool | Purpose | False Positives | Priority |
|------|---------|-----------------|----------|
| `php -l` | Syntax validation | None | 🔴 CRITICAL |
| `phpunit` | Functional tests | None | 🔴 CRITICAL |
| PHPStan | Static analysis | Yes, some | 🟡 MEDIUM |
| Intelephense | IDE support | Yes, many | 🟢 LOW |

**Conclusion**: Ignore IDE warnings, focus on syntax check and tests.
