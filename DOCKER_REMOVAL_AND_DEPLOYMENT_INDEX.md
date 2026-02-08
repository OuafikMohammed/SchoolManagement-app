# Docker Removal & Production Deployment
## Complete Guide for Symfony 7.4 School Management Application

**Last Updated:** February 2026  
**Application:** School Management System v1.0  
**Target Environment:** Production Server (Ubuntu 22.04 LTS)  
**Status:** Ready to Deploy

---

## 📋 Quick Start Guide

### For Windows Users (Local Cleanup)

```powershell
# Open PowerShell in your project directory
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process
.\Remove-Docker.ps1
```

### For Linux/Mac Users (Server Cleanup)

```bash
# On production server after uploading code
cd /var/www/school-management
bash remove-docker.sh
```

---

## 📚 Documentation Overview

This package includes comprehensive guides for removing Docker and deploying to production:

### 1. **PRODUCTION_DEPLOYMENT_GUIDE.md** (Primary Reference)
   - **50+ pages of detailed instructions**
   - Complete server setup from scratch
   - Database configuration
   - Web server setup (Nginx & Apache)
   - Security hardening
   - Performance optimization
   - **Read this first for comprehensive understanding**

### 2. **DOCKER_REMOVAL_QUICK_REF.md** (Quick Reference)
   - Quick removal steps
   - File checklist
   - Verification commands
   - **Use this for quick lookup**

### 3. **PRE_PRODUCTION_CHECKLIST.md** (Verification Tool)
   - 16 phases with checkboxes
   - Comprehensive verification at each step
   - Issue tracking template
   - **Use this to verify nothing is missed**

### 4. **remove-docker.sh** (Automated Script - Linux/Mac)
   - Automatically removes all Docker files
   - Creates backup before removal
   - Verifies project integrity
   - Creates .env.production template
   - **Run on production server**

### 5. **Remove-Docker.ps1** (Automated Script - Windows)
   - PowerShell version for Windows users
   - Removes Docker files locally
   - Creates backup
   - Prepares for deployment
   - **Run on development machine**

### 6. **INDEX.md** (This File)
   - Overview of all documentation
   - Quick navigation guide
   - Phase overview

---

## 🎯 Three-Phase Process

### Phase 1: Remove Docker (LOCAL - Your Machine)
**Time: 5-10 minutes**

```powershell
# Windows PowerShell
.\Remove-Docker.ps1

# Expected output:
# ✓ All Docker files removed successfully!
# ✓ Project structure is intact.
# ✓ Ready for production deployment!
```

**What happens:**
- All Docker files removed (Dockerfile, docker-compose.yml, etc.)
- Project structure verified intact
- .env.production created as template
- Backup created (optional restore if needed)

**Verify:**
```bash
dir | findstr docker      # Should show nothing
composer install --no-dev  # Should work
php bin/console about      # Should work
```

---

### Phase 2: Provision & Configure Server (SERVER SETUP)
**Time: 30-45 minutes**

**Follow: PRODUCTION_DEPLOYMENT_GUIDE.md → Phase 1-8**

Steps:
1. Choose hosting provider (DigitalOcean, Linode, AWS, etc.)
2. Provision Ubuntu 22.04 LTS server
3. SSH into server
4. Install system dependencies (PHP 8.2, MySQL/PostgreSQL, Nginx/Apache)
5. Install Composer
6. Create application directory
7. Deploy application code
8. Configure .env.production
9. Set up database
10. Configure web server
11. Configure SSL/HTTPS
12. Set permissions

**Hosting Recommendations:**
- **Budget:** DigitalOcean, Linode, Hetzner (~$5-10/month)
- **Enterprise:** AWS, Google Cloud, Azure (~$20+/month)
- **Managed Symfony:** Symfony Cloud, PlatformSH (~$50+/month)

---

### Phase 3: Post-Deployment (VERIFICATION & OPTIMIZATION)
**Time: 15-30 minutes**

**Follow: PRODUCTION_DEPLOYMENT_GUIDE.md → Phase 9-15**

