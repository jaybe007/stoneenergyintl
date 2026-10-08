<?php
/**
 * STONE ENERGY INT'L LTD - Create / Edit Administrator
 */
$requireSuperAdmin = true;
require_once __DIR__ . '/includes/auth-check.php';

$userId = (int)($_GET['id'] ?? 0);
$isEditing = ($userId > 0);
$userRecord = $isEditing ? Database::fetchOne("SELECT * FROM `users` WHERE `id` = :id", [':id' => $userId]) : null;

if ($isEditing && !$userRecord) {
    set_flash('error', 'User record not found.');
    header('Location: ' . admin_url('users.php'));
    exit;
}

$adminPageTitle = ($isEditing ? "Edit: " . $userRecord['username'] : "Create Administrator") . " | CMS";
$adminSection = "User Management";
$errorMessage = '';

$roles = Database::fetchAll("SELECT * FROM `roles` ORDER BY `id` ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $roleId   = (int)($_POST['role_id'] ?? 4);
    $status   = in_array($_POST['status'] ?? '', ['active', 'inactive', 'suspended'], true) ? $_POST['status'] : 'active';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($fullName)) {
        $errorMessage = "Please enter username, email address, and full name.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please provide a valid corporate email.";
    } elseif (!$isEditing && (empty($password) || strlen($password) < 8)) {
        $errorMessage = "A password with at least 8 characters is required for new accounts.";
    } else {
        // Unique username & email checks
        $conflict = Database::fetchOne(
            "SELECT id FROM `users` WHERE (username = :u OR email = :e) AND id != :id LIMIT 1",
            [':u' => $username, ':e' => $email, ':id' => $userId]
        );

        if ($conflict) {
            $errorMessage = "The username or email is already in use by another account.";
        } else {
            $data = [
                'username'  => $username,
                'email'     => $email,
                'full_name' => $fullName,
                'role_id'   => $roleId,
                'status'    => $status
            ];

            if (!empty($password)) {
                $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }

            if ($isEditing) {
                Database::update('users', $data, '`id` = :id', [':id' => $userId]);
                Audit::log('update_user', 'users', (string)$userId, "Updated user account '{$username}'");
                set_flash('success', "User '{$username}' updated successfully.");
            } else {
                $newId = Database::insert('users', $data);
                Audit::log('create_user', 'users', (string)$newId, "Created user account '{$username}'");
                set_flash('success', "New user '{$username}' created.");
            }

            header('Location: ' . admin_url('users.php'));
            exit;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card" style="max-width: 650px; margin: 0 auto;">
    <div class="admin-card-header">
        <h3><?= $isEditing ? 'Edit Administrator Account' : 'Create New Administrator' ?></h3>
        <a href="<?= admin_url('users.php') ?>" class="btn-admin btn-admin-outline">
            &larr; Back to Users
        </a>
    </div>
    <div class="admin-card-body">
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <span><?= e($errorMessage) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= admin_url('user-edit.php' . ($isEditing ? '?id=' . $userId : '')) ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-row">
                <label class="form-label-admin">Full Legal Name <span style="color:red;">*</span></label>
                <input type="text" name="full_name" class="form-control-admin" required value="<?= e($userRecord['full_name'] ?? '') ?>" placeholder="e.g. Babatunde Adeleke">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-row">
                    <label class="form-label-admin">Username <span style="color:red;">*</span></label>
                    <input type="text" name="username" class="form-control-admin" required value="<?= e($userRecord['username'] ?? '') ?>" placeholder="e.g. badeleke">
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Corporate Email <span style="color:red;">*</span></label>
                    <input type="email" name="email" class="form-control-admin" required value="<?= e($userRecord['email'] ?? '') ?>" placeholder="user@stoneenergyintl.com">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-row">
                    <label class="form-label-admin">Role &amp; Permissions</label>
                    <select name="role_id" class="form-control-admin" required>
                        <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= (($userRecord['role_id'] ?? 4) == $r['id']) ? 'selected' : '' ?>>
                            <?= e($r['name']) ?> (<?= e($r['description']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Account Status</label>
                    <select name="status" class="form-control-admin">
                        <option value="active" <?= (($userRecord['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (($userRecord['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        <option value="suspended" <?= (($userRecord['status'] ?? '') === 'suspended') ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <label class="form-label-admin">
                    Account Password <?= $isEditing ? '<span style="font-size:0.75rem; color:var(--admin-text-muted);">(Leave blank to keep current)</span>' : '<span style="color:red;">*</span>' ?>
                </label>
                <input type="password" name="password" class="form-control-admin" placeholder="<?= $isEditing ? '••••••••••••' : 'Minimum 8 characters' ?>" <?= $isEditing ? '' : 'required' ?> autocomplete="new-password">
            </div>

            <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--admin-border); display: flex; justify-content: flex-end; gap: 10px;">
                <a href="<?= admin_url('users.php') ?>" class="btn-admin btn-admin-outline">Cancel</a>
                <button type="submit" class="btn-admin btn-admin-primary">
                    <?= $isEditing ? 'Save User Profile' : 'Create User Account' ?> &rarr;
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
