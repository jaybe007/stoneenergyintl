<?php
/**
 * STONE ENERGY INT'L LTD - Services Management List
 */
$requiredPermission = 'services.manage';
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Services Management | STONE ENERGY INT'L LTD CMS";
$adminSection = "Services Directory";

// Handle Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    CSRF::validateOrAbort();
    $serviceId = (int)($_POST['id'] ?? 0);
    if ($serviceId > 0) {
        $svc = Database::fetchOne("SELECT title FROM `services` WHERE `id` = :id", [':id' => $serviceId]);
        Database::delete('services', '`id` = :id', [':id' => $serviceId]);
        Audit::log('delete_service', 'services', (string)$serviceId, "Deleted service '{$svc['title']}'");
        set_flash('success', 'Service deleted successfully.');
    }
    header('Location: ' . admin_url('services.php'));
    exit;
}

$services = Database::fetchAll(
    "SELECT s.*, c.name as category_name 
     FROM `services` s 
     JOIN `service_categories` c ON s.category_id = c.id 
     ORDER BY s.sort_order ASC"
);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>Services &amp; Capabilities (<?= count($services) ?> Total)</h3>
        <a href="<?= admin_url('service-edit.php') ?>" class="btn-admin btn-admin-primary">
            + Create New Service
        </a>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Service Title</th>
                    <th>Category</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $srv): ?>
                <tr>
                    <td><strong>#<?= (int)$srv['sort_order'] ?></strong></td>
                    <td>
                        <strong><?= e($srv['title']) ?></strong><br>
                        <small style="color: var(--admin-text-muted);"><?= truncate($srv['short_description'], 45) ?></small>
                    </td>
                    <td><?= e($srv['category_name']) ?></td>
                    <td><code><?= e($srv['slug']) ?></code></td>
                    <td>
                        <span class="badge-status <?= ($srv['status'] === 'active') ? 'badge-active' : 'badge-inactive' ?>">
                            <?= e($srv['status']) ?>
                        </span>
                    </td>
                    <td><small><?= format_date($srv['updated_at']) ?></small></td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="<?= admin_url('service-edit.php?id=' . $srv['id']) ?>" class="btn-admin btn-admin-outline btn-icon">
                                Edit
                            </a>
                            <a href="<?= url('services.php?slug=' . urlencode($srv['slug'])) ?>" target="_blank" class="btn-admin btn-admin-outline btn-icon" title="View Public Page">
                                View
                            </a>
                            <form action="<?= admin_url('services.php') ?>" method="POST" style="display: inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $srv['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-admin btn-admin-danger btn-icon" data-confirm-delete="Delete this service?" title="Delete">
                                    &times;
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
