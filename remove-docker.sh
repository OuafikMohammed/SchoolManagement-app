#!/bin/bash

# ============================================================================
# Docker Removal Script
# ============================================================================
# Purpose: Automatically remove all Docker files from Symfony project
# Usage: bash remove-docker.sh
# ============================================================================

set -e  # Exit on error

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'  # No Color

# Functions
print_header() {
    echo -e "${BLUE}===================================================${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}===================================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Check if in project root
if [ ! -f "composer.json" ]; then
    print_error "Not in project root! composer.json not found."
    exit 1
fi

print_header "Docker Removal Script"
echo "This script will remove all Docker-related files from your project."
echo ""
read -p "Continue? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Cancelled."
    exit 1
fi

# Step 1: Back up Docker files
print_header "Step 1: Creating Backup"
BACKUP_DIR="docker_backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

if [ -f "Dockerfile" ]; then
    cp Dockerfile "$BACKUP_DIR/" && print_success "Backed up Dockerfile"
fi

if [ -f "docker-compose.yml" ]; then
    cp docker-compose.yml "$BACKUP_DIR/" && print_success "Backed up docker-compose.yml"
fi

if [ -f "compose.yaml" ]; then
    cp compose.yaml "$BACKUP_DIR/" && print_success "Backed up compose.yaml"
fi

if [ -f "compose.override.yaml" ]; then
    cp compose.override.yaml "$BACKUP_DIR/" && print_success "Backed up compose.override.yaml"
fi

if [ -f ".dockerignore" ]; then
    cp .dockerignore "$BACKUP_DIR/" && print_success "Backed up .dockerignore"
fi

if [ -d "docker" ]; then
    cp -r docker "$BACKUP_DIR/" && print_success "Backed up docker/ directory"
fi

if [ "$(ls -A $BACKUP_DIR)" ]; then
    print_success "All files backed up to: $BACKUP_DIR"
    echo "You can restore them later if needed."
else
    rm -d "$BACKUP_DIR"
    print_warning "No Docker files found to backup."
fi

echo ""

# Step 2: Remove Docker files
print_header "Step 2: Removing Docker Files"

# Remove root-level files
for file in Dockerfile docker-compose.yml compose.yaml compose.override.yaml .dockerignore; do
    if [ -f "$file" ]; then
        rm -f "$file" && print_success "Removed $file"
    fi
done

echo ""

# Remove docker directory
if [ -d "docker" ]; then
    rm -rf docker && print_success "Removed docker/ directory"
fi

echo ""

# Step 3: Optional - Remove Docker documentation
print_header "Step 3: Docker Documentation (Optional)"
echo "Found Docker-related documentation files:"

docs_to_remove=()
if [ -f "DOCKER_QUICK_REFERENCE.md" ]; then
    echo "  - DOCKER_QUICK_REFERENCE.md"
    docs_to_remove+=("DOCKER_QUICK_REFERENCE.md")
fi

if [ -f "DOCKER_SETUP.md" ]; then
    echo "  - DOCKER_SETUP.md"
    docs_to_remove+=("DOCKER_SETUP.md")
fi

if [ ${#docs_to_remove[@]} -gt 0 ]; then
    read -p "Remove Docker documentation files? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        for doc in "${docs_to_remove[@]}"; do
            rm -f "$doc" && print_success "Removed $doc"
        done
    else
        print_warning "Keeping Docker documentation."
    fi
else
    print_success "No Docker documentation files found."
fi

echo ""

# Step 4: Verify removal
print_header "Step 4: Verification"

errors=0

# Check files are removed
for file in Dockerfile docker-compose.yml compose.yaml .dockerignore; do
    if [ -f "$file" ]; then
        print_error "$file still exists!"
        errors=$((errors + 1))
    fi
done

if [ -d "docker" ]; then
    print_error "docker/ directory still exists!"
    errors=$((errors + 1))
fi

# Check critical files exist
if [ ! -f "composer.json" ]; then
    print_error "composer.json not found!"
    errors=$((errors + 1))
fi

if [ ! -d "src" ]; then
    print_error "src/ directory not found!"
    errors=$((errors + 1))
fi

if [ ! -d "public" ]; then
    print_error "public/ directory not found!"
    errors=$((errors + 1))
fi

if [ ! -f ".env" ]; then
    print_error ".env file not found!"
    errors=$((errors + 1))
fi

echo ""

if [ $errors -eq 0 ]; then
    print_success "All Docker files removed successfully!"
    print_success "Project structure is intact."
else
    print_error "Found $errors issues. Please review above."
    exit 1
fi

echo ""

# Step 5: Create .env.production
print_header "Step 5: Environment Configuration"

if [ ! -f ".env.production" ]; then
    read -p "Create .env.production file? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        cp .env .env.production
        print_success "Created .env.production"
        echo "Edit .env.production with your production settings:"
        echo "  - APP_ENV=prod"
        echo "  - APP_DEBUG=false"
        echo "  - DATABASE_URL=your_production_db_url"
        echo "  - APP_SECRET=your_32_char_secret"
    fi
else
    print_warning ".env.production already exists."
fi

echo ""

# Final summary
print_header "Removal Complete!"
echo ""
echo "Summary:"
echo "  ✓ Docker files removed"
echo "  ✓ Project structure verified"
echo ""
echo "Next steps:"
echo "  1. Review .env.production for production settings"
echo "  2. Follow PRODUCTION_DEPLOYMENT_GUIDE.md"
echo "  3. Deploy to your production server"
echo ""
echo "Backup location: $BACKUP_DIR"
echo "  (Delete this folder once you're confident in the migration)"
echo ""
print_success "Ready for production deployment!"
