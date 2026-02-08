# Pre-Production Deployment Checklist
## Symfony 7.4 School Management Application

**Document:** Comprehensive verification checklist  
**Purpose:** Ensure all requirements are met before deploying to production  
**Status:** Use this to track your deployment progress

---

## Phase 1: Docker Removal ✓/✗

### Files Removed
- [ ] `Dockerfile` - Removed
- [ ] `docker-compose.yml` - Removed
- [ ] `compose.yaml` - Removed
- [ ] `compose.override.yaml` - Removed
- [ ] `.dockerignore` - Removed
- [ ] `docker/php/Dockerfile` - Removed
- [ ] `docker/php/php.ini` - Removed (or archived)
- [ ] `docker/php/opcache.ini` - Removed (or archived)
- [ ] `docker/nginx/default.conf` - Removed (or archived)
- [ ] `docker/` directory - Completely removed

### Project Integrity
- [ ] `composer.json` - Still present and valid
- [ ] `src/` directory - Still present with all controllers
- [ ] `config/` directory - Still present with all configuration
- [ ] `public/` directory - Still present with index.php
- [ ] `templates/` directory - Still present with all Twig files
- [ ] `.env` - Still present and readable
- [ ] `vendor/` directory - Still present

### Verification Commands
```bash
# Run these to verify
ls -la | grep -i docker          # Should show nothing
php bin/console about             # Should work without errors
composer install --no-dev         # Should complete successfully
```

- [ ] No Docker files found with `ls -la | grep -i docker`
- [ ] `php bin/console about` executes without errors
- [ ] `composer install --no-dev` completes successfully

---

## Phase 2: Server Selection & Provisioning ✓/✗

### Hosting Provider Selected
- [ ] Provider chosen: ___________________
- [ ] Domain name: ___________________
- [ ] Server IP address: ___________________
- [ ] SSH access configured
- [ ] Root or sudo access available

### Server Access Verified
```bash
# Test SSH connection
ssh user@server_ip
```

- [ ] Can SSH into server
- [ ] `sudo` commands work without password (optional but recommended)
- [ ] Server is running Ubuntu 22.04 LTS (or compatible Linux)

---

## Phase 3: System Dependencies ✓/✗

### System Updates
```bash
sudo apt update && sudo apt upgrade -y
```

- [ ] System packages updated
- [ ] Build tools installed: `sudo apt install -y build-essential`

### PHP 8.2+ Installation
```bash
sudo apt install -y php8.2 php8.2-fpm php8.2-cli [extensions...]
php -v
php -m | grep pdo
```

- [ ] PHP 8.2+ installed
- [ ] `php -v` shows PHP 8.2 or higher
- [ ] Required extensions installed:
  - [ ] `php8.2-fpm`
  - [ ] `php8.2-mysql` (or PostgreSQL)
  - [ ] `php8.2-pdo`
  - [ ] `php8.2-gd`
  - [ ] `php8.2-intl`
  - [ ] `php8.2-zip`
  - [ ] `php8.2-mbstring`
  - [ ] `php8.2-curl`
  - [ ] `php8.2-xml`
  - [ ] `php8.2-bcmath`
  - [ ] `php8.2-json`

### Database Server
- [ ] MySQL 8.0 OR PostgreSQL 16 installed
- [ ] Database service running: `sudo systemctl status mysql` (or postgresql)
- [ ] Database service enabled: `sudo systemctl enable mysql`

### Web Server
- [ ] Nginx OR Apache installed
- [ ] Web server service running: `sudo systemctl status nginx`
- [ ] Web server service enabled: `sudo systemctl enable nginx`

### Composer Installation
```bash
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
composer --version
```

- [ ] Composer installed globally
- [ ] `composer --version` works

---

## Phase 4: Application Deployment ✓/✗

### Application Directory
```bash
sudo mkdir -p /var/www/school-management
sudo chown -R $USER:$USER /var/www/school-management
```

- [ ] Application directory created: `/var/www/school-management`
- [ ] Directory ownership correct
- [ ] Directory permissions correct (755)

### Code Deployment
```bash
# Option A: Git clone
git clone https://your-repo.git /var/www/school-management

# Option B: Upload via SCP
# scp -r ./SchoolManagement-app/* user@server:/var/www/school-management/
```

- [ ] Application code deployed to `/var/www/school-management`
- [ ] Git repository initialized (if using git) OR files uploaded (if using SCP)
- [ ] `.git` directory accessible for deployments (if applicable)

