<?php
/**
 * STONE ENERGY INT'L LTD - Installation & Database Provisioning Wizard
 */
declare(strict_types=1);

define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('LOCK_FILE', ROOT_PATH . 'config' . DIRECTORY_SEPARATOR . 'installed.lock');

// Prevent re-installation if already locked
if (file_exists(LOCK_FILE)) {
    die("<!DOCTYPE html><html><head><title>System Installed</title><style>body{font-family:sans-serif;background:#0a192f;color:#fff;padding:50px;text-align:center;}a{color:#d97706;}</style></head><body><h1>System Already Installed</h1><p>STONE ENERGY INT'L LTD CMS is already configured and locked for security.</p><p><a href='./admin/login.php'>Access Admin Portal</a> | <a href='./'>View Website</a></p></body></html>");
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbName = trim($_POST['db_name'] ?? 'stoneenergy_db');
    $dbUser = trim($_POST['db_user'] ?? 'root');
    $dbPass = $_POST['db_pass'] ?? '';

    $adminUser  = trim($_POST['admin_user'] ?? 'admin');
    $adminEmail = trim($_POST['admin_email'] ?? 'admin@stoneenergyintl.com');
    $adminPass  = $_POST['admin_pass'] ?? '';
    $adminName  = trim($_POST['admin_name'] ?? 'Principal Administrator');

    if (empty($dbHost) || empty($dbName) || empty($dbUser) || empty($adminUser) || empty($adminEmail) || empty($adminPass)) {
        $error = "Please fill in all mandatory database and administrator fields.";
    } elseif (strlen($adminPass) < 8) {
        $error = "Admin password must be at least 8 characters long.";
    } else {
        try {
            // Test connection
            $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);

            // Create DB if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbName}`");

            // Execute schema.sql
            $schemaFile = ROOT_PATH . 'database' . DIRECTORY_SEPARATOR . 'schema.sql';
            if (!file_exists($schemaFile)) {
                throw new Exception("Database schema file missing at database/schema.sql");
            }

            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);

            // Update or Insert Super Admin
            $adminHash = password_hash($adminPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("SELECT id FROM `users` WHERE `id` = 1 LIMIT 1");
            $stmt->execute();
            if ($stmt->fetch()) {
                $upStmt = $pdo->prepare("UPDATE `users` SET `username` = :u, `email` = :e, `password_hash` = :p, `full_name` = :f, `role_id` = 1, `status` = 'active' WHERE `id` = 1");
                $upStmt->execute([':u' => $adminUser, ':e' => $adminEmail, ':p' => $adminHash, ':f' => $adminName]);
            } else {
                $inStmt = $pdo->prepare("INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `role_id`, `status`) VALUES (:u, :e, :p, :f, 1, 'active')");
                $inStmt->execute([':u' => $adminUser, ':e' => $adminEmail, ':p' => $adminHash, ':f' => $adminName]);
            }

            // Write .env file
            $envContent = <<<ENV
APP_ENV=production
APP_DEBUG=false
APP_URL=http://{$_SERVER['HTTP_HOST']}/stoneenergyintl
APP_KEY=" . bin2hex(random_bytes(16)) . "

DB_HOST={$dbHost}
DB_PORT={$dbPort}
DB_NAME={$dbName}
DB_USER={$dbUser}
DB_PASS={$dbPass}

MAIL_HOST=localhost
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@stoneenergyintl.com
MAIL_FROM_NAME="STONE ENERGY INT'L LTD"

SESSION_LIFETIME=7200
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=15
ENV;
            file_put_contents(ROOT_PATH . '.env', $envContent);

            // Create Lock File
            file_put_contents(LOCK_FILE, "Installed on " . date('Y-m-d H:i:s') . "\n");

            $success = true;
        } catch (Exception $e) {
            $error = "Installation Failure: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Wizard | STONE ENERGY INT'L LTD</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        body { background: #0a192f; color: #f8fafc; padding: 40px 20px; font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .install-box { background: #ffffff; color: #1e293b; border-radius: 12px; width: 100%; max-width: 640px; padding: 40px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
    </style>
</head>
<body>
    <div class="install-box">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="assets/images/logo.svg" alt="Stone Energy Int'l Ltd" style="height: 48px; margin-bottom: 12px;">
            <h2 style="font-size: 1.5rem; color: #0a192f;">System Installation Wizard</h2>
            <p style="font-size: 0.85rem; color: #64748b;">Provision MySQL database schema &amp; initialize Super Admin account</p>
        </div>

        <?php if ($success): ?>
            <div style="background: #ecfdf5; border: 1.5px solid #059669; border-radius: 8px; padding: 24px; text-align: center;">
                <h3 style="color: #065f46; margin-bottom: 10px;">Installation Completed Successfully!</h3>
                <p style="font-size: 0.92rem; color: #065f46; margin-bottom: 20px;">
                    All 31 MySQL tables, site settings, and the Super Admin account have been provisioned. The security lock file has been established.
                </p>
                <div style="display: flex; justify-content: center; gap: 12px;">
                    <a href="admin/login.php" class="btn-admin btn-admin-primary">Log in to Admin CMS &rarr;</a>
                    <a href="./" class="btn-admin btn-admin-outline">View Website</a>
                </div>
            </div>
        <?php else: ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="install.php" method="POST">
                <h4 style="font-size: 1rem; color: #0a192f; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;">
                    1. MySQL / MariaDB Database Connection
                </h4>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px;">
                    <div class="form-row">
                        <label class="form-label-admin">DB Host</label>
                        <input type="text" name="db_host" class="form-control-admin" required value="127.0.0.1">
                    </div>
                    <div class="form-row">
                        <label class="form-label-admin">DB Port</label>
                        <input type="number" name="db_port" class="form-control-admin" required value="3306">
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Database Name</label>
                    <input type="text" name="db_name" class="form-control-admin" required value="stoneenergy_db">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-row">
                        <label class="form-label-admin">DB Username</label>
                        <input type="text" name="db_user" class="form-control-admin" required value="root">
                    </div>
                    <div class="form-row">
                        <label class="form-label-admin">DB Password</label>
                        <input type="password" name="db_pass" class="form-control-admin" placeholder="Database password">
                    </div>
                </div>

                <h4 style="font-size: 1rem; color: #0a192f; margin: 24px 0 12px 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;">
                    2. Primary Super Admin Account
                </h4>

                <div class="form-row">
                    <label class="form-label-admin">Full Legal Name</label>
                    <input type="text" name="admin_name" class="form-control-admin" required value="Principal Administrator">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-row">
                        <label class="form-label-admin">Admin Username</label>
                        <input type="text" name="admin_user" class="form-control-admin" required value="admin">
                    </div>
                    <div class="form-row">
                        <label class="form-label-admin">Admin Corporate Email</label>
                        <input type="email" name="admin_email" class="form-control-admin" required value="admin@stoneenergyintl.com">
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Admin Password (min 8 characters)</label>
                    <input type="password" name="admin_pass" class="form-control-admin" required placeholder="Choose a strong password" value="AdminPassword2026!">
                </div>

                <div style="margin-top: 28px;">
                    <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; justify-content: center; padding: 14px; font-size: 1rem;">
                        Run Installation &amp; Lock System &rarr;
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
