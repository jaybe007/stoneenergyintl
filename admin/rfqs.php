<?php
/**
 * STONE ENERGY INT'L LTD - RFQ Management Subsystem
 */
$requiredPermission = 'rfqs.manage';
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Request for Quotes (RFQ) | STONE ENERGY INT'L LTD CMS";
$adminSection = "Commercial RFQs";

$statusFilter = trim($_GET['status'] ?? '');
$searchTerm = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Delete RFQ via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    CSRF::validateOrAbort();
    $rfqId = (int)($_POST['rfq_id'] ?? 0);
    if ($rfqId > 0) {
        $rfq = Database::fetchOne("SELECT * FROM `rfqs` WHERE `id` = :id", [':id' => $rfqId]);
        if ($rfq) {
            Database::delete('rfqs', '`id` = :id', [':id' => $rfqId]);
            Audit::log('delete_rfq', 'rfqs', (string)$rfqId, "Deleted RFQ {$rfq['rfq_number']} permanently");
            set_flash('success', "RFQ {$rfq['rfq_number']} has been permanently deleted.");
        }
    }
    header('Location: ' . admin_url('rfqs.php?' . http_build_query($_GET)));
    exit;
}

// Quick status change via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    CSRF::validateOrAbort();
    $rfqId = (int)$_POST['rfq_id'];
    $newStatus = trim($_POST['status']);
    $notifyClient = !empty($_POST['notify_client']);
    $adminNotes = trim($_POST['admin_notes'] ?? '');

    $validStatuses = ['NEW', 'UNDER REVIEW', 'QUOTATION PREPARED', 'SENT', 'NEGOTIATION', 'APPROVED', 'COMPLETED', 'CANCELLED'];
    if (in_array($newStatus, $validStatuses, true) && $rfqId > 0) {
        $rfq = Database::fetchOne("SELECT * FROM `rfqs` WHERE `id` = :id", [':id' => $rfqId]);
        if ($rfq) {
            Database::update('rfqs', [
                'status' => $newStatus,
                'admin_notes' => $adminNotes ?: $rfq['admin_notes']
            ], '`id` = :id', [':id' => $rfqId]);

            Audit::log('update_rfq_status', 'rfqs', (string)$rfqId, "Updated RFQ {$rfq['rfq_number']} status to {$newStatus}");

            if ($notifyClient && !empty($rfq['email'])) {
                Mailer::sendRfqStatusUpdate($rfq, $newStatus, $adminNotes);
            }

            set_flash('success', "RFQ {$rfq['rfq_number']} status changed to {$newStatus}.");
        }
    }
    header('Location: ' . admin_url('rfqs.php?' . http_build_query($_GET)));
    exit;
}

// Build query
$whereClauses = ["1=1"];
$params = [];

if (!empty($statusFilter)) {
    $whereClauses[] = "`status` = :st";
    $params[':st'] = $statusFilter;
}

if (!empty($searchTerm)) {
    $whereClauses[] = "(`rfq_number` LIKE :q OR `customer_name` LIKE :q OR `company_name` LIKE :q OR `email` LIKE :q OR `phone` LIKE :q OR `service_or_product` LIKE :q)";
    $params[':q'] = "%{$searchTerm}%";
}

$whereSql = implode(' AND ', $whereClauses);
$totalRfqs = (int)Database::fetchColumn("SELECT COUNT(*) FROM `rfqs` WHERE {$whereSql}", $params);
$totalPages = ceil($totalRfqs / $perPage);