### Docker Files Removed (Production)
```bash
cd /var/www/school-management
rm -f Dockerfile docker-compose.yml compose.yaml .dockerignore
rm -rf docker/
```

- [ ] Docker files removed from production directory also
- [ ] Verified: `ls -la | grep -i docker` shows nothing

---

## Phase 5: Environment Configuration ✓/✗

### Create .env.production
```bash
cp /var/www/school-management/.env /var/www/school-management/.env.production
```

- [ ] `.env.production` file created
- [ ] `.env.production` is readable by web server
- [ ] `.env.production` is NOT world-readable: `chmod 640`

### Configure Environment Variables

**App Settings:**
```dotenv
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=YOUR_GENERATED_32_CHAR_STRING
DEFAULT_URI=https://yourdomain.com
APP_SHARE_DIR=var/share
```

- [ ] `APP_ENV=prod`
- [ ] `APP_DEBUG=false`
- [ ] `APP_SECRET` - Generated 32+ character random string
- [ ] `DEFAULT_URI` - Set to your domain with HTTPS

### Database Configuration

**Selected Database Type:**
- [ ] MySQL 8.0
- [ ] PostgreSQL 16
- [ ] Other: ___________________

**Database URL Set:**
```
For MySQL:
DATABASE_URL="mysql://username:password@127.0.0.1:3306/school_management?serverVersion=8.0&charset=utf8mb4"

For PostgreSQL:
DATABASE_URL="postgresql://username:password@127.0.0.1:5432/school_management?serverVersion=16&charset=utf8"
```

- [ ] `DATABASE_URL` configured for chosen database
- [ ] Database credentials are strong passwords
- [ ] Connection tested locally: `php bin/console doctrine:database:validate-schema`

### Mailer Configuration
```dotenv
MAILER_DSN=smtp://username:password@smtp.example.com:587?encryption=tls
```

- [ ] `MAILER_DSN` configured (or set to `null://null` if not using email)

### Messenger Configuration
```dotenv
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```

- [ ] `MESSENGER_TRANSPORT_DSN` configured

---

## Phase 6: Database Setup ✓/✗

### Create Database

**For MySQL:**
```bash
mysql -u root -p
# CREATE DATABASE school_management;
# CREATE USER 'school_user'@'localhost' IDENTIFIED BY 'strong_password';
# GRANT ALL PRIVILEGES ON school_management.* TO 'school_user'@'localhost';
# FLUSH PRIVILEGES;
```

**For PostgreSQL:**
```bash
sudo -u postgres psql
# CREATE DATABASE school_management;
# CREATE USER school_user WITH PASSWORD 'strong_password';
# GRANT ALL PRIVILEGES ON DATABASE school_management TO school_user;
```

- [ ] Database created: `school_management`
- [ ] Database user created with secure password
- [ ] User has ALL PRIVILEGES on the database
- [ ] Can connect: `php bin/console doctrine:database:create --if-not-exists`

### Install Composer Dependencies
```bash
cd /var/www/school-management
composer install --no-dev --optimize-autoloader
```

- [ ] `composer install` completed successfully
- [ ] `vendor/` directory created with all dependencies
- [ ] No errors in composer output
- [ ] Autoloader generated: `vendor/autoload.php` exists

### Run Database Migrations
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

- [ ] All migrations executed successfully
- [ ] Database schema created/updated
- [ ] No SQL errors in output
- [ ] Schema validates: `php bin/console doctrine:schema:validate` shows "OK"

### Seed Initial Data (if applicable)
```bash
php bin/console doctrine:fixtures:load --no-interaction
```

- [ ] Initial data loaded (if fixtures exist)
- [ ] Or: Skipped (if no fixtures needed)

---

## Phase 7: File Permissions & Security ✓/✗

### Directory Ownership
```bash
cd /var/www/school-management
sudo chown -R www-data:www-data /var/www/school-management
```

- [ ] All files owned by `www-data` user
- [ ] All files owned by `www-data` group

### Directory Permissions
```bash
sudo chmod -R 755 /var/www/school-management
sudo chmod -R 775 /var/www/school-management/var
sudo chmod -R 775 /var/www/school-management/public
```

- [ ] Public web root: `755` permissions
- [ ] `var/` directory: `775` permissions (writable by web server)
- [ ] `public/` directory: `755` permissions
- [ ] All files in `var/cache/` and `var/log/` are writable

