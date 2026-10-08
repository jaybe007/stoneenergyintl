<?php
/**
 * STONE ENERGY INT'L LTD - Project Portfolio Management
 */
$requiredPermission = 'projects.manage';
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Project Portfolio | STONE ENERGY INT'L LTD CMS";
$adminSection = "Project Portfolio";

$statusFilter = trim($_GET['status'] ?? '');
$searchTerm = trim($_GET['search'] ?? '');

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    CSRF::validateOrAbort();
    $projId = (int)($_POST['id'] ?? 0);
    if ($projId > 0) {
        $p = Database::fetchOne("SELECT title FROM `projects` WHERE `id` = :id", [':id' => $projId]);
        Database::delete('projects', '`id` = :id', [':id' => $projId]);
        Audit::log('delete_project', 'projects', (string)$projId, "Deleted project '{$p['title']}'");
        set_flash('success', 'Project deleted successfully.');
    }
    header('Location: ' . admin_url('projects.php'));
    exit;
}

$whereClauses = ["1=1"];
$params = [];

if (!empty($statusFilter)) {
    $whereClauses[] = "p.status = :st";
    $params[':st'] = $statusFilter;
}

if (!empty($searchTerm)) {
    $whereClauses[] = "(p.title LIKE :q OR p.location LIKE :q OR p.client LIKE :q)";
    $params[':q'] = "%{$searchTerm}%";
}

$whereSql = implode(' AND ', $whereClauses);
$projects = Database::fetchAll(
    "SELECT p.*, c.name as category_name 
     FROM `projects` p 
     JOIN `project_categories` c ON p.category_id = c.id 
     WHERE {$whereSql} 
     ORDER BY p.id DESC",
    $params
);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>Civil &amp; Supply Projects (<?= count($projects) ?> Total)</h3>
        <a href="<?= admin_url('project-edit.php') ?>" class="btn-admin btn-admin-primary">
            + Add New Project
        </a>
    </div>

    <!-- Filter Bar -->
    <div style="padding: 16px 24px; border-bottom: 1px solid var(--admin-border); background: #f8fafc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <form action="<?= admin_url('projects.php') ?>" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <select name="status" class="form-control-admin" style="width: 180px;">
                <option value="">All Statuses</option>
                <option value="Upcoming" <?= ($statusFilter === 'Upcoming') ? 'selected' : '' ?>>Upcoming</option>
                <option value="Ongoing" <?= ($statusFilter === 'Ongoing') ? 'selected' : '' ?>>Ongoing</option>
                <option value="Completed" <?= ($statusFilter === 'Completed') ? 'selected' : '' ?>>Completed</option>
            </select>
            <input type="text" name="search" class="form-control-admin" placeholder="Search title, location..." value="<?= e($searchTerm) ?>" style="width: 220px;">
            <button type="submit" class="btn-admin btn-admin-primary">Filter</button>
            <?php if (!empty($searchTerm) || !empty($statusFilter)): ?>
                <a href="<?= admin_url('projects.php') ?>" class="btn-admin btn-admin-outline">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Project Title</th>
                    <th>Sector Category</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Client Entity</th>
                    <th>Visibility</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($projects)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--admin-text-muted); padding: 40px;">
                        No projects match your filter.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($projects as $proj): 
                    $sClass = match ($proj['status']) {
                        'Completed' => 'badge-approved',
                        'Ongoing' => 'badge-under-review',
                        default => 'badge-new'
                    };
                ?>
                <tr>
                    <td>
                        <strong><?= e($proj['title']) ?></strong><br>
                        <small style="color: var(--admin-text-muted);"><?= truncate($proj['description'], 45) ?></small>
                    </td>
                    <td><?= e($proj['category_name']) ?></td>
                    <td><?= e($proj['location']) ?></td>
                    <td>
                        <span class="badge-status <?= $sClass ?>"><?= e($proj['status']) ?></span>
                    </td>
                    <td><small><?= e($proj['client']) ?></small></td>
                    <td>
                        <span class="badge-status <?= ($proj['published_status'] === 'published') ? 'badge-active' : 'badge-inactive' ?>">
                            <?= e($proj['published_status']) ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="<?= admin_url('project-edit.php?id=' . $proj['id']) ?>" class="btn-admin btn-admin-outline btn-icon" title="Edit">
                                Edit
                            </a>
                            <a href="<?= url('projects.php?slug=' . urlencode($proj['slug'])) ?>" target="_blank" class="btn-admin btn-admin-outline btn-icon" title="View Public Page">
                                View
                            </a>
                            <form action="<?= admin_url('projects.php') ?>" method="POST" style="display: inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $proj['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-admin btn-admin-danger btn-icon" data-confirm-delete="Permanently delete project '<?= e($proj['title']) ?>'?" title="Delete">
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
