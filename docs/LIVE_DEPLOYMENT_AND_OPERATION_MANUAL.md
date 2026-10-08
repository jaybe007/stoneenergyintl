# STONE ENERGY INT'L LTD
## Live Production Deployment & Operations Manual

**Organization:** STONE ENERGY INT'L LTD  
**Positioning:** GENERAL CONTRACTOR & MULTI-SECTOR SUPPLY SOLUTIONS  
**Motto:** Building. Supplying. Delivering.  
**Headquarters:** 22, Oyelude Layout, Aba Alfa, Ojo, Ibadan, Oyo State, Nigeria  
**Architecture:** Vanilla PHP 8.x / MySQL (PDO) / Vanilla JavaScript  
**Target Environment:** Apache 2.4+ / cPanel / Shared Hosting / VPS  

---

## 1. Hosting Environment Prerequisites

* **Web Server:** Apache 2.4+ (with `mod_rewrite`, `mod_headers`, `mod_expires`, `mod_deflate` enabled).
* **PHP Engine:** PHP 8.1 or PHP 8.2 with standard extensions: `pdo_mysql`, `fileinfo`, `mbstring`, `openssl`, `json`.
* **Database:** MySQL 5.7+ or MariaDB 10.4+ (InnoDB engine, `utf8mb4` charset).
* **SSL Certificate:** Active TLS/SSL certificate (cPanel AutoSSL or Let's Encrypt).

---

## 2. Pre-Deployment Packaging

1. Exclude local version control directories (`.git/`).
2. Compress all application directories and files into a single `.zip` file:
   * `assets/` (CSS, JS, images, uploads)
   * `config/` (environment and database bootstrap)
   * `core/` (security, authentication, mailer, uploader, audit engines)
   * `database/` (`schema.sql`)
   * `docs/` (documentation and manuals)
   * `includes/` (header, footer, navigation, modals)
   * `logs/` (system logging)
   * `admin/` (custom CMS modules)
   * Public PHP pages (`index.php`, `about.php`, `services.php`, `products.php`, `projects.php`, `industries.php`, `quote.php`, `contact.php`, `blog.php`, etc.)
   * Error pages (`404.php`, `403.php`, `500.php`)
   * Server configs (`.htaccess`, `robots.txt`, `sitemap.xml`)

---

## 3. Step-by-Step Live Deployment (cPanel Workflow)

### Step 3.1: Upload and Extract Files
1. Log in to cPanel and open **File Manager**.
2. Navigate to your web root (`public_html/` or your assigned subdomain directory).
3. Click **Upload**, upload the `.zip` archive, and extract its contents into `public_html/`.
4. Ensure hidden files are visible to confirm `.htaccess` is present.

### Step 3.2: Verify Linux File Permissions
* **Directories:** `755` (`rwxr-xr-x`)
* **Files:** `644` (`rw-r--r--`)
* **Writable Directories (must be writable by web server user):**
  * `assets/uploads/` &rarr; `755` or `775`
  * `logs/` &rarr; `755` or `775`
  * `config/` &rarr; `755` or `775`

### Step 3.3: Create MySQL Database & User
1. In cPanel, navigate to **MySQL® Databases**.
2. Create a new database: e.g., `cpuser_stoneenergy`.
3. Create a new database user: e.g., `cpuser_stoneadmin` with a strong password.
4. Add the user to the database and grant **ALL PRIVILEGES**.

### Step 3.4: Import Database Schema
1. In cPanel, open **phpMyAdmin**.
2. Select your newly created database from the left navigation tree.
3. Click the **Import** tab at the top.
4. Choose `database/schema.sql` and click **Import** (or **Go**).
5. All 31 tables, foreign keys, indexes, baseline categories, and seed data will be populated.

---

## 4. Production Configuration Hardening

### Step 4.1: Production `.env` Setup
Create or update `.env` in the root directory:

```ini
APP_NAME="STONE ENERGY INT'L LTD"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.stoneenergyintl.com

DB_HOST=localhost
DB_PORT=3306
DB_NAME=cpuser_stoneenergy
DB_USER=cpuser_stoneadmin
DB_PASS=YourStrongDatabasePasswordHere

MAIL_DRIVER=smtp
SMTP_HOST=mail.stoneenergyintl.com
SMTP_PORT=465
SMTP_USER=info@stoneenergyintl.com
SMTP_PASS=YourCorporateEmailPasswordHere
SMTP_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@stoneenergyintl.com
MAIL_FROM_NAME="STONE ENERGY INT'L LTD"

SESSION_LIFETIME=7200
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=15
```

> **IMPORTANT:** Keep `APP_DEBUG=false` in production to prevent technical database errors from being exposed to public visitors.

### Step 4.2: Enforce SSL / HTTPS
Confirm the HTTPS redirect rule in `.htaccess`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### Step 4.3: Secure the Installer
* Ensure `config/installed.lock` exists.
* Delete `install.php` from the production server so the web installer cannot be re-executed.

---

## 5. Initial System Setup & Credentials Rotation

1. Navigate to: `https://yourdomain.com/admin/login.php`
2. Log in using default credentials:
   * **Username:** `admin`
   * **Password:** `AdminPassword2026!`
3. **Change Password:** Go to **User Management** &rarr; **Administrators** &rarr; edit `admin` &rarr; set a strong corporate passphrase.
4. **Update Registration Numbers:** Go to **Site Settings** &rarr; **General & Legal** &rarr; replace `[ADD CAC NUMBER]` and `[ADD RC NUMBER]` with official verified registration codes.
5. **Update Contact Details:** In **Site Settings** &rarr; **Headquarters & Contact**, enter official corporate emails and paste the Google Maps `<iframe>` embed code.

---

## 6. Staff Roles & Permission Matrix

| Role | Permitted Actions | Target Staff |
| :--- | :--- | :--- |
| **Super Admin** | Full system control, backups, settings, audit trails, and staff user management. | Managing Director, Lead IT Officer |
| **Admin** | Manages RFQs, client messages, products, projects, services, articles, and settings. | General Manager, Operations Head |
| **Editor** | Creates and updates products, projects, services, and articles. Cannot change system settings. | Senior Procurement Officer, Project Engineer |
| **Content Manager** | Publishes articles, uploads site photos, and edits copy. | Corporate Communications, Media Officer |

---

## 7. Day-to-Day Operations & Commercial Workflows

### 7.1 Request for Quotes (RFQs)
* Incoming RFQs appear in `/admin/rfqs.php` with status `NEW`.
* Click **Review &rarr;** to inspect client requirements, quantity, industry sector, and any attached specification PDF or technical drawing.
* Update status from `NEW` &rarr; `UNDER REVIEW` &rarr; `QUOTATION PREPARED` &rarr; `SENT` &rarr; `COMPLETED`.
* Check **"Send email notification to client"** to dispatch an automated notification with internal notes.
* Click **Print RFQ Sheet** to generate a clean, print-formatted tender worksheet.

### 7.2 Product Catalog & Technical Datasheets
* Add new products in `/admin/products.php`.
* Input technical specifications separated by double pipes (`Property: Value||Property 2: Value 2`).
* Upload primary product photo, multi-image gallery photos, and technical PDF datasheet.
* The public product page automatically displays specifications and a "Download Technical Datasheet (PDF)" button.

### 7.3 Project Portfolio Case Studies
* Document civil works and supply contracts in `/admin/projects.php`.
* Input contracting scope items separated by double pipes (`||`).
* Upload featured photo and multiple project site progress photos.

### 7.4 Site Settings & Narrative
* Customize the homepage headline, buttons, and background photo in `/admin/homepage-cms.php`.
* Toggle homepage sections on or off with checkboxes.

---

## 8. Backup & Maintenance

* **One-Click SQL Backup:** Generate and download instant backups anytime in `/admin/backup.php`.
* **Automated Daily Cron:** Schedule in cPanel (`0 2 * * *`):
  ```bash
  mysqldump -u cpuser_stoneadmin -p'YourPassword' cpuser_stoneenergy | gzip > /home/username/backups/db_$(date +\%F).sql.gz
  ```
* **Audit Trail:** Review immutable user and login activity anytime in `/admin/audit-logs.php`.
