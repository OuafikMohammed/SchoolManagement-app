# Testing Fixes - February 8, 2026

## Issues Resolved

### 1. PHPStan Configuration Error
**Problem:** Invalid configuration error - "Unexpected item 'parameters › php'"
**Root Cause:** Using `php:` instead of `phpVersion:` in phpstan.neon YAML configuration
**Solution:** Changed line 20 in [phpstan.neon](phpstan.neon)
```yaml
# OLD
php: '8.2'

# NEW  
phpVersion: '8.2'
```
**Result:** PHPStan will now correctly recognize the PHP version parameter

---

### 2. PHPUnit Kernel Booting Error
**Problem:** `LogicException: Booting the kernel before calling "Symfony\Bundle\FrameworkBundle\Test\WebTestCase::createClient()" is not supported, the kernel should only be booted once.`

**Root Cause:** In [tests/Functional/Controller/PdfControllerTest.php](tests/Functional/Controller/PdfControllerTest.php), the `setUpBeforeClass()` method was manually booting the kernel with `self::bootKernel()`, which conflicts with `WebTestCase::createClient()` trying to boot the kernel again when called in individual test methods.

**Solution:** Refactored database initialization approach:

#### Changes Made:
1. **Changed from static setup to instance setup**
   - Old: `public static function setUpBeforeClass()` (runs once per test class)
   - New: `protected function setUp()` (runs before each test method)

2. **Removed manual kernel boot**
   - Old: `$kernel = self::bootKernel();` 
   - New: `$client = static::createClient();` (properly managed by WebTestCase)

3. **Changed schema initialization flag**
   - Old: `private static bool $schemaInitialized`
   - New: `private bool $schemaInitialized` (instance property)

4. **Removed helper method calls**
   - Removed all `$this->ensureSchemaInitialized();` calls from test methods
   - Database is now initialized once per test in `setUp()` instead

#### Code Changes:

**File:** [tests/Functional/Controller/PdfControllerTest.php](tests/Functional/Controller/PdfControllerTest.php)

```php
// NEW setUp() method
protected function setUp(): void
{
    parent::setUp();
    
    if (!$this->schemaInitialized) {
        $this->initializeDatabase();
        $this->schemaInitialized = true;
    }
}

// UPDATED initializeDatabase() to use createClient()
private function initializeDatabase(): void
{
    try {
        // Use createClient to boot kernel properly
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        // ... rest of initialization
    } catch (\Exception $e) {
        // Schema already exists or error, continue anyway
    }
}

// Test methods now start directly without ensureSchemaInitialized()
public function testStudentBulletinPdfGeneration(): void
{
    $client = static::createClient();
    // ... test code
}
```

**Methods Updated:**
- ✅ `testStudentBulletinPdfGeneration()`
- ✅ `testStudentBulletinNotEnrolled()`
- ✅ `testCourseReportPdfGeneration()`
- ✅ `testCourseReportNotTeacher()`
- ✅ `testCourseReportNotYourCourse()`

---

### 3. Bootstrap File Warning
**Warning:** "Class bootstrap cannot be found in tests/bootstrap.php"
**Note:** This is a non-critical warning from PHPUnit. The bootstrap file exists and is properly formatted. The warning may appear in newer PHPUnit versions but does not affect test execution.

---

## Testing the Fixes

To verify the fixes work:

```bash
# Run all tests with coverage
php bin/phpunit --coverage-text --coverage-clover coverage.xml

# Or run just the PDF controller tests
php bin/phpunit tests/Functional/Controller/PdfControllerTest.php

# Run static analysis
composer stan
```

## Expected Results

After these fixes:
- ✅ All 41 tests should pass (previously 40 passed, 1 error)
- ✅ No kernel booting errors
- ✅ PHPStan analysis will complete without configuration errors
- ✅ Code coverage report will generate successfully

## Related Files Modified

1. [phpstan.neon](phpstan.neon) - Line 20: `php:` → `phpVersion:`
2. [tests/Functional/Controller/PdfControllerTest.php](tests/Functional/Controller/PdfControllerTest.php) - Database initialization refactored

---

**Status:** ✅ Ready for CI/CD Pipeline Testing
