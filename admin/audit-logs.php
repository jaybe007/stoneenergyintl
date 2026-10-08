<?php
/**
 * STONE ENERGY INT'L LTD - Administrative Audit Trail Viewer
 */
$requiredPermission = 'audit.view';
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Audit Trail | STONE ENERGY INT'L LTD CMS";
$adminSection = "System Audit Logs";

$moduleFilter = trim($_GET['module'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$whereClauses = ["1=1"];
$params = [];

if (!empty($moduleFilter)) {
    $whereClauses[] = "a.module = :mod";
    $params[':mod'] = $moduleFilter;
}

$whereSql = implode(' AND ', $whereClauses);
$totalLogs = (int)Database::fetchColumn("SELECT COUNT(*) FROM `audit_logs` a WHERE {$whereSql}", $params);
$totalPages = ceil($totalLogs / $perPage);

$logs = Database::fetchAll(
    "SELECT a.*, u.username, u.full_name 
     FROM `audit_logs` a 
     LEFT JOIN `users` u ON a.user_id = u.id 
     WHERE {$whereSql} 
     ORDER BY a.id DESC LIMIT {$perPage} OFFSET {$offset}",
    $params
);

$modules = Database::fetchAll("SELECT DISTINCT `module` FROM `audit_logs` ORDER BY `module` ASC");

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>System Audit Trail (<?= $totalLogs ?> Events Recorded)</h3>
        <div style="font-size: 0.82rem; color: var(--admin-text-muted);">
            Immutable activity log tracking administrative operations and access events
        </div>
    </div>

    <!-- Filter by Module -->
    <div style="padding: 16px 24px; border-bottom: 1px solid var(--admin-border); background: #f8fafc;">
        <form action="<?= admin_url('audit-logs.php') ?>" method="GET" style="display: flex; gap: 10px; align-items: center;">
            <label style="font-size: 0.85rem; font-weight: 600;">Filter Module:</label>
            <select name="module" class="form-control-admin" style="width: 200px;">
                <option value="">All Modules</option>
                <?php foreach ($modules as $m): ?>
                <option value="<?= e($m['module']) ?>" <?= ($moduleFilter === $m['module']) ? 'selected' : '' ?>>
                    <?= ucfirst($m['module']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-admin btn-admin-primary">Apply</button>
            <?php if (!empty($moduleFilter)): ?>
                <a href="<?= admin_url('audit-logs.php') ?>" class="btn-admin btn-admin-outline">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Module</th>
                    <th>Description</th>
                    <th>Client IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--admin-text-muted); padding: 40px;">
                        No audit events recorded in this filter.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td>
                        <small style="font-weight: 600; color: var(--admin-text);"><?= format_date($log['created_at'], 'M j, Y - H:i:s') ?></small><br>
                        <small style="color: var(--admin-text-muted);"><?= time_ago($log['created_at']) ?></small>
                    </td>
                    <td>
                        <strong><?= e($log['username'] ?? 'System / Guest') ?></strong>
                    </td>
                    <td>
                        <code style="color: var(--admin-accent); font-weight: 700;"><?= e($log['action']) ?></code>
                    </td>
                    <td>
                        <span class="badge-status badge-inactive" style="text-transform: uppercase;">
                            <?= e($log['module']) ?>
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 0.88rem;"><?= e($log['description']) ?></span>
                    </td>
                    <td>
                        <code style="font-size: 0.78rem;"><?= e($log['ip_address']) ?></code>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div style="padding: 16px 24px; border-top: 1px solid var(--admin-border); display: flex; justify-content: center; gap: 6px;">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="<?= admin_url('audit-logs.php?' . http_build_query(array_merge($_GET, ['page' => $p]))) ?>" class="btn-admin <?= ($page === $p) ? 'btn-admin-primary' : 'btn-admin-outline' ?>" style="padding: 4px 10px; font-size: 0.8rem;">
                <?= $p ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