### Environment File Security
```bash
sudo chmod 640 /var/www/school-management/.env.production
sudo chmod 640 /var/www/school-management/.env
ls -la .env*  # Check permissions
```

- [ ] `.env.production` - `640` permissions (not world-readable)
- [ ] `.env` - `640` permissions (not world-readable)
- [ ] `config/` directory - Not world-writable

### Disable Dangerous Functions
```bash
# Check php.ini for disable_functions
grep "disable_functions" /etc/php/8.2/fpm/php.ini
```

- [ ] Dangerous functions disabled in `php.ini`
- [ ] Must include: `exec, passthru, shell_exec, system, proc_open, popen`

---

## Phase 8: Web Server Configuration ✓/✗

### Nginx Configuration (if using Nginx)

File: `/etc/nginx/sites-available/school-management`

- [ ] Server block created
- [ ] `server_name` set to your domain
- [ ] `root` points to `/var/www/school-management/public`
- [ ] `index index.php` configured
- [ ] PHP-FPM upstream configured: `upstream php_backend { server 127.0.0.1:9000; }`
- [ ] Rewrite rules for Symfony: `try_files $uri $uri/ /index.php$is_args$args;`
- [ ] Site enabled: `sudo ln -s /etc/nginx/sites-available/school-management /etc/nginx/sites-enabled/`
- [ ] Default site disabled: `sudo rm /etc/nginx/sites-enabled/default`
- [ ] Nginx config tested: `sudo nginx -t` shows "OK"
- [ ] Nginx restarted: `sudo systemctl restart nginx`

### Apache Configuration (if using Apache)

File: `/etc/apache2/sites-available/school-management.conf`

- [ ] Virtual host created
- [ ] `ServerName` set to your domain
- [ ] `DocumentRoot` points to `/var/www/school-management/public`
- [ ] `mod_rewrite` enabled
- [ ] `.htaccess` rules present or inline
- [ ] Site enabled: `sudo a2ensite school-management`
- [ ] Default site disabled: `sudo a2dissite 000-default`
- [ ] Apache config tested: `sudo apache2ctl configtest` shows "OK"
- [ ] Apache restarted: `sudo systemctl restart apache2`

### PHP-FPM Configuration

```bash
sudo systemctl status php8.2-fpm
sudo systemctl enable php8.2-fpm
```

- [ ] PHP-FPM service running
- [ ] PHP-FPM service enabled (starts on boot)
- [ ] PHP-FPM socket/port accessible: `127.0.0.1:9000`

---

## Phase 9: SSL/HTTPS Configuration ✓/✗

### Let's Encrypt Installation
```bash
# For Nginx
sudo apt install -y certbot python3-certbot-nginx
# For Apache
sudo apt install -y certbot python3-certbot-apache
```

- [ ] Certbot installed
- [ ] Python plugin installed for your web server

### SSL Certificate Generation
```bash
sudo certbot certonly --nginx \
    -d yourdomain.com -d www.yourdomain.com \
    --email your-email@example.com \
    --agree-tos --non-interactive
```

- [ ] SSL certificate generated successfully
- [ ] Certificate location: `/etc/letsencrypt/live/yourdomain.com/`
- [ ] Certificate files accessible:
  - [ ] `fullchain.pem`
  - [ ] `privkey.pem`

### HTTPS Web Server Configuration
- [ ] HTTPS (port 443) configured in web server
- [ ] SSL certificates paths correct
- [ ] HTTP (port 80) redirects to HTTPS
- [ ] Web server restarted with HTTPS config

### SSL Auto-Renewal
```bash
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
sudo certbot renew --dry-run
```

- [ ] Certbot timer enabled
- [ ] Dry-run renewal test successful
- [ ] Auto-renewal configured

### Test HTTPS
```bash
curl -I https://yourdomain.com
```

- [ ] HTTPS connection successful
- [ ] No SSL/TLS warnings
- [ ] Page returns HTTP 200 or redirects properly

---

## Phase 10: Cache & Assets ✓/✗

### Clear Production Caches
```bash
cd /var/www/school-management
php bin/console cache:clear --env=prod
```

- [ ] Cache cleared: `rm -rf var/cache/prod/*`
- [ ] No errors in output
- [ ] Cache directory writable by web server

### Warm Up Caches (Recommended)
```bash
php bin/console cache:warmup --env=prod
```

- [ ] Cache warmed successfully
- [ ] `var/cache/prod/` populated with compiled classes

