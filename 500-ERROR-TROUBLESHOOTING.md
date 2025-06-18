# 🚨 500 Internal Server Error - Troubleshooting Guide

## 🔍 Quick Diagnostics

### Step 1: Upload and Run Database Test
1. **Upload `test-database.php`** to your Hostinger public_html folder
2. **Visit** `https://hermosawaterdistrict.com/test-database.php` in your browser
3. **Check results** - this will tell you if the database connection is working

### Step 2: Check Error Logs
1. **Login to Hostinger Control Panel**
2. **Go to File Manager** → **Error Logs**
3. **Look for recent PHP errors** related to your domain
4. **Common error patterns to look for:**
   - Database connection errors
   - Missing extensions (pdo_pgsql)
   - File permission errors
   - Missing .env file

### Step 3: Verify File Structure
Make sure your files are structured like this on Hostinger:
```
public_html/
├── index.php (from public folder)
├── .htaccess (from public folder) 
├── build/ (from public/build)
├── favicon.ico (from public folder)
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
├── artisan
├── composer.json
└── test-database.php (for testing)
```

## 🛠️ Common 500 Error Causes & Solutions

### 1. **PostgreSQL Extension Missing**
**Symptoms:** Database connection test fails
**Solution:** 
- Contact Hostinger support to enable PostgreSQL/pdo_pgsql extension
- Most shared hosting plans support MySQL by default, PostgreSQL needs to be enabled

### 2. **Missing .env File**
**Symptoms:** Laravel configuration errors
**Solution:**
- Create `.env` file in your root directory
- Copy content from `env-production-sample.txt`
- Make sure it's named exactly `.env` (not `.env.txt`)

### 3. **File Permissions**
**Symptoms:** Laravel cannot write to storage
**Solution:**
Set these permissions in Hostinger File Manager:
- `storage/` folder: **755**
- `storage/logs/` folder: **755**
- `storage/framework/` folder: **755**
- `bootstrap/cache/` folder: **755**

### 4. **Vendor Dependencies Missing**
**Symptoms:** Class not found errors
**Solution:**
- Upload the entire `vendor/` folder
- Or run `composer install --no-dev` if you have SSH access

### 5. **Cached Configuration**
**Symptoms:** Old configuration being used
**Solution:**
If you have SSH access, run:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 6. **Laravel Index.php Path Issues**
**Symptoms:** 500 error after moving public folder contents
**Solution:**
Edit `index.php` in your public_html root and make sure paths are correct:
```php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
```

## 🔧 Step-by-Step Debugging Process

### 1. Enable Debug Mode (Temporarily)
Add this to your `.env` file temporarily:
```env
APP_DEBUG=true
APP_ENV=local
```
**⚠️ Remember to set back to `false` and `production` after debugging!**

### 2. Check Specific Components

**Test Database Connection:**
```
https://hermosawaterdistrict.com/test-database.php
```

**Test Basic PHP:**
Create `info.php` with:
```php
<?php phpinfo(); ?>
```
Visit: `https://hermosawaterdistrict.com/info.php`

### 3. Check Laravel Requirements
Your Laravel app needs:
- PHP >= 8.0
- PDO PHP Extension
- PDO PostgreSQL Driver
- Mbstring PHP Extension
- OpenSSL PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension
- BCMath PHP Extension

## 📋 Hostinger-Specific Issues

### PostgreSQL Support
**Problem:** Hostinger's shared hosting may not support PostgreSQL by default
**Solutions:**
1. **Contact Hostinger Support** to enable PostgreSQL
2. **Upgrade to a VPS plan** that supports PostgreSQL
3. **Switch to MySQL** (requires database migration)

### SSL Certificate
**Problem:** Mixed content errors if SSL isn't properly configured
**Solution:**
1. Enable SSL in Hostinger control panel
2. Ensure `APP_URL=https://hermosawaterdistrict.com` in `.env`

## 🆘 Emergency Checklist

If you're still getting 500 errors, check these in order:

- [ ] `.env` file exists and has correct database credentials
- [ ] `vendor/` folder is uploaded
- [ ] `storage/` and `bootstrap/cache/` are writable (755)
- [ ] PostgreSQL is enabled on your hosting plan
- [ ] `index.php` paths are correct
- [ ] Error logs show specific error messages
- [ ] `test-database.php` runs successfully

## 📞 When to Contact Support

Contact Hostinger support if:
1. PostgreSQL extension is not available
2. File permissions cannot be changed
3. Error logs show server configuration issues
4. Basic PHP test files don't work

---

**💡 Pro Tip:** Always check the database connection test first - many 500 errors are caused by database connectivity issues! 