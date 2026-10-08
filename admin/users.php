<?php
/**
 * STONE ENERGY INT'L LTD - Admin Users Management (Super Admin Only)
 */
$requireSuperAdmin = true;
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Admin Users | STONE ENERGY INT'L LTD CMS";
$adminSection = "User Management";

// Handle Delete User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    CSRF::validateOrAbort();
    $userId = (int)($_POST['id'] ?? 0);

    if ($userId === Auth::id()) {
        set_flash('error', 'You cannot delete your own administrative account.');
    } elseif ($userId > 0) {
        $u = Database::fetchOne("SELECT username FROM `users` WHERE `id` = :id", [':id' => $userId]);
        Database::delete('users', '`id` = :id', [':id' => $userId]);
        Audit::log('delete_user', 'users', (string)$userId, "Deleted user '{$u['username']}'");
        set_flash('success', "User '{$u['username']}' removed.");
    }
    header('Location: ' . admin_url('users.php'));
    exit;
}

$users = Database::fetchAll(
    "SELECT u.*, r.name as role_name 
     FROM `users` u 
     JOIN `roles` r ON u.role_id = r.id 
     ORDER BY u.id ASC"
);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>Administrative Users (<?= count($users) ?> Total)</h3>
        <a href="<?= admin_url('user-edit.php') ?>" class="btn-admin btn-admin-primary">
            + Create New Administrator
        </a>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="admin-avatar" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <strong><?= e($u['full_name']) ?></strong><br>
                                <code style="font-size: 0.75rem; color: var(--admin-accent);">@<?= e($u['username']) ?></code>
                            </div>
                        </div>
                    </td>
                    <td><?= e($u['email']) ?></td>
                    <td>
                        <strong style="color: var(--admin-text);"><?= e($u['role_name']) ?></strong>
                    </td>
                    <td>
                        <span class="badge-status <?= ($u['status'] === 'active') ? 'badge-active' : 'badge-inactive' ?>">
                            <?= e($u['status']) ?>
                        </span>
                    </td>
                    <td><small><?= time_ago($u['last_login']) ?></small></td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="<?= admin_url('user-edit.php?id=' . $u['id']) ?>" class="btn-admin btn-admin-outline btn-icon" title="Edit">
                                Edit
                            </a>
                            <?php if ($u['id'] !== Auth::id()): ?>
                            <form action="<?= admin_url('users.php') ?>" method="POST" style="display: inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn-admin btn-admin-danger btn-icon" data-confirm-delete="Permanently delete user '<?= e($u['username']) ?>'?" title="Delete">
                                    &times;
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