Steps:
1. Run application verification
2. Test database connectivity
3. Test user authentication
4. Test form submissions
5. Set up monitoring
6. Configure automated backups
7. Test SSL/HTTPS
8. Monitor error logs
9. Performance optimization
10. Security hardening

---

## 🚀 Complete Deployment Timeline

### Week 1: Preparation
- [ ] **Day 1:** Review documentation
- [ ] **Day 2:** Set up development environment (remove Docker locally)
- [ ] **Day 3:** Choose hosting provider & provision server
- [ ] **Day 4:** Begin Phase 1 installation on server
- [ ] **Day 5:** Complete Phase 2 - database & application setup

### Week 2: Launch
- [ ] **Day 1:** Web server configuration (Phase 3)
- [ ] **Day 2:** SSL certificate setup (Phase 4)
- [ ] **Day 3:** Performance optimization (Phase 5)
- [ ] **Day 4:** Security hardening (Phase 6)
- [ ] **Day 5:** Testing & verification (Phase 7)

### Week 3: Post-Launch Monitoring
- [ ] **Daily:** Monitor logs & performance
- [ ] **Every 2 hours:** Check application health
- [ ] **Daily:** Verify backups running
- [ ] **Weekly:** Review security logs

---

## 📁 What Gets Removed

### Docker Files (Root Level)
```
✗ Dockerfile
✗ docker-compose.yml
✗ compose.yaml
✗ compose.override.yaml
✗ .dockerignore
✗ docker/                          (entire directory)
  ✗ docker/php/
    ✗ Dockerfile
    ✗ php.ini
    ✗ opcache.ini
  ✗ docker/nginx/
    ✗ default.conf
```

### Documentation Files (Optional)
```
? DOCKER_QUICK_REFERENCE.md        (optional)
? DOCKER_SETUP.md                  (optional)
```

### What STAYS (Critical Files)
```
✓ Dockerfile                       (REMOVED - not needed)
✓ .env                             (KEPT - modified)
✓ .env.production                  (CREATED - new)
✓ composer.json                    (KEPT - unchanged)
✓ composer.lock                    (KEPT - unchanged)
✓ src/                             (KEPT - unchanged)
✓ public/                          (KEPT - unchanged)
✓ config/                          (KEPT - unchanged)
✓ templates/                       (KEPT - unchanged)
✓ migrations/                      (KEPT - unchanged)
```

---

## 🔧 System Requirements After Docker Removal

### Server Operating System
- Ubuntu 22.04 LTS (recommended)
- Or any Linux with recent packages
- Or Windows Server 2019+
- Or macOS

### PHP
- **Version:** PHP 8.2+ (8.3+ recommended for better performance)
- **SAPI:** php-fpm (for web servers)
- **Extensions Required:**
  - pdo, pdo_mysql (or pdo_pgsql)
  - gd, intl, zip, mbstring
  - curl, xml, bcmath, json, iconv

### Web Server (Choose One)
- **Nginx** (recommended, lighter)
- **Apache** (compatible, more features)

### Database (Choose One)
- **MySQL 8.0** (recommended)
- **PostgreSQL 16** (alternative)
- **MariaDB** (MySQL-compatible)

### Additional Services
- **SSL:** Let's Encrypt (free)
- **Backup:** mysqldump/pg_dump
- **Monitoring:** Optional

---

## 🛠️ Recommended Hosting Configurations

### Option 1: DigitalOcean VPS ($6/month)
```
- Droplet: Basic - $6/month
- OS: Ubuntu 22.04
- CPU: 1 core
- RAM: 1 GB
- Storage: 25 GB
- Perfect for: Small to medium applications
```

### Option 2: Linode VPS ($5/month)
```
- Nanode: Shared CPU - $5/month
- OS: Ubuntu 22.04
- CPU: 1 core
- RAM: 1 GB
- Storage: 25 GB
- Perfect for: Tight budget, high reliability
```

### Option 3: AWS EC2 (Free tier eligible)
```
- t2.micro (Free tier annually)
- OS: Ubuntu 22.04
- CPU: 1 vCPU
- RAM: 1 GB
- Storage: 30 GB
- Perfect for: High scalability potential
```

