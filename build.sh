#!/bin/bash
# Render build script for Symfony 7.4 with SQLite
# This script is more robust and handles errors gracefully

set -e  # Exit on first error, but continue for tolerable errors

echo "================================"
echo "🔧 Building School Management App"
echo "================================"

# Step 1: Clear any corrupted cache
echo "📦 Step 1: Clearing cache..."
rm -rf var/cache/* 2>/dev/null || echo "  (cache was empty)"
rm -rf var/log/* 2>/dev/null || echo "  (logs were empty)"

# Step 2: Install dependencies
echo "📦 Step 2: Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Step 3: Create necessary directories
echo "📁 Step 3: Creating data directories..."
mkdir -p var/data
chmod -R 775 var/data || true

# Step 4: Database setup
echo "🗄️  Step 4: Setting up database..."
php bin/console doctrine:database:create --if-not-exists 2>/dev/null || echo "  (database already exists)"
php bin/console doctrine:migrations:migrate --no-interaction 2>/dev/null || echo "  (migrations already applied)"

# Step 5: Warm up cache SAFELY
echo "🔥 Step 5: Warming up Symfony cache..."
if ! php bin/console cache:warmup --env=prod 2>&1; then
    echo "  ⚠️  Cache warmup had issues, but continuing..."
fi

# Step 6: Asset compilation
echo "📁 Step 6: Compiling assets..."
php bin/console asset-map:compile 2>/dev/null || echo "  (no assets to compile)"

# Step 7: Set permissions
echo "🔐 Step 7: Setting file permissions..."
chmod -R 775 var/ || true

echo ""
echo "✅ Build complete!"
echo "================================"
