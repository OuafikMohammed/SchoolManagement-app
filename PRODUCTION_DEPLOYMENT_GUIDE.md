# Symfony 7.4 Production Deployment Guide
## Removing Docker & Hosting on Production Servers

**Document Version:** 1.0  
**Last Updated:** February 2026  
**Application:** School Management System  
**Target:** Symfony 7.4 with PHP 8.2+

---

## Table of Contents

1. [Part 1: Docker Removal](#part-1-docker-removal)
2. [Part 2: Production Hosting Setup](#part-2-production-hosting-setup)
3. [Part 3: Post-Deployment Configuration](#part-3-post-deployment-configuration)

---

# PART 1: DOCKER REMOVAL

## Step 1: Files to Remove

### Root-Level Docker Files
Remove these files from your project root:

```
- Dockerfile
- docker-compose.yml
- compose.yaml
- compose.override.yaml
- .dockerignore
```

### Docker Configuration Directory
Remove the entire directory:
```
- docker/                          # Complete directory
  ├── php/
  │   ├── Dockerfile
  │   ├── php.ini
  │   └── opcache.ini
  └── nginx/
      └── default.conf
```

### Docker-Related Documentation Files
Consider archiving or removing these (unless you want to keep for reference):
```
- DOCKER_QUICK_REFERENCE.md
- DOCKER_SETUP.md
- docker-compose.yml                    # (specific to Docker deployment)
```

## Step 2: Identify Configuration Changes

### Review Environment Files
Your `.env` file needs updates for production:

**Current Status:** ✓ GOOD - Your `.env` already has production-ready options:
- SQLite (development): Line 34
- MySQL (production): Line 37  
- MariaDB (production): Line 40
- PostgreSQL (production): Line 43

**Action Required:** No immediate changes needed, but you'll configure these for your hosting.

### Check for Docker-Specific Code References

Search your codebase for Docker-specific configurations:

```bash
# Search for Docker references
grep -r "docker" src/ --include="*.php" --include="*.yaml" --include="*.twig"
grep -r "container" config/ --include="*.yaml"
grep -r "localhost" . --include="*.env*"
grep -r "var/data" . --include="*.php" --include="*.yaml"
```

**Expected findings:** None critical - your Symfony app is framework-agnostic

### Check Nginx Configuration

Your Docker `docker/nginx/default.conf` contains Nginx settings that you'll need in production:

**Key Configuration Points:**
- Server port: 80 (or 443 for HTTPS)
- Root directory: `/var/www/public`
- PHP-FPM connection: Typically `127.0.0.1:9000`
- Fastcgi settings and location blocks

## Step 3: Update Documentation

Update references in these files:

### Files to Modify

1. **README.md** - Remove Docker setup instructions, add production hosting info
2. **INSTALLATION.md** - Update installation steps to use local PHP
3. **QUICKSTART.md** - Replace Docker commands with native commands
4. **Any CI/CD documentation** - Update deployment workflows

### Commands to Remove from Docs

```bash
# Remove these Docker-specific command references:
docker-compose up -d
docker-compose down
docker-compose exec php php bin/console
docker-compose logs -f
docker build
docker run
```

### Commands to Replace With

```bash
# Replace with native commands:
php bin/console                    # Instead of docker-compose exec php bin/console
composer install                  # Instead of docker-compose build
./bin/console cache:clear         # Clear cache
php -S localhost:8000 -t public/  # For local development (simple)
```

## Step 4: Verify No Critical Dependencies

Ensure your application doesn't depend on Docker-specific features:

✓ **Your app uses:**
- Symfony Framework (framework-agnostic) ✓
- Doctrine ORM (works on any server) ✓
- PDO drivers (MySQL, PostgreSQL, SQLite) ✓
- Standard PHP extensions (no Docker-only extensions) ✓

✓ **You're safe to remove Docker**

---

# PART 2: PRODUCTION HOSTING SETUP

## Recommended Hosting Options for Symfony 7.4

### 1. **Shared Hosting** (Budget-Friendly)
- **Best for:** Small applications, learning
- **Examples:** Bluehost, DreamHost, HostGator PHP+MySQL plans
- **Pros:** Cheap, easy setup, email hosting included
- **Cons:** Limited control, limited performance
- **Symfony Support:** Most support PHP 8.2+
- **Requirements:** SSH access, PHP 8.2+, MySQL/PostgreSQL support

### 2. **Virtual Private Server (VPS)** (Recommended for Production)
- **Best for:** Medium to large applications, more control
- **Examples:** DigitalOcean, Linode, Vultr, AWS Lightsail, Hetzner
- **Pros:** Full control, scalability, better performance
- **Cons:** Requires more technical knowledge
- **Price:** $5-50/month
- **Symfony Support:** Full support with all extensions available

### 3. **Cloud Platforms** (Advanced Scaling)
- **Best for:** Enterprise applications, high traffic
- **Examples:** AWS, Google Cloud, Azure, Heroku
- **Pros:** Auto-scaling, managed services, CDN integration
- **Cons:** More complex, potentially higher cost
- **Symfony Support:** Full support with specialized options

### 4. **Managed Symfony Hosting** (Semi-Managed)
- **Best for:** Symfony-focused developers
- **Examples:** Symfony Cloud, PlatformSH
- **Pros:** Pre-optimized for Symfony, auto-deployments
- **Cons:** More expensive
- **Price:** $50+/month

---

## Complete Step-by-Step Production Deployment

### Phase 1: Server Setup (Initial Configuration)

#### Step 1.1: Server Requirements

**Operating System:** Ubuntu 22.04 LTS (recommended)

**System Packages:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install essential tools
sudo apt install -y \
    curl \
    wget \
    git \
    htop \
    build-essential \
    net-tools

# Install Supervisor (for queue workers)
sudo apt install -y supervisor
```

#### Step 1.2: PHP 8.2+ Installation

```bash
# Add PHP repository
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# Install PHP 8.2 with required extensions
sudo apt install -y \
    php8.2 \
    php8.2-fpm \
    php8.2-cli \
    php8.2-dev \
    php8.2-mysql \
    php8.2-pdo \
    php8.2-gd \
    php8.2-intl \
    php8.2-zip \
    php8.2-mbstring \
    php8.2-curl \
    php8.2-xml \
    php8.2-bcmath \
    php8.2-iconv \
    php8.2-json \
    php8.2-readline \
    php8.2-sqlite3

# Verify PHP installation
php -v
php -m | grep -E "pdo|mysql|gd|intl"
```

#### Step 1.3: Database Installation

**Choose ONE option:**

**Option A: MySQL 8.0 (Recommended)**
```bash
sudo apt install -y mysql-server

# Secure installation
sudo mysql_secure_installation

# Start and enable service
sudo systemctl start mysql
sudo systemctl enable mysql

# Verify
mysql --version
```

**Option B: PostgreSQL (Alternative)**
```bash
sudo apt install -y postgresql postgresql-contrib

# Start and enable service
sudo systemctl start postgresql
sudo systemctl enable postgresql

# Verify
psql --version
```

#### Step 1.4: Web Server Installation

**Option A: Nginx (Recommended for Symfony)**
```bash
# Install Nginx
sudo apt install -y nginx

# Start and enable
sudo systemctl start nginx
sudo systemctl enable nginx

# Verify
nginx -v
```

**Option B: Apache (Alternative)**
```bash
# Install Apache with mod_php
sudo apt install -y apache2 libapache2-mod-php8.2

# Enable modules
sudo a2enmod php8.2
sudo a2enmod rewrite

# Start and enable
sudo systemctl start apache2
sudo systemctl enable apache2
```

#### Step 1.5: Composer Installation

```bash
# Download Composer installer
curl -sS https://getcomposer.org/installer -o composer-setup.php

# Verify integrity
HASH=`curl -sS https://composer.github.io/installer.sig`
echo $HASH

# Install globally
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# Verify installation
composer --version
```

---

### Phase 2: Application Deployment

#### Step 2.1: Create Application Directory

```bash
# Create directory
sudo mkdir -p /var/www/school-management
cd /var/www/school-management

# Set permissions (adjust for your user)
sudo chown -R $USER:$USER /var/www/school-management
chmod -R 755 /var/www/school-management
```

#### Step 2.2: Clone/Upload Application

**Option A: Clone from Git Repository**
```bash
cd /var/www/school-management
git clone https://your-repository-url.git .
cd /var/www/school-management
```

**Option B: Upload via SCP/FTP**
```bash
# From your local machine
scp -r ./SchoolManagement-app/* user@server:/var/www/school-management/
```

#### Step 2.3: Remove Docker Files

```bash
cd /var/www/school-management

# Remove Docker files
rm -f Dockerfile docker-compose.yml compose.yaml compose.override.yaml .dockerignore
rm -rf docker/

# Remove Docker documentation (optional)
rm -f DOCKER_QUICK_REFERENCE.md DOCKER_SETUP.md

# Verify removal
ls -la | grep -i docker
```

#### Step 2.4: Set Up Environment File

```bash
# Create production environment file
cp .env .env.production

# Edit for production
nano .env.production
```

**Key `.env.production` settings:**

```dotenv
###> symfony/framework-bundle ###
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=your-very-secret-random-string-here-min-32-chars
APP_SHARE_DIR=var/share
DEFAULT_URI=https://yourdomain.com
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
# Choose based on your database
# MySQL
DATABASE_URL="mysql://school_user:YourSecurePassword@127.0.0.1:3306/school_management?serverVersion=8.0&charset=utf8mb4"

# OR PostgreSQL
# DATABASE_URL="postgresql://school_user:YourSecurePassword@127.0.0.1:5432/school_management?serverVersion=16&charset=utf8"

# NOT SQLite for production
###< doctrine/doctrine-bundle ###

###> symfony/messenger ###
# Use Doctrine for async messages in production
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
###< symfony/messenger ###

###> symfony/mailer ###
# Configure your mail service
MAILER_DSN=smtp://username:password@smtp.mailer.com:587?encryption=tls
###< symfony/mailer ###
```

**IMPORTANT: Generate a secure `APP_SECRET`**
```bash
# Generate random string
php -r 'echo base64_encode(random_bytes(32));'
```

---

### Phase 3: Database Configuration

#### Step 3.1: Create Database and User

**For MySQL:**
```bash
# Connect to MySQL
mysql -u root -p

# In MySQL shell
CREATE DATABASE school_management;
CREATE USER 'school_user'@'localhost' IDENTIFIED BY 'YourSecurePassword123!';
GRANT ALL PRIVILEGES ON school_management.* TO 'school_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**For PostgreSQL:**
```bash
# Connect as postgres user
sudo -u postgres psql

# In PostgreSQL shell
CREATE DATABASE school_management;
CREATE USER school_user WITH PASSWORD 'YourSecurePassword123!';
GRANT ALL PRIVILEGES ON DATABASE school_management TO school_user;
\q
```

#### Step 3.2: Install Composer Dependencies

```bash
cd /var/www/school-management

# Install production dependencies (no dev packages)
composer install --no-dev --optimize-autoloader

# This will take 2-5 minutes depending on internet speed
```

#### Step 3.3: Create Database Schema

```bash
# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# OR if you prefer direct schema creation
php bin/console doctrine:database:create
php bin/console doctrine:schema:create

# Load initial data (if you have fixtures)
php bin/console doctrine:fixtures:load --no-interaction
```

#### Step 3.4: Verify Database Connection

```bash
# Test database connection
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:schema:validate
```

---

### Phase 4: Web Server Configuration

#### Option A: Nginx Configuration

Create `/etc/nginx/sites-available/school-management`:

```bash
sudo nano /etc/nginx/sites-available/school-management
```

**Content:**
```nginx
upstream php_backend {
    server 127.0.0.1:9000;
}

server {
    listen 80;
    listen [::]:80;
    
    server_name yourdomain.com www.yourdomain.com;
    
    # Redirect HTTP to HTTPS (after SSL setup)
    # return 301 https://$server_name$request_uri;
    
    root /var/www/school-management/public;
    index index.php;

    # Logs
    access_log /var/log/nginx/school-management-access.log;
    error_log /var/log/nginx/school-management-error.log;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css text/xml text/javascript 
               application/x-javascript application/xml+rss 
               application/javascript application/json;
    gzip_min_length 1000;

    # Static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Disable access to dot files
    location ~ /\. {
        deny all;
    }

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass php_backend;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        
        # Standard FastCGI parameters
        include fastcgi_params;
        
        # Timeouts
        fastcgi_connect_timeout 60s;
        fastcgi_send_timeout 300s;
        fastcgi_read_timeout 300s;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
    }

    # Symfony rewrite
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }
}

# HTTPS Configuration (after SSL certificate installed)
# server {
#     listen 443 ssl http2;
#     listen [::]:443 ssl http2;
#     
#     server_name yourdomain.com www.yourdomain.com;
#     
#     ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
#     ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
#     ssl_protocols TLSv1.2 TLSv1.3;
#     ssl_ciphers HIGH:!aNULL:!MD5;
#     ssl_prefer_server_ciphers on;
#     
#     # Rest of configuration same as above...
# }
```

**Enable the site:**
```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/school-management /etc/nginx/sites-enabled/

# Disable default site
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

#### Option B: Apache Configuration

Create `/etc/apache2/sites-available/school-management.conf`:

```bash
sudo nano /etc/apache2/sites-available/school-management.conf
```

**Content:**
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    
    DocumentRoot /var/www/school-management/public

    <Directory /var/www/school-management/public>
        AllowOverride All
        Order Allow,Deny
        Allow from All
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ /index.php [QSA,L]
        </IfModule>
    </Directory>

    # Logs
    CustomLog /var/log/apache2/school-management-access.log combined
    ErrorLog /var/log/apache2/school-management-error.log

    # Security headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "DENY"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

**Enable the site:**
```bash
# Enable site and rewrite module
sudo a2ensite school-management
sudo a2dissite 000-default

# Test configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

---

### Phase 5: File Permissions & Security

#### Step 5.1: Set Directory Permissions

```bash
cd /var/www/school-management

# Set ownership
sudo chown -R www-data:www-data /var/www/school-management

# Set permissions
sudo chmod -R 755 /var/www/school-management
sudo chmod -R 775 /var/www/school-management/var
sudo chmod -R 775 /var/www/school-management/public

# Make sure var/cache and var/log are writable
sudo chmod -R 775 /var/www/school-management/var/cache
sudo chmod -R 775 /var/www/school-management/var/log
```

#### Step 5.2: .env.production Security

```bash
# Make .env files readable only by owner and web server
sudo chown www-data:www-data /var/www/school-management/.env.production
sudo chmod 640 /var/www/school-management/.env.production

# Ensure .env is not world-readable
sudo chmod 640 /var/www/school-management/.env
```

---

### Phase 6: SSL/HTTPS Configuration

#### Step 6.1: Install Certbot for Let's Encrypt

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx
# OR for Apache
# sudo apt install -y certbot python3-certbot-apache
```

#### Step 6.2: Generate SSL Certificate

**For Nginx:**
```bash
sudo certbot certonly --nginx \
    -d yourdomain.com \
    -d www.yourdomain.com \
    --email your-email@example.com \
    --agree-tos \
    --non-interactive
```

**For Apache:**
```bash
sudo certbot certonly --apache \
    -d yourdomain.com \
    -d www.yourdomain.com \
    --email your-email@example.com \
    --agree-tos \
    --non-interactive
```

#### Step 6.3: Update Web Server Configuration

**For Nginx (uncomment HTTPS block in earlier config):**
```bash
# Certificate files are at:
# /etc/letsencrypt/live/yourdomain.com/fullchain.pem
# /etc/letsencrypt/live/yourdomain.com/privkey.pem

sudo systemctl restart nginx
```

#### Step 6.4: Auto-Renewal

```bash
# Enable auto-renewal
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer

# Test renewal
sudo certbot renew --dry-run
```

---

### Phase 7: PHP-FPM Configuration

```bash
# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# If not running, start it
sudo systemctl start php8.2-fpm
sudo systemctl enable php8.2-fpm
```

#### Edit PHP Configuration (Optional but Recommended)

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

**Key settings for production:**
```ini
; Increase limits for large file uploads
upload_max_filesize = 50M
post_max_size = 50M

; Session settings
session.save_path = "/var/lib/php/sessions"

; Increase memory limit if needed
memory_limit = 256M

; Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source

; Production error logging
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
```

**Restart PHP-FPM:**
```bash
sudo systemctl restart php8.2-fpm
```

---

### Phase 8: Application Finalization

#### Step 8.1: Build Assets

```bash
cd /var/www/school-management

# Install asset dependencies
npm install  # If using Node-based assets

# Build assets
npm run build  # Or your asset build command

# OR if using AssetMapper (Symfony 7+)
php bin/console asset-map:compile
```

#### Step 8.2: Clear Caches

```bash
cd /var/www/school-management

# Clear all caches
php bin/console cache:clear --env=prod

# Warm up cache
php bin/console cache:warmup --env=prod
```

#### Step 8.3: Run Final Setup Commands

```bash
# Create shared directory if needed
php bin/console app:setup  # If you have custom setup command

# Verify everything
php bin/console about
php bin/console doctrine:schema:validate
```

#### Step 8.4: Test Application

```bash
# Check if index page loads
curl http://yourdomain.com

# Check logs for errors
tail -f /var/log/nginx/school-management-error.log
# OR for Apache
# tail -f /var/log/apache2/school-management-error.log
```

---

# PART 3: POST-DEPLOYMENT CONFIGURATION

## Performance Optimization

### 1. OpCache Configuration

Edit `/etc/php/8.2/fpm/conf.d/10-opcache.ini`:

```ini
[opcache]
; Enable OpCache
opcache.enable=1
opcache.enable_cli=0

; Memory allocation (in MB)
opcache.memory_consumption=256

; Interned strings buffer
opcache.interned_strings_buffer=16

; Number of cached files
opcache.max_accelerated_files=10000

; Revalidation frequency (0 = never)
opcache.validate_timestamps=0

; File update frequency in seconds (only used if validate_timestamps=1)
opcache.revalidate_freq=60

; JIT compilation (PHP 8.2+)
opcache.jit_buffer_size=256M
opcache.jit=1235  ; 1235 = CRTO mode
```

### 2. Database Optimization

**For MySQL:**
```sql
-- Create indexes if not present
ALTER TABLE student ADD INDEX idx_email (email);
ALTER TABLE teacher ADD INDEX idx_email (email);
ALTER TABLE course ADD INDEX idx_code (code);

-- Optimize tables
OPTIMIZE TABLE student;
OPTIMIZE TABLE teacher;
OPTIMIZE TABLE course;
```

**For PostgreSQL:**
```sql
-- Analyze tables
ANALYZE;

-- Create indexes
CREATE INDEX idx_student_email ON student(email);
CREATE INDEX idx_teacher_email ON teacher(email);
CREATE INDEX idx_course_code ON course(code);
```

### 3. Web Server Optimization

**Nginx:**
- Enable gzip compression (already in config)
- Set appropriate worker processes
- Configure timeouts

**Apache:**
```bash
# Enable mod_deflate for compression
sudo a2enmod deflate
sudo systemctl restart apache2
```

---

## Security Best Practices

### 1. SSH Key Authentication

```bash
# Generate SSH keys on your local machine
ssh-keygen -t ed25519 -C "your-email@example.com"

# Copy public key to server
ssh-copy-id -i ~/.ssh/id_ed25519.pub user@server

# Disable password authentication
sudo nano /etc/ssh/sshd_config
```

Set in sshd_config:
```
PasswordAuthentication no
PubkeyAuthentication yes
```

### 2. Firewall Configuration

```bash
# Enable UFW
sudo ufw enable

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP
sudo ufw allow 80/tcp

# Allow HTTPS
sudo ufw allow 443/tcp

# Check rules
sudo ufw status
```

### 3. Regular Updates

```bash
# Update system weekly
sudo apt update && sudo apt upgrade -y

# Enable automatic security updates
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

### 4. Application Security

```bash
# Ensure sensitive files are not readable
sudo chmod 640 /var/www/school-management/.env.production

# Check file permissions
ls -la /var/www/school-management/.env*

# Regular backups
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-school-app.sh
```

### 5. Disable Dangerous Functions

Already in PHP config, but verify:
```bash
# Check current disabled functions
php -i | grep "disable_functions"
```

---

## Monitoring & Maintenance

### 1. Log Monitoring

```bash
# Monitor Nginx errors in real-time
sudo tail -f /var/log/nginx/school-management-error.log

# Monitor PHP errors
sudo tail -f /var/log/php/error.log

# Monitor system resources
htop

# Check disk space
df -h
```

### 2. Database Backups

**Automated MySQL backup script** (`/usr/local/bin/backup-db.sh`):

```bash
#!/bin/bash

BACKUP_DIR="/backups/mysql"
DATE=$(date +%Y%m%d_%H%M%S)
MYSQL_USER="school_user"
MYSQL_PASS="YourPassword"

mkdir -p $BACKUP_DIR

mysqldump -u $MYSQL_USER -p$MYSQL_PASS school_management > \
    $BACKUP_DIR/school_management_$DATE.sql

# Compress
gzip $BACKUP_DIR/school_management_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $BACKUP_DIR/school_management_$DATE.sql.gz"
```

Make executable and add to crontab:
```bash
sudo chmod +x /usr/local/bin/backup-db.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
# 0 2 * * * /usr/local/bin/backup-db.sh
```

### 3. Application Backups

```bash
# Create weekly application backup
sudo crontab -e
# 0 3 * * 0 tar -czf /backups/app/school-app-$(date +\%Y\%m\%d).tar.gz /var/www/school-management
```

### 4. Health Check Script

```bash
#!/bin/bash
# /usr/local/bin/health-check.sh

# Check web server
curl -f http://localhost/health 2>/dev/null || echo "Web server down"

# Check database
php -r "
try {
    \$pdo = new PDO('mysql:host=127.0.0.1;dbname=school_management', 'school_user', 'password');
    echo 'Database OK';
} catch (Exception \$e) {
    echo 'Database Error: ' . \$e->getMessage();
}
"

# Check disk space
DISK_USAGE=\$(df /var/www | awk 'NR==2 {print \$5}' | sed 's/%//')
if [ \$DISK_USAGE -gt 80 ]; then
    echo "WARNING: Disk usage at \${DISK_USAGE}%"
fi

# Check memory
FREE_MEMORY=\$(free | grep Mem | awk '{print int(\$3/\$2 * 100)}')
if [ \$FREE_MEMORY -gt 80 ]; then
    echo "WARNING: Memory usage at \${FREE_MEMORY}%"
fi
```

---

## Deployment Updates

### Deploying New Versions

```bash
# SSH into server
ssh user@server

# Navigate to app directory
cd /var/www/school-management

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear old cache
php bin/console cache:clear --env=prod

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Warm up cache
php bin/console cache:warmup --env=prod

# Restart services (if needed)
sudo systemctl restart php8.2-fpm

# Verify
curl https://yourdomain.com
```

### Automated Deployment (Optional)

Set up GitHub Actions to deploy automatically:

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Deploy to server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /var/www/school-management
          git pull origin main
          composer install --no-dev --optimize-autoloader
          php bin/console doctrine:migrations:migrate --no-interaction
          php bin/console cache:clear --env=prod
          sudo systemctl restart php8.2-fpm
```

---

## Troubleshooting

### Common Issues & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| 503 Service Unavailable | PHP-FPM not running | `sudo systemctl start php8.2-fpm` |
| Permission Denied | Wrong file ownership | `sudo chown -R www-data:www-data /var/www/school-management` |
| Database connection error | Wrong credentials in .env | Verify `DATABASE_URL` in `.env.production` |
| Memory limit exceeded | PHP memory too low | Increase `memory_limit` in php.ini |
| 502 Bad Gateway | PHP-FPM timeout | Increase `fastcgi_read_timeout` in Nginx |
| SSL certificate expired | Let's Encrypt renewal failed | `sudo certbot renew --force-renewal` |
| Assets not loading | Missing asset compile | `php bin/console asset-map:compile` |

---

## Checklist: Pre-Production Launch

- [ ] All Docker files removed
- [ ] `.env.production` configured with correct database URL
- [ ] `APP_SECRET` is 32+ characters (generated randomly)
- [ ] Database created and migrations run
- [ ] PHP 8.2+ installed with all required extensions
- [ ] Web server (Nginx/Apache) configured
- [ ] SSL certificate installed
- [ ] File permissions set correctly (var/ is writable)
- [ ] Logs directory exists and is writable
- [ ] Application assets compiled
- [ ] Cache cleared and warmed up
- [ ] Database backups automated
- [ ] Firewall configured
- [ ] SSH key authentication enabled
- [ ] Monitoring scripts in place
- [ ] Domain DNS records pointing to server

---

## Additional Resources

- [Symfony Production Server Configuration](https://symfony.com/doc/current/setup/prod_server.html)
- [Nginx Configuration Best Practices](https://nginx.org/en/docs/)
- [PHP-FPM Configuration](https://www.php.net/manual/en/install.fpm.php)
- [Let's Encrypt Documentation](https://letsencrypt.org/docs/)
- [Symfony Performance Guide](https://symfony.com/doc/current/performance.html)

---

## Next Steps

1. Review this guide thoroughly
2. Choose your hosting provider
3. Provision a server (Ubuntu 22.04 LTS recommended)
4. Follow Phase 1-8 sequentially
5. Test the application thoroughly
6. Monitor logs for issues
7. Set up automated backups and deployments

---

**Questions or Issues?** Refer to the Symfony documentation or contact your hosting provider's support team.

