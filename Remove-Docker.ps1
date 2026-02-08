# Remove-Docker.ps1
# ============================================================================
# Docker Removal Script - Windows PowerShell Version
# ============================================================================
# Purpose: Automatically remove all Docker files from Symfony project
# Usage: .\Remove-Docker.ps1
# Requirements: PowerShell 5.0+, Run in project root directory
# ============================================================================

param(
    [switch]$SkipBackup = $false,
    [switch]$RemoveDocs = $false
)

# Color functions
function Write-Header {
    param([string]$Message)
    Write-Host ""
    Write-Host "============================================================" -ForegroundColor Blue
    Write-Host "  $Message" -ForegroundColor Blue
    Write-Host "============================================================" -ForegroundColor Blue
    Write-Host ""
}

function Write-Success {
    param([string]$Message)
    Write-Host "[OK] $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error-Custom {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

# Check if in project root
Write-Header "Docker Removal Script - Windows"

if (-not (Test-Path "composer.json")) {
    Write-Error-Custom "Not in project root! composer.json not found."
    exit 1
}

Write-Host "This script will remove all Docker-related files from your project."
Write-Host ""

# Confirmation
$continue = Read-Host "Continue? (y/n)"
if ($continue -notmatch "^[Yy]$") {
    Write-Warning "Cancelled."
    exit 0
}

Write-Host ""

# Step 1: Back up Docker files
Write-Header "Step 1: Creating Backup"

if (-not $SkipBackup) {
    $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
    $backupDir = "docker_backup_$timestamp"
    
    $filesToBackup = @(
        "Dockerfile",
        "docker-compose.yml",
        "compose.yaml",
        "compose.override.yaml",
        ".dockerignore"
    )
    
    New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
    
    foreach ($file in $filesToBackup) {
        if (Test-Path $file) {
            Copy-Item -Path $file -Destination "$backupDir\" -Force
            Write-Success "Backed up $file"
        }
    }
    
    if (Test-Path "docker") {
        Copy-Item -Path "docker" -Destination "$backupDir\" -Recurse -Force
        Write-Success "Backed up docker/ directory"
    }
    
    $backupContents = Get-ChildItem $backupDir -ErrorAction SilentlyContinue
    if ($backupContents.Count -gt 0) {
        Write-Success "All files backed up to: $backupDir"
        Write-Host "You can restore them later if needed."
    } else {
        Remove-Item $backupDir -Force -ErrorAction SilentlyContinue
        Write-Warning "No Docker files found to backup."
    }
} else {
    Write-Warning "Skipping backup (SkipBackup flag used)."
}

Write-Host ""

# Step 2: Remove Docker files
Write-Header "Step 2: Removing Docker Files"

$filesToRemove = @(
    "Dockerfile",
    "docker-compose.yml",
    "compose.yaml",
    "compose.override.yaml",
    ".dockerignore"
)

foreach ($file in $filesToRemove) {
    if (Test-Path $file) {
        Remove-Item -Path $file -Force
        Write-Success "Removed $file"
    }
}

Write-Host ""

# Remove docker directory
if (Test-Path "docker") {
    Remove-Item -Path "docker" -Recurse -Force
    Write-Success "Removed docker/ directory"
}

Write-Host ""

# Step 3: Optional - Remove Docker documentation
Write-Header "Step 3: Docker Documentation (Optional)"

$docsToRemove = @(
    "DOCKER_QUICK_REFERENCE.md",
    "DOCKER_SETUP.md"
)

$foundDocs = @()
foreach ($doc in $docsToRemove) {
    if (Test-Path $doc) {
        Write-Host "  - $doc"
        $foundDocs += $doc
    }
}

if ($foundDocs.Count -gt 0) {
    if ($RemoveDocs) {
        foreach ($doc in $foundDocs) {
            Remove-Item -Path $doc -Force
            Write-Success "Removed $doc"
        }
    } else {
        $removeDocs = Read-Host "Remove Docker documentation files? (y/n)"
        if ($removeDocs -match "^[Yy]$") {
            foreach ($doc in $foundDocs) {
                Remove-Item -Path $doc -Force
                Write-Success "Removed $doc"
            }
        } else {
            Write-Warning "Keeping Docker documentation."
        }
    }
} else {
    Write-Success "No Docker documentation files found."
}

Write-Host ""

# Step 4: Verify removal
Write-Header "Step 4: Verification"

$errors = 0

# Check files are removed
if (Test-Path "Dockerfile") {
    Write-Error-Custom "Dockerfile still exists!"
    $errors++
}

if (Test-Path "docker-compose.yml") {
    Write-Error-Custom "docker-compose.yml still exists!"
    $errors++
}

if (Test-Path "compose.yaml") {
    Write-Error-Custom "compose.yaml still exists!"
    $errors++
}

if (Test-Path ".dockerignore") {
    Write-Error-Custom ".dockerignore still exists!"
    $errors++
}

if (Test-Path "docker") {
    Write-Error-Custom "docker/ directory still exists!"
    $errors++
}

# Check critical files exist
if (-not (Test-Path "composer.json")) {
    Write-Error-Custom "composer.json not found!"
    $errors++
}

if (-not (Test-Path "src")) {
    Write-Error-Custom "src/ directory not found!"
    $errors++
}

if (-not (Test-Path "public")) {
    Write-Error-Custom "public/ directory not found!"
    $errors++
}

if (-not (Test-Path ".env")) {
    Write-Error-Custom ".env file not found!"
    $errors++
}

Write-Host ""

if ($errors -eq 0) {
    Write-Success "All Docker files removed successfully!"
    Write-Success "Project structure is intact."
} else {
    Write-Error-Custom "Found $errors issues. Please review above."
    exit 1
}

Write-Host ""

# Step 5: Create .env.production
Write-Header "Step 5: Environment Configuration"

if (-not (Test-Path ".env.production")) {
    $createEnv = Read-Host "Create .env.production file? (y/n)"
    if ($createEnv -match "^[Yy]$") {
        Copy-Item ".env" ".env.production"
        Write-Success "Created .env.production"
        Write-Host "Edit .env.production with your production settings:"
        Write-Host "  - APP_ENV=prod"
        Write-Host "  - APP_DEBUG=false"
        Write-Host "  - DATABASE_URL=your_production_db_url"
        Write-Host "  - APP_SECRET=your_32_char_secret"
    }
} else {
    Write-Warning ".env.production already exists."
}

Write-Host ""

# Final summary
# Final summary
Write-Host ""
Write-Host "============================================================" -ForegroundColor Blue
Write-Host "  Removal Complete!" -ForegroundColor Blue
Write-Host "============================================================" -ForegroundColor Blue
Write-Host ""

Write-Host "Summary:"
Write-Host "  Docker files removed"
Write-Host "  Project structure verified"
Write-Host ""
Write-Host "Next steps:"
Write-Host "  1. Review .env.production for production settings"
Write-Host "  2. Follow PRODUCTION_DEPLOYMENT_GUIDE.md"
Write-Host "  3. Deploy to your production server"
Write-Host ""
Write-Success "Ready for production deployment!"
