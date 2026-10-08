# STONE ENERGY INT'L LTD — Database Backup & Disaster Recovery Strategy

## 1. On-Demand Administrative SQL Export

Super Administrators can create an immediate, complete snapshot of the database at any time:

1. Log into the CMS Admin at `/admin/login.php`.
2. In the left navigation, click **Database Backup** (under Administration).
3. Click **"Export Full MySQL Database Dump (.sql)"**.
4. The system directly streams a timestamped `.sql` file (`stoneenergy_backup_YYYY_MM_DD_HHMMSS.sql`) directly to your local computer.
5. **Security Policy:** Database backups are never saved into public web folders.

---

## 2. Automated Scheduled Backups (cPanel Cron Jobs)

To ensure continuous disaster recovery without manual intervention, configure a daily cPanel Cron Job:

1. Open cPanel and navigate to **Cron Jobs**.
2. Select interval: **Once Per Day** (e.g., at `02:00` midnight).
3. Enter the command to export the database outside the public web root:

```bash
mysqldump -u cpuser_stoneadmin -p'YourPassword' cpuser_stoneenergy | gzip > /home/cpuser/db_backups/stoneenergy_$(date +\%F).sql.gz
```

4. Create a secondary cron job to clean backups older than 30 days:
```bash
find /home/cpuser/db_backups/ -type f -name "*.sql.gz" -mtime +30 -exec rm {} \;
```

---

## 3. Media Assets Backup Strategy

Uploads (specifications, BOQ documents, project photos, product sheets) are stored in `assets/uploads/`.

Schedule a weekly archive of the uploads directory via cron:
```bash
tar -czf /home/cpuser/media_backups/uploads_$(date +\%F).tar.gz /home/cpuser/public_html/assets/uploads/
```

---

## 4. Disaster Recovery Restoration Procedure

In the event of server corruption or accidental data deletion:

### Step 1: Restore Database
```bash
# Decompress and import SQL dump
gunzip < /home/cpuser/db_backups/stoneenergy_2026-10-06.sql.gz | mysql -u cpuser_stoneadmin -p cpuser_stoneenergy
```

### Step 2: Restore Media Files
```bash
tar -xzf /home/cpuser/media_backups/uploads_2026-10-06.tar.gz -C /home/cpuser/public_html/assets/
```

### Step 3: Verify Integrity
1. Visit the website to confirm products, projects, and settings load correctly.
2. Log into the admin portal to confirm user sessions and audit logs are intact.
