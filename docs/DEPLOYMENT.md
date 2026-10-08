# STONE ENERGY INT'L LTD — Production Deployment Guide (cPanel / Shared Hosting)

This guide documents deploying the **STONE ENERGY INT'L LTD** website and custom CMS to standard cPanel or shared Apache hosting environments.

---

## 1. Directory Permissions & File Structure

Set recommended Linux permissions via cPanel File Manager or SSH:

* **Directories:** `755` (`rwxr-xr-x`)
* **Files:** `644` (`rw-r--r--`)
* **Writable Folders (Require 755 or 775):**
  * `assets/uploads/` (User media and PDF specification attachments)
  * `logs/` (System exception log files)
  * `config/` (Location of `installed.lock`)

```bash
chmod -R 755 assets/uploads
chmod -R 755 logs
chmod -R 755 config
```

---

## 2. cPanel Deployment Steps

### Step 1: Upload Project Archive
1. Compress your project directory into a ZIP archive (excluding `.git`).
2. Log in to cPanel and open **File Manager**.
3. Navigate to `public_html` (or the targeted subdomain root).
4. Upload and extract the ZIP archive.

### Step 2: Create MySQL Database in cPanel
1. In cPanel, navigate to **MySQL® Databases**.
2. Create a new database: e.g., `cpuser_stoneenergy`.
3. Create a new MySQL user: e.g., `cpuser_stoneadmin`, with a strong generated password.
4. Add the user to the database and grant **ALL PRIVILEGES**.

### Step 3: Import Database Schema
1. In cPanel, open **phpMyAdmin**.
2. Select your newly created database (`cpuser_stoneenergy`).
3. Click the **Import** tab.
4. Choose `database/schema.sql` from your computer or server and click **Go**.

### Step 4: Configure Production `.env`
Create or edit `.env` in the root of the project:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.stoneenergyintl.com

DB_HOST=localhost
DB_PORT=3306
DB_NAME=cpuser_stoneenergy
DB_USER=cpuser_stoneadmin
DB_PASS=YourGeneratedPasswordHere

MAIL_HOST=mail.stoneenergyintl.com
MAIL_PORT=587
MAIL_USERNAME=noreply@stoneenergyintl.com
MAIL_PASSWORD=EmailAccountPasswordHere
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@stoneenergyintl.com
MAIL_FROM_NAME="STONE ENERGY INT'L LTD"

SESSION_LIFETIME=7200
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=15
```

---

## 3. SSL / HTTPS Enforcement

1. In cPanel, navigate to **SSL/TLS Status** and run **AutoSSL** (Let's Encrypt or Sectigo).
2. To force HTTPS throughout the site, verify your root `.htaccess` contains the standard redirect:
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

---

## 4. Production Security Checklist

* [x] Verify `APP_DEBUG=false` in `.env` to ensure no database errors or stack traces are ever exposed to visitors.
* [x] Confirm that `assets/uploads/.htaccess` prevents PHP execution inside uploaded files.
* [x] Confirm `logs/.htaccess` denies direct browser access to `logs/app.log`.
* [x] Confirm `config/installed.lock` exists so `install.php` cannot be rerun by malicious actors.
* [x] Update the default Super Admin password from `AdminPassword2026!` to a custom corporate password.