### Option 4: Hetzner Cloud (€2.99/month)
```
- CX11 - €2.99/month
- OS: Ubuntu 22.04
- CPU: 1 vCPU
- RAM: 1 GB
- Storage: 25 GB
- Perfect for: EU-based, very cheap
```

---

## 📊 Estimated Timeline by Phase

| Phase | Duration | Components |
|-------|----------|-----------|
| **Phase 1: Docker Removal** | 10 min | File removal, verification |
| **Phase 2: Server Setup** | 20 min | System packages, PHP, DB |
| **Phase 3: Application Deploy** | 15 min | Code upload, dependencies |
| **Phase 4: Database** | 10 min | DB creation, migrations |
| **Phase 5: Web Server** | 10 min | Nginx/Apache configuration |
| **Phase 6: SSL Certificate** | 5 min | Let's Encrypt setup |
| **Phase 7: Security** | 10 min | Firewall, permissions |
| **Phase 8: Optimization** | 10 min | Caching, performance |
| **Phase 9: Testing** | 15 min | Health checks, verification |
| **TOTAL** | **95 min** | Full production launch |

---

## ✅ Pre-Deployment Checklist

### Local Machine (Before Uploading)
- [ ] Review PRODUCTION_DEPLOYMENT_GUIDE.md
- [ ] Run Remove-Docker.ps1 successfully
- [ ] Verify .env.production created
- [ ] Test `composer install --no-dev` works locally
- [ ] Commit all changes to git

### Server Selection
- [ ] Hosting provider chosen
- [ ] Domain purchased
- [ ] Server provisioned
- [ ] SSH access verified
- [ ] Root/sudo access available

### Pre-Deployment
- [ ] Study PRE_PRODUCTION_CHECKLIST.md
- [ ] Have terminal/SSH open
- [ ] Have text editor ready (nano/vim)
- [ ] Have secure password generator ready
- [ ] Have 2 hours uninterrupted time allocated

---

## 🔍 Common Issues & Quick Fixes

### "Permission denied" on var/ directory
```bash
sudo chown -R www-data:www-data /var/www/school-management
sudo chmod -R 775 /var/www/school-management/var
```

### "Connection to database failed"
```bash
# Check credentials in .env.production
cat /var/www/school-management/.env.production | grep DATABASE_URL

# Test database connection
php bin/console doctrine:database:create --if-not-exists
```

### "502 Bad Gateway" from Nginx
```bash
# Check PHP-FPM is running
sudo systemctl status php8.2-fpm

# Check Nginx error log
sudo tail -f /var/log/nginx/school-management-error.log
```

### "Class not found" errors
```bash
# Regenerate autoloader
cd /var/www/school-management
composer dump-autoload --optimize

# Clear cache
php bin/console cache:clear --env=prod
```

### Assets not loading (CSS/JS 404)
```bash
# Compile assets
php bin/console asset-map:compile

# Check permissions
ls -la /var/www/school-management/public/
```

---

## 📚 Additional Resources

