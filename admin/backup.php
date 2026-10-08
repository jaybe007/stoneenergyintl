<?php
/**
 * STONE ENERGY INT'L LTD - Secure Database Backup & SQL Export (Super Admin Only)
 */
$requireSuperAdmin = true;
require_once __DIR__ . '/includes/auth-check.php';

// Handle Export Stream
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'export_sql') {
    CSRF::validateOrAbort();

    Audit::log('export_database', 'backup', null, "Exported full SQL database backup");

    $pdo = Database::getConnection();
    $dbName = env('DB_NAME', 'stoneenergy_db');
    $filename = "stoneenergy_backup_" . date('Y_m_d_His') . ".sql";

    // Set headers to stream directly to client without storing on public disk
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    // Generate SQL dump stream
    echo "-- =====================================================================\n";
    echo "-- STONE ENERGY INT'L LTD - Database Export\n";
    echo "-- Database: {$dbName}\n";
    echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Exported By: " . Auth::user()['username'] . " (" . Auth::user()['email'] . ")\n";
    echo "-- =====================================================================\n\n";
    echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        // Table structure
        $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        echo "DROP TABLE IF EXISTS `{$table}`;\n";
        echo $createTable['Create Table'] . ";\n\n";

        // Table data
        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $cols = array_keys($rows[0]);
            $colList = implode(', ', array_map(fn($c) => "`{$c}`", $cols));
            
            echo "INSERT INTO `{$table}` ({$colList}) VALUES\n";
            $valRows = [];
            foreach ($rows as $row) {
                $vals = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $vals[] = "NULL";
                    } else {
                        $vals[] = $pdo->quote((string)$val);
                    }
                }
                $valRows[] = "(" . implode(', ', $vals) . ")";
            }
            echo implode(",\n", $valRows) . ";\n\n";
        }
    }

    echo "SET FOREIGN_KEY_CHECKS = 1;\n";
    echo "-- Dump Complete --\n";
    exit;
}

$adminPageTitle = "Database Backup & Export | STONE ENERGY INT'L LTD CMS";
$adminSection = "System Backup";

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card" style="max-width: 800px; margin: 0 auto;">
    <div class="admin-card-header">
        <h3>Database Backup &amp; Disaster Recovery</h3>
        <span class="badge-status badge-approved">Super Admin Authorized</span>
    </div>
    <div class="admin-card-body">
        <p style="color: var(--admin-text); font-size: 0.95rem; line-height: 1.6; margin-bottom: 24px;">
            Export a full, consistent SQL dump of the <strong>STONE ENERGY INT'L LTD</strong> database. This export includes all site settings, user accounts, catalog products, civil projects, RFQ transactions, messages, and audit trail logs.
        </p>

        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 18px; margin-bottom: 28px;">
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" style="flex-shrink: 0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <div style="font-size: 0.88rem; color: #92400e;">
                    <strong>Security Protocol Notice:</strong> Backups are streamed directly to your browser over an encrypted connection and are never stored on public web directories. Keep generated SQL dump files in secure offline corporate storage.
                </div>
            </div>
        </div>

        <form action="<?= admin_url('backup.php') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="export_sql">
            
            <button type="submit" class="btn-admin btn-admin-primary" style="padding: 14px 28px; font-size: 1rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export Full MySQL Database Dump (.sql)
            </button>
        </form>

        <div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--admin-border);">
            <h4 style="font-size: 1.05rem; margin-bottom: 12px; color: var(--admin-text);">Automated Hosting &amp; cPanel Backup Advice</h4>
            <ul style="font-size: 0.88rem; color: var(--admin-text-muted); line-height: 1.7; padding-left: 20px; list-style: disc;">
                <li><strong>cPanel Automated Cron:</strong> Use standard cPanel Cron Jobs to execute <code>mysqldump -u [user] -p[pass] [dbname] | gzip > /home/user/backups/db_$(date +\%F).sql.gz</code> outside public_html.</li>
                <li><strong>Retention Policy:</strong> Retain daily snapshots for 7 days, weekly for 4 weeks, and monthly for 12 months.</li>
                <li><strong>Media Backups:</strong> Schedule weekly rsync or tar backups of the <code>assets/uploads/</code> directory.</li>
            </ul>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