### Compile Assets
```bash
php bin/console asset-map:compile  # For Symfony 7+
# OR
npm run build  # If using npm for assets
```

- [ ] Assets compiled successfully
- [ ] `public/assets/` or equivalent contains compiled assets
- [ ] No JavaScript errors in browser console

---

## Phase 11: Verify Application ✓/✗

### Application Commands
```bash
php bin/console about
php bin/console doctrine:schema:validate
php bin/console cache:clear --env=prod
```

- [ ] `php bin/console about` - No errors
- [ ] `doctrine:schema:validate` - Schema is valid
- [ ] `cache:clear` - Executes without errors

### Web Server Test
```bash
curl -I https://yourdomain.com
curl https://yourdomain.com/health  # If health endpoint exists
```

- [ ] Homepage loads (HTTP 200)
- [ ] No 404 or 500 errors
- [ ] CSS/JS assets load (check browser DevTools)

### Login Test
- [ ] Can navigate to login page
- [ ] Can attempt login with test credentials
- [ ] Forms submit correctly
- [ ] Session handling works

### Database Test
- [ ] Can retrieve data from database
- [ ] Forms submit and save data
- [ ] No database connection errors in logs

### Event/Email Test (if applicable)
- [ ] Automated emails send correctly (or queue successfully)
- [ ] Event listeners trigger properly
- [ ] Messenger jobs process correctly

---

## Phase 12: Monitoring & Logs ✓/✗

### Log Files Configured
- [ ] Nginx error log: `/var/log/nginx/school-management-error.log`
- [ ] Nginx access log: `/var/log/nginx/school-management-access.log`
- [ ] PHP error log: `/var/log/php/error.log`
- [ ] Symfony logs: `/var/www/school-management/var/log/prod.log`

### Systemd Services Configured
```bash
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql  # or postgresql
```

- [ ] All services running
- [ ] All services enabled (start on boot)
- [ ] No service errors in status

### Real-Time Log Monitoring
```bash
tail -f /var/log/nginx/school-management-error.log
tail -f /var/log/php/error.log
```

- [ ] Can monitor logs in real-time
- [ ] No errors appearing during normal operation
- [ ] Check logs during test operations

---

## Phase 13: Backup & Recovery ✓/✗

### Database Backup Script
```bash
sudo nano /usr/local/bin/backup-db.sh
```

- [ ] Backup script created
- [ ] Script is executable: `sudo chmod +x /usr/local/bin/backup-db.sh`
- [ ] Test backup runs successfully

### Automated Database Backups
```bash
sudo crontab -e
# 0 2 * * * /usr/local/bin/backup-db.sh
```

- [ ] Cron job created for daily backups (2 AM)
- [ ] Backup directory exists and has permissions
- [ ] Backup files being created daily

### Application Backup
- [ ] Application files backed up (at minimum weekly)
- [ ] Backup location: `/backups/app/` or cloud storage
- [ ] Backup retention policy defined (e.g., keep 30 days)

### Disaster Recovery Plan
- [ ] Documented: How to restore from backup
- [ ] Tested: Full restore from recent backup works
- [ ] Time estimated: _____ minutes to full recovery

---

## Phase 14: Security Hardening ✓/✗

### Firewall Configuration
```bash
sudo ufw enable
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw status
```

- [ ] UFW firewall enabled
- [ ] SSH (22) allowed
- [ ] HTTP (80) allowed
- [ ] HTTPS (443) allowed
- [ ] All other ports blocked

### SSH Key Authentication
```bash
# On local machine
ssh-copy-id -i ~/.ssh/id_ed25519.pub user@server

# On server
sudo nano /etc/ssh/sshd_config
# PasswordAuthentication no
# PubkeyAuthentication yes
```

- [ ] SSH keys generated (locally)
- [ ] Public key copied to server (`~/.ssh/authorized_keys`)
- [ ] Password authentication disabled
- [ ] SSH service restarted: `sudo systemctl restart ssh`

