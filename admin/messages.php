<?php
/**
 * STONE ENERGY INT'L LTD - Contact Messages Management
 */
$requiredPermission = 'messages.manage';
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Contact Messages | STONE ENERGY INT'L LTD CMS";
$adminSection = "Inquiries & Messages";

$filter = trim($_GET['filter'] ?? 'all');
$searchTerm = trim($_GET['search'] ?? '');
$viewId = (int)($_GET['view'] ?? 0);

// Actions: mark read, archive, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    CSRF::validateOrAbort();
    $msgId = (int)($_POST['id'] ?? 0);

    if ($msgId > 0) {
        if ($_POST['action'] === 'mark_read') {
            Database::update('contact_messages', ['is_read' => 1], '`id` = :id', [':id' => $msgId]);
            set_flash('success', 'Message marked as read.');
        } elseif ($_POST['action'] === 'archive') {
            Database::update('contact_messages', ['is_archived' => 1], '`id` = :id', [':id' => $msgId]);
            set_flash('success', 'Message moved to archive.');
        } elseif ($_POST['action'] === 'delete') {
            Database::delete('contact_messages', '`id` = :id', [':id' => $msgId]);
            Audit::log('delete_message', 'messages', (string)$msgId, "Deleted contact message #{$msgId}");
            set_flash('success', 'Message deleted permanently.');
        }
    }
    header('Location: ' . admin_url('messages.php?' . http_build_query($_GET)));
    exit;
}

// Mark message as read if single view requested
$activeMessage = null;
if ($viewId > 0) {
    $activeMessage = Database::fetchOne("SELECT * FROM `contact_messages` WHERE `id` = :id", [':id' => $viewId]);
    if ($activeMessage && !$activeMessage['is_read']) {
        Database::update('contact_messages', ['is_read' => 1], '`id` = :id', [':id' => $viewId]);
    }
}

// Build query
$whereClauses = [];
$params = [];

if ($filter === 'unread') {
    $whereClauses[] = "`is_read` = 0 AND `is_archived` = 0";
} elseif ($filter === 'archived') {
    $whereClauses[] = "`is_archived` = 1";
} else {
    $whereClauses[] = "`is_archived` = 0";
}

if (!empty($searchTerm)) {
    $whereClauses[] = "(`name` LIKE :q OR `email` LIKE :q OR `subject` LIKE :q OR `message` LIKE :q)";
    $params[':q'] = "%{$searchTerm}%";
}

$whereSql = implode(' AND ', $whereClauses);
$messages = Database::fetchAll("SELECT * FROM `contact_messages` WHERE {$whereSql} ORDER BY `id` DESC LIMIT 50", $params);

include __DIR__ . '/includes/header.php';
?>

<?php if ($activeMessage): ?>
<!-- Detailed Message View Card -->
<div class="admin-card" style="border: 2px solid var(--admin-accent); margin-bottom: 30px;">
    <div class="admin-card-header" style="background: #fffbeb;">
        <div>
            <h3 style="color: #92400e;">Subject: <?= e($activeMessage['subject']) ?></h3>
            <span style="font-size: 0.8rem; color: var(--admin-text-muted);">
                Received <?= format_date($activeMessage['created_at'], 'M j, Y - H:i:s') ?> from <?= e($activeMessage['name']) ?> (<?= e($activeMessage['email']) ?>)
            </span>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="mailto:<?= e($activeMessage['email']) ?>?subject=RE: <?= urlencode($activeMessage['subject']) ?>" class="btn-admin btn-admin-primary">
                Reply via Email &rarr;
            </a>
            <a href="<?= admin_url('messages.php') ?>" class="btn-admin btn-admin-outline">&times; Close</a>
        </div>
    </div>
    <div class="admin-card-body">
        <div style="display: flex; gap: 24px; margin-bottom: 20px; font-size: 0.9rem; color: var(--admin-text);">
            <div><strong>Sender Phone:</strong> <?= e($activeMessage['phone'] ?: 'N/A') ?></div>
            <div><strong>Sender IP:</strong> <code><?= e($activeMessage['ip_address'] ?: 'Unknown') ?></code></div>
        </div>
        <div style="background: var(--admin-body-bg); border-radius: 6px; padding: 20px; font-size: 0.95rem; line-height: 1.7; white-space: pre-wrap; color: var(--admin-text);">
            <?= e($activeMessage['message']) ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>Contact Inquiries</h3>
        <div style="display: flex; gap: 8px;">
            <a href="<?= admin_url('messages.php') ?>" class="btn-admin btn-admin-outline <?= ($filter === 'all') ? 'active' : '' ?>">Inbox</a>
            <a href="<?= admin_url('messages.php?filter=unread') ?>" class="btn-admin btn-admin-outline <?= ($filter === 'unread') ? 'active' : '' ?>">Unread</a>
            <a href="<?= admin_url('messages.php?filter=archived') ?>" class="btn-admin btn-admin-outline <?= ($filter === 'archived') ? 'active' : '' ?>">Archive</a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div style="padding: 16px 24px; border-bottom: 1px solid var(--admin-border); background: #f8fafc;">
        <form action="<?= admin_url('messages.php') ?>" method="GET" style="display: flex; gap: 8px;">
            <input type="hidden" name="filter" value="<?= e($filter) ?>">
            <input type="text" name="search" class="form-control-admin" placeholder="Search sender, email, keywords..." value="<?= e($searchTerm) ?>" style="width: 280px;">
            <button type="submit" class="btn-admin btn-admin-primary">Search</button>
            <?php if (!empty($searchTerm)): ?>
                <a href="<?= admin_url('messages.php?filter=' . urlencode($filter)) ?>" class="btn-admin btn-admin-outline">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Sender Name</th>
                    <th>Email / Phone</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--admin-text-muted); padding: 40px;">
                        No contact messages in this view.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($messages as $m): ?>
                <tr style="<?= $m['is_read'] ? '' : 'background-color: #f0fdf4; font-weight: 600;' ?>">
                    <td>
                        <?php if (!$m['is_read']): ?>
                            <span class="badge-status badge-new">New</span>
                        <?php else: ?>
                            <span class="badge-status badge-inactive">Read</span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($m['name']) ?></td>
                    <td>
                        <?= e($m['email']) ?><br>
                        <small style="color: var(--admin-text-muted);"><?= e($m['phone'] ?? '') ?></small>
                    </td>
                    <td>
                        <strong><?= e($m['subject']) ?></strong><br>
                        <small style="color: var(--admin-text-muted); font-weight: normal;"><?= truncate($m['message'], 40) ?></small>
                    </td>
                    <td>
                        <small><?= time_ago($m['created_at']) ?></small>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="<?= admin_url('messages.php?view=' . $m['id'] . '&filter=' . urlencode($filter)) ?>" class="btn-admin btn-admin-primary btn-icon" title="View Message">
                                Open
                            </a>
                            <form action="<?= admin_url('messages.php') ?>" method="POST" style="display: inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <input type="hidden" name="action" value="archive">
                                <button type="submit" class="btn-admin btn-admin-outline btn-icon" title="Archive">
                                    Archive
                                </button>
                            </form>
                            <form action="<?= admin_url('messages.php') ?>" method="POST" style="display: inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-admin btn-admin-danger btn-icon" data-confirm-delete="Permanently delete this message?" title="Delete">
                                    &times;
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