### Official Symfony Documentation
- [Symfony 7.4 Documentation](https://symfony.com/doc/7.4/)
- [Symfony Production Server Configuration](https://symfony.com/doc/current/setup/prod_server.html)
- [Symfony Performance Guide](https://symfony.com/doc/current/performance.html)

### Server Configuration
- [Nginx Documentation](https://nginx.org/en/docs/)
- [Apache Documentation](https://httpd.apache.org/docs/)
- [PHP Official Manual](https://www.php.net/manual/)

### Security
- [Let's Encrypt Documentation](https://letsencrypt.org/docs/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Symfony Security Guide](https://symfony.com/doc/current/security.html)

---

## 🆘 Getting Help

### Issue: Not sure which steps to follow
**Solution:** Start with **PRODUCTION_DEPLOYMENT_GUIDE.md** - follow it sequentially

### Issue: Getting errors during deployment
**Solution:** Check **PRE_PRODUCTION_CHECKLIST.md** for that phase, verify each step

### Issue: Docker files won't remove
**Solution:** Run the automated script instead:
- Windows: `.\Remove-Docker.ps1`
- Linux/Mac: `bash remove-docker.sh`

### Issue: Can't connect to server
**Solution:** Verify SSH access:
```bash
ssh -v user@server_ip    # Verbose output shows connection issues
```

### Issue: Application won't start
**Solution:** Check logs:
```bash
# Web server errors
sudo tail -f /var/log/nginx/school-management-error.log

# PHP errors
sudo tail -f /var/log/php/error.log

# Application logs
tail -f /var/www/school-management/var/log/prod.log
```

---

## 🎓 Learning Path

### If you're new to Linux servers
1. Read: PRODUCTION_DEPLOYMENT_GUIDE.md → Phase 1 (System Setup)
2. Do: Follow each command step-by-step
3. Understand: What each service does

### If you're familiar with servers
1. Scan: DOCKER_REMOVAL_QUICK_REF.md
2. Use: PRE_PRODUCTION_CHECKLIST.md for verification
3. Reference: PRODUCTION_DEPLOYMENT_GUIDE.md as needed

### If you're experienced
1. Use: remove-docker.sh or Remove-Docker.ps1
2. Reference: Phase-specific sections as needed
3. Customize: Based on your infrastructure

---

## 📞 Support Contacts

### Before Reaching Out
1. Check this documentation
2. Check the relevant section's troubleshooting
3. Search error message online
4. Review server logs

### When Asking for Help
Include:
1. Error message (exact text)
2. What you were trying to do
3. Which step/phase you're on
4. Output from `php bin/console about`
5. Server OS and PHP version

---

## 🎯 Success Criteria

Your deployment is successful when:

✓ Application loads on HTTPS (no errors)  
✓ User login works  
✓ Database queries work  
✓ Forms submit and save data  
✓ SSL certificate is valid (green lock)  
✓ No errors in error logs (after 1 hour)  
✓ System resources normal (CPU < 50%, RAM < 50%)  
✓ Automated backups running  

---

## 📝 Document Map

```
┌─ START HERE ────────────────────────────┐
│ You are reading: INDEX.md               │
│ Next: Choose your path                 │
└────────────────────────────────────────┘
                    ↓
        ┌───────────┬───────────┐
        ↓           ↓           ↓
  Windows User   Linux User   Experienced
        ↓           ↓           ↓
   Remove-     remove-    DOCKER_REMOVAL
   Docker.ps1  docker.sh  _QUICK_REF.md
        │           │           │
        └───────────┴───────────┘
                    ↓
    PRE_PRODUCTION_CHECKLIST.md
    (Use for verification)
                    ↓
    PRODUCTION_DEPLOYMENT_GUIDE.md
    (Use for detailed steps)
                    ↓
            DEPLOYMENT READY!
```

---

## 🚀 Final Step: Start Your Deployment

### Choose Your Starting Point:

**I want to remove Docker files locally first:**
→ Run `.\Remove-Docker.ps1` (Windows) or `bash remove-docker.sh` (Linux)

**I'm ready to deploy to production:**
→ Read: PRODUCTION_DEPLOYMENT_GUIDE.md → Phase 1

**I want to verify nothing is missed:**
→ Use: PRE_PRODUCTION_CHECKLIST.md

**I need quick reference:**
→ Use: DOCKER_REMOVAL_QUICK_REF.md

---

## 📅 Deployment Timeline

**Recommended:** Deploy on a weekday during business hours (in case issues arise)

**Minimum Time Needed:** 2 hours uninterrupted

**Team Needed:** You (+ optional code review partner)

**Testing Time:** 24-48 hours monitoring post-launch

---

## ✨ You're Ready!

You now have everything needed to:
1. ✓ Remove Docker from your Symfony application
2. ✓ Deploy to a production server
3. ✓ Configure security, performance, and monitoring
4. ✓ Maintain the application long-term

**Let's deploy! 🚀**

---

*Questions? Refer to specific documentation sections or search for your error/issue.*