### System Updates
```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

- [ ] System fully updated
- [ ] Automatic security updates enabled
- [ ] Update schedule: Daily at _____ (default: 6 AM)

### Security Headers
- [ ] `Strict-Transport-Security` enabled
- [ ] `X-Frame-Options: DENY` set
- [ ] `X-Content-Type-Options: nosniff` set
- [ ] `X-XSS-Protection` set

---

## Phase 15: Performance Optimization ✓/✗

### OpCache Configuration
```bash
sudo nano /etc/php/8.2/fpm/conf.d/10-opcache.ini
# Verify settings for production
```

- [ ] OpCache enabled (`opcache.enable=1`)
- [ ] Memory sufficient: `opcache.memory_consumption=256`
- [ ] JIT enabled (PHP 8.2+): `opcache.jit=1235`
- [ ] JIT buffer: `opcache.jit_buffer_size=256M`

### Gzip Compression
```bash
# For Nginx - already configured in earlier steps
# For Apache
sudo a2enmod deflate
sudo systemctl restart apache2
```

- [ ] Gzip compression enabled
- [ ] Compresses: text/css, text/javascript, application/json
- [ ] Minimum size: 1000 bytes

### HTTP Caching Headers
- [ ] Static assets: `Cache-Control: public, max-age=2592000` (30 days)
- [ ] Dynamic pages: `Cache-Control: private, must-revalidate`
- [ ] ETags enabled for static files

### Database Indexes
```bash
# For MySQL
SHOW INDEX FROM student;
-- Add indexes for frequently searched columns

# For PostgreSQL
\d student
-- Add indexes if missing
```

- [ ] Database indexes created for:
  - [ ] `id` (primary key)
  - [ ] `email` (user lookups)
  - [ ] `created_at` (sorting)
  - [ ] Other frequently filtered columns

---

## Phase 16: Testing & QA ✓/✗

### Functionality Tests
- [ ] Home page loads
- [ ] User login works
- [ ] User registration works (if applicable)
- [ ] Dashboard displays correctly
- [ ] CRUD operations work (Create/Read/Update/Delete)
- [ ] File uploads work (if applicable)
- [ ] PDF generation works (if applicable)
- [ ] Redirects work correctly
- [ ] 404 errors handled properly
- [ ] Form validations work

### Performance Tests
```bash
curl -w "Request took %{time_total}s\n" https://yourdomain.com
```

- [ ] Page load time: < 2 seconds
- [ ] Database queries: < 100ms
- [ ] No memory leaks after heavy usage

### Security Tests
- [ ] SQL injection attempted - blocked ✓
- [ ] XSS attempts - blocked ✓
- [ ] CSRF protection active ✓
- [ ] Sensitive files not accessible (e.g., `.env`, `vendor/`)
- [ ] Direct access to private directory blocked

### Cross-Browser Testing
- [ ] Chrome/Chromium - Works
- [ ] Firefox - Works
- [ ] Safari - Works
- [ ] Edge - Works
- [ ] Mobile browsers - Works

### Device Testing
- [ ] Desktop (1920x1080) - Works
- [ ] Tablet (768x1024) - Works
- [ ] Mobile (375x667) - Works

---

## Final Verification Checklist ✓/✗

### Pre-Launch
- [ ] All above checklists completed
- [ ] No critical errors in logs
- [ ] All services running
- [ ] DNS points to server IP
- [ ] Domain HTTPS certificate valid
- [ ] Database backups automated and tested
- [ ] Monitoring alerts configured
- [ ] Team trained on deployment process
- [ ] Runbook/documentation updated
- [ ] Emergency contact list ready

### Post-Launch (24 Hours)
- [ ] Monitor error logs - no spike in errors
- [ ] Monitor server resources - no bottlenecks
- [ ] Check database size growth - normal rate
- [ ] Verify backups running automatically
- [ ] Check user login/registration working
- [ ] Monitor application performance metrics
- [ ] Verify email notifications sending (if applicable)

### Post-Launch (1 Week)
- [ ] Performance remains stable
- [ ] No security incidents
- [ ] User feedback positive
- [ ] All automated processes running
- [ ] Backup verification successful

---

## Issue Tracking

### Issues Found During Deployment

| Issue | Status | Notes | Resolution |
|-------|--------|-------|-----------|
| | | | |
| | | | |
| | | | |

---

## Sign-Off

**Deployment Date:** ___________________  
**Deployed By:** ___________________  
**Verified By:** ___________________  
**Status:** ✓ PRODUCTION READY / ✗ ISSUES REMAINING

**Issues Remaining:** (if any)
```




```

**Release Notes:**
```




```

---

## Next Steps

1. Monitor application for first 24 hours
2. Watch error logs for any issues
3. Perform user acceptance testing (UAT)
4. Train team on production procedures
5. Set up ongoing maintenance schedule

---

**Questions?** Refer to **PRODUCTION_DEPLOYMENT_GUIDE.md**

