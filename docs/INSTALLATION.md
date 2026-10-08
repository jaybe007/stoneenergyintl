# STONE ENERGY INT'L LTD — Installation Guide

## 1. System Requirements

* **Operating System:** Linux (Ubuntu/Debian, AlmaLinux, CentOS, CloudLinux) or Windows (XAMPP/WAMP)
* **Web Server:** Apache 2.4+ (with `mod_rewrite`, `mod_headers`)
* **PHP Engine:** PHP 8.1, 8.2, or 8.3
* **Required PHP Extensions:** `pdo`, `pdo_mysql`, `fileinfo`, `mbstring`, `json`, `openssl`, `session`
* **Database:** MySQL 5.7+ or MariaDB 10.3+
* **Storage:** 500MB+ for files and growing media assets

---

## 2. Fast Installation via Web Wizard

1. Upload or copy the project files to your web document root (e.g., `c:\xampp\htdocs\stoneenergyintl` or `/home/username/public_html`).
2. Verify that Apache and MySQL are running.
3. Navigate in your web browser to:
   ```
   http://localhost/stoneenergyintl/install.php
   ```
4. Enter your MySQL database credentials:
   * **Host:** `127.0.0.1` or `localhost`
   * **Port:** `3306`
   * **Database Name:** `stoneenergy_db`
   * **User:** `root` (or your cPanel database username)
   * **Password:** (your database password)
5. Enter your initial Super Admin credentials:
   * **Username:** `admin`
   * **Email:** `admin@stoneenergyintl.com`
   * **Password:** (minimum 8 characters)
6. Click **"Run Installation & Lock System"**.
7. The installer creates all 31 normalized tables, baseline seed records, compiles your `.env` configuration file, and creates the `config/installed.lock` safety file.

---

## 3. Manual Installation (Command Line / phpMyAdmin)

If you prefer provisioning via CLI or phpMyAdmin:

1. Create the database:
   ```sql
   CREATE DATABASE stoneenergy_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Import the complete schema:
   ```bash
   mysql -u root -p stoneenergy_db < database/schema.sql
   ```
3. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
4. Update `.env` with your database credentials:
   ```ini
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://localhost/stoneenergyintl

   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=stoneenergy_db
   DB_USER=root
   DB_PASS=your_password
   ```
5. Create the installation lockfile:
   ```bash
   touch config/installed.lock
   ```

---

## 4. Default Administrator Credentials

* **Login URL:** `http://localhost/stoneenergyintl/admin/login.php`
* **Default Username:** `admin`
* **Default Password:** `AdminPassword2026!`
* **Role:** Super Admin (Full Access)

> **Important:** Immediately log into the admin dashboard upon installation, navigate to **Admin Users**, and update the default password.