$rfqs = Database::fetchAll(
    "SELECT * FROM `rfqs` WHERE {$whereSql} ORDER BY `id` DESC LIMIT {$perPage} OFFSET {$offset}",
    $params
);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>Request for Quotes Pipeline (<?= $totalRfqs ?> Total)</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?= admin_url('rfqs.php') ?>" class="btn-admin btn-admin-outline <?= empty($statusFilter) ? 'active' : '' ?>">All</a>
            <a href="<?= admin_url('rfqs.php?status=NEW') ?>" class="btn-admin btn-admin-outline <?= ($statusFilter === 'NEW') ? 'active' : '' ?>">New</a>
            <a href="<?= admin_url('rfqs.php?status=UNDER REVIEW') ?>" class="btn-admin btn-admin-outline <?= ($statusFilter === 'UNDER REVIEW') ? 'active' : '' ?>">Reviewing</a>
            <a href="<?= admin_url('rfqs.php?status=QUOTATION PREPARED') ?>" class="btn-admin btn-admin-outline <?= ($statusFilter === 'QUOTATION PREPARED') ? 'active' : '' ?>">Prepared</a>
            <a href="<?= admin_url('rfqs.php?status=APPROVED') ?>" class="btn-admin btn-admin-outline <?= ($statusFilter === 'APPROVED') ? 'active' : '' ?>">Approved</a>
        </div>
    </div>

    <!-- Search Toolbar -->
    <div style="padding: 18px 24px; border-bottom: 1px solid var(--admin-border); background: #f8fafc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <form action="<?= admin_url('rfqs.php') ?>" method="GET" style="display: flex; gap: 8px;">
            <?php if (!empty($statusFilter)): ?>
                <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
            <?php endif; ?>
            <input type="text" name="search" class="form-control-admin" placeholder="Search RFQ#, name, email..." value="<?= e($searchTerm) ?>" style="width: 260px;">
            <button type="submit" class="btn-admin btn-admin-primary">Filter</button>
            <?php if (!empty($searchTerm) || !empty($statusFilter)): ?>
                <a href="<?= admin_url('rfqs.php') ?>" class="btn-admin btn-admin-outline">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>RFQ #</th>
                    <th>Customer / Entity</th>
                    <th>Contact</th>
                    <th>Requirement Scope</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rfqs)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--admin-text-muted); padding: 40px;">
                        No commercial RFQs matching your search criteria.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($rfqs as $rfq): 
                    $badgeClass = match ($rfq['status']) {
                        'NEW' => 'badge-new',
                        'UNDER REVIEW' => 'badge-under-review',
                        'QUOTATION PREPARED', 'SENT' => 'badge-approved',
                        'APPROVED', 'COMPLETED' => 'badge-completed',
                        'CANCELLED' => 'badge-cancelled',
                        default => 'badge-under-review'
                    };
                ?>
                <tr>
                    <td>
                        <strong style="color: var(--admin-accent); font-size: 0.95rem;"><?= e($rfq['rfq_number']) ?></strong>
                        <?php if ($rfq['attachment_path']): ?>
                            <br><span style="font-size: 0.72rem; color: #2563eb;">&bull; Attachment attached</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= e($rfq['customer_name']) ?></strong><br>
                        <small style="color: var(--admin-text-muted);"><?= e($rfq['company_name'] ?: 'Private / Individual') ?></small>
                    </td>
                    <td>
                        <span><?= e($rfq['phone']) ?></span><br>
                        <small style="color: var(--admin-text-muted);"><?= e($rfq['email']) ?></small>
                    </td>
                    <td>
                        <strong><?= e(truncate($rfq['service_or_product'], 30)) ?></strong><br>
                        <small style="color: var(--admin-text-muted);">Sector: <?= e($rfq['industry']) ?></small>
                    </td>
                    <td>
                        <span class="badge-status <?= $badgeClass ?>"><?= e($rfq['status']) ?></span>
                    </td>
                    <td>
                        <small><?= format_date($rfq['created_at']) ?></small>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            <a href="<?= admin_url('rfq-view.php?id=' . $rfq['id']) ?>" class="btn-admin btn-admin-primary btn-icon" style="padding: 5px 11px; font-size: 0.8rem;">
                                Review &rarr;
                            </a>
                            <form action="<?= admin_url('rfqs.php?' . http_build_query($_GET)) ?>" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to permanently delete RFQ <?= e($rfq['rfq_number']) ?>?');" style="margin: 0; display: inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="rfq_id" value="<?= $rfq['id'] ?>">
                                <button type="submit" class="btn-admin" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 5px 8px; font-size: 0.8rem; cursor: pointer;" title="Delete RFQ">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
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

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div style="padding: 16px 24px; border-top: 1px solid var(--admin-border); display: flex; justify-content: center; gap: 6px;">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="<?= admin_url('rfqs.php?' . http_build_query(array_merge($_GET, ['page' => $p]))) ?>" class="btn-admin <?= ($page === $p) ? 'btn-admin-primary' : 'btn-admin-outline' ?>" style="padding: 4px 10px; font-size: 0.8rem;">
                <?= $p ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
