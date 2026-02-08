# Docker Removal Quick Reference
## Symfony 7.4 School Management App

**Date:** February 2026  
**Purpose:** Quick steps to remove all Docker configurations and prepare for production

---

## Quick Removal Steps

### Step 1: List Files to Remove
```bash
# Navigate to project root
cd /var/www/school-management  # Or your project directory

# View Docker files before deletion
ls -la | grep -E "Docker|docker|compose"
ls -la docker/
```

### Step 2: Remove Docker Files
```bash
# Remove root-level Docker files
rm -f Dockerfile
rm -f docker-compose.yml
rm -f compose.yaml
rm -f compose.override.yaml
rm -f .dockerignore

# Remove Docker directory completely
rm -rf docker/

# Verify removal
ls -la | grep -i docker
```

### Step 3: Update Documentation (Optional)

Remove or update these files:
```bash
# Optional: Archive or remove Docker docs
rm -f DOCKER_QUICK_REFERENCE.md  # or mv to archive/
rm -f DOCKER_SETUP.md            # or mv to archive/
```

### Step 4: Verify Application Integrity

```bash
# Check no critical errors
ls -la src/
ls -la config/

# Verify .env exists and is intact
cat .env | head -20

# Check composer.json
cat composer.json | head -50
```

---

## Environment Setup for Production

### Quick Setup Command

```bash
# Create .env.production from template
cp .env .env.production

# Edit for your production server
nano .env.production
```

### .env.production Template

```dotenv
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=YOUR_32_CHAR_RANDOM_STRING_HERE
DEFAULT_URI=https://yourdomain.com

# Choose ONE database option
DATABASE_URL="mysql://user:password@localhost:3306/school_management?serverVersion=8.0&charset=utf8mb4"
# OR
# DATABASE_URL="postgresql://user:password@localhost:5432/school_management?serverVersion=16&charset=utf8"

MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
MAILER_DSN=smtp://username:password@smtp.mailer.com:587?encryption=tls
```

---

## Verification Checklist

After removing Docker:

- [ ] `Dockerfile` removed
- [ ] `docker-compose.yml` removed
- [ ] `docker/` directory removed
- [ ] `.dockerignore` removed
- [ ] `.env.production` created
- [ ] `composer.json` intact
- [ ] `src/` directory intact
- [ ] `public/` directory intact
- [ ] Can run `composer install --no-dev`
- [ ] Can run `php bin/console about`

---

## Verify No Critical Docker Dependencies

```bash
# Search for any Docker-specific code
grep -r "docker" src/ --include="*.php" 2>/dev/null | wc -l
grep -r "container" config/ --include="*.yaml" 2>/dev/null | wc -l

# Expected: Should return 0 or very few results
```

---

## Next: Production Server Setup

Once Docker is removed, follow the **PRODUCTION_DEPLOYMENT_GUIDE.md** for:

1. **Server Requirements Installation** (Phase 1)
2. **Application Deployment** (Phase 2)
3. **Database Configuration** (Phase 3)
4. **Web Server Setup** (Phase 4)
5. **Permissions & Security** (Phase 5)
6. **SSL Configuration** (Phase 6)
7. **PHP Configuration** (Phase 7)
8. **Application Finalization** (Phase 8)

---

## Git Cleanup

If using git, remove Docker files from version control:

```bash
# Remove from git (but keep locally during migration)
git rm --cached Dockerfile docker-compose.yml .dockerignore
git rm -r --cached docker/

# Update .gitignore if needed
echo "Dockerfile" >> .gitignore
echo "docker-compose*.yml" >> .gitignore
echo "docker/" >> .gitignore

# Commit the removal
git commit -m "Remove Docker configuration for direct server hosting"
git push origin main
```

---

## Rollback (If Needed)

If you need to restore Docker files:

```bash
# Restore from git
git checkout HEAD -- Dockerfile docker-compose.yml .dockerignore docker/

# Or restore from backup
tar -xzf backup-with-docker.tar.gz
```

---

## Common Errors & Solutions

| Error | Solution |
|-------|----------|
| `composer install` fails | Ensure PHP 8.2+ is installed with ext-zip, ext-mbstring |
| `php bin/console` not found | Check PHP path: `which php` |
| Database migration fails | Check DATABASE_URL in .env.production |
| Permission denied on var/ | Run: `sudo chown -R www-data:www-data /var/www/school-management` |

---

## Questions?

Refer to: **PRODUCTION_DEPLOYMENT_GUIDE.md**

Time estimate for full production setup: **30-45 minutes** on a fresh VPS

