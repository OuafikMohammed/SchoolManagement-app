# Deployment Checklist

## ✅ Pre-Deployment Verification

### 1. Code Quality
- [ ] Run static analysis: `./vendor/bin/phpstan analyse src/`
- [ ] Check code style: `./vendor/bin/php-cs-fixer fix src/ --dry-run`
- [ ] Run all tests: `php bin/phpunit`
- [ ] Check test coverage: `php bin/phpunit --coverage-html=coverage`
- [ ] Review security: `composer audit`

### 2. Database
- [ ] Run migrations: `php bin/console doctrine:migrations:migrate`
- [ ] Verify entities: `php bin/console doctrine:schema:validate`
- [ ] Test fixtures: `php bin/console doctrine:fixtures:load --purge-with-truncate`
- [ ] Check database performance: Review slow query logs

### 3. Security
- [ ] Update all dependencies: `composer update`
- [ ] Check for vulnerable packages: `composer audit`
- [ ] Review `.env.local` for secrets (should NOT be committed)
- [ ] Enable HTTPS on production
- [ ] Configure CORS if needed
- [ ] Review CSRF token settings

### 4. Configuration
- [ ] Set `APP_ENV=prod`
- [ ] Set strong `APP_SECRET`
- [ ] Configure real database URL
- [ ] Set up error logging
- [ ] Configure email settings (MAILER_DSN)
- [ ] Set up asset CDN (if applicable)

### 5. Assets
- [ ] Build assets: `npm run build`
- [ ] Compile asset map: `php bin/console asset-map:compile`
- [ ] Verify CSS/JS loads correctly
- [ ] Test responsive design on mobile

### 6. Performance
- [ ] Enable caching: `php bin/console cache:warmup --env=prod`
- [ ] Configure Redis (optional): For session & cache
- [ ] Set up OpCache on server
- [ ] Configure database connection pooling
- [ ] Test under load

### 7. Monitoring
- [ ] Set up error tracking (Sentry, Rollbar, etc.)
- [ ] Configure application monitoring
- [ ] Set up uptime monitoring
- [ ] Create monitoring dashboards

---

## 🚀 Deployment Steps

### 1. Prepare Repository
```bash
# Create production branch
git checkout -b production

# Update version
# Commit changes
git commit -m "v1.0.0 - Initial release"
git tag v1.0.0
```

### 2. Deploy Code
```bash
# Clone to production server
git clone <repo> /var/www/my_projet
cd /var/www/my_projet
git checkout v1.0.0

# Install dependencies (production only)
composer install --no-dev --optimize-autoloader

# Build assets
npm ci --production
npm run build
php bin/console asset-map:compile
```

### 3. Configure Environment
```bash
# Create .env.prod.local
cat > .env.prod.local << EOF
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=your-secret-key-here
DATABASE_URL="mysql://user:pass@localhost:3306/db_name?serverVersion=8.0"
MAILER_DSN="smtp://localhost:1025"
EOF

chmod 600 .env.prod.local
```

### 4. Prepare Database
```bash
# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Create admin user (optional)
php bin/console app:create-user admin@example.com --password=changeme --roles=ROLE_ADMIN
```

### 5. Configure Web Server

**Nginx:**
```nginx
server {
    listen 80;
    server_name example.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name example.com;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    root /var/www/my_projet/public;
    
    location / {
        try_files $uri /index.php$is_args$args;
    }
    
    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }
    
    location ~ \.php$ {
        return 404;
    }
}
```

**Apache:**
```apache
<VirtualHost *:443>
    ServerName example.com
    DocumentRoot /var/www/my_projet/public
    
    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem
    
    <Directory /var/www/my_projet/public>
        AllowOverride None
        Order Allow,Deny
        Allow from All
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ index.php [QSA,L]
        </IfModule>
    </Directory>
    
    <Directory /var/www/my_projet/public/bundles>
        <IfModule mod_rewrite.c>
            RewriteEngine Off
        </IfModule>
    </Directory>
</VirtualHost>
```

### 6. Set Permissions
```bash
# Set correct ownership
chown -R www-data:www-data /var/www/my_projet

# Set permissions
chmod -R 755 /var/www/my_projet
chmod -R 777 /var/www/my_projet/var
chmod 600 /var/www/my_projet/.env.prod.local
```

### 7. PHP Configuration
```ini
; /etc/php/8.2/fpm/php.ini
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 30
opcache.enable = 1
opcache.memory_consumption = 128
```

### 8. Warm up Cache
```bash
php bin/console cache:warmup --env=prod
```

### 9. Verify Deployment
```bash
# Check application is running
curl https://example.com

# Check logs
tail -f var/log/prod.log

# Test critical paths
curl https://example.com/login
curl https://example.com/register
```

---

## 🔄 Post-Deployment

### 1. Monitoring
- [ ] Check error logs: `tail -f var/log/prod.log`
- [ ] Monitor application performance
- [ ] Set up alerts for errors
- [ ] Review user feedback

### 2. Backups
- [ ] Set up daily database backups
- [ ] Set up file backups
- [ ] Test restore process
- [ ] Document backup procedure

### 3. Updates
- [ ] Plan security update schedule
- [ ] Document upgrade process
- [ ] Create staging environment
- [ ] Test updates before production

### 4. Optimization
- [ ] Analyze slow queries
- [ ] Optimize database indexes
- [ ] Enable caching where possible
- [ ] Compress assets
- [ ] Enable CDN for static files

---

## 🚨 Rollback Plan

If issues occur:

```bash
# Revert to previous version
git checkout previous-tag
composer install --no-dev --optimize-autoloader

# Restore database backup
mysql db_name < backup.sql

# Clear cache
rm -rf var/cache/prod

# Restart services
systemctl restart php-fpm
systemctl restart nginx
```

---

## 📊 Post-Launch Metrics

- **Uptime**: Target 99.9%
- **Response Time**: Target <500ms
- **Error Rate**: Target <0.1%
- **User Satisfaction**: Monitor feedback

---

**Last Updated:** December 23, 2025

**Status:** ✅ Ready for Production Deployment
