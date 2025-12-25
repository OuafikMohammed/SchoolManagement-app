#!/bin/bash
set -e

echo "🔍 Running pre-commit checks..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Track failures
FAILED=0

# 1. Composer validation
echo -e "${YELLOW}✓ Validating composer.json...${NC}"
if ! composer validate > /dev/null 2>&1; then
    echo -e "${RED}✗ Composer validation failed${NC}"
    FAILED=$((FAILED + 1))
else
    echo -e "${GREEN}✓ Composer validation passed${NC}"
fi

# 2. PHP syntax check
echo -e "${YELLOW}✓ Checking PHP syntax...${NC}"
if ! php -l bin/console > /dev/null 2>&1; then
    echo -e "${RED}✗ PHP syntax check failed${NC}"
    FAILED=$((FAILED + 1))
else
    find src -name "*.php" -exec php -l {} \; > /dev/null 2>&1 || {
        echo -e "${RED}✗ PHP files have syntax errors${NC}"
        FAILED=$((FAILED + 1))
    }
    if [ $FAILED -lt 1 ]; then
        echo -e "${GREEN}✓ PHP syntax check passed${NC}"
    fi
fi

# 3. Code style check (optional, continues on error)
echo -e "${YELLOW}✓ Checking code style...${NC}"
if ! composer cs-check > /dev/null 2>&1; then
    echo -e "${YELLOW}⚠ Code style issues found (not blocking)${NC}"
else
    echo -e "${GREEN}✓ Code style check passed${NC}"
fi

# 4. Static analysis (optional, continues on error)
echo -e "${YELLOW}✓ Running static analysis...${NC}"
if ! composer stan > /dev/null 2>&1; then
    echo -e "${YELLOW}⚠ Static analysis issues found (not blocking)${NC}"
else
    echo -e "${GREEN}✓ Static analysis passed${NC}"
fi

# 5. Unit tests (if database available)
if command -v mysql &> /dev/null; then
    echo -e "${YELLOW}✓ Running tests...${NC}"
    if ! php bin/phpunit --testdox > /dev/null 2>&1; then
        echo -e "${RED}✗ Tests failed${NC}"
        FAILED=$((FAILED + 1))
    else
        echo -e "${GREEN}✓ Tests passed${NC}"
    fi
fi

# Summary
echo ""
if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✅ All critical checks passed!${NC}"
    exit 0
else
    echo -e "${RED}❌ Some checks failed ($FAILED)${NC}"
    exit 1
fi
