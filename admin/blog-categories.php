<?php
/**
 * STONE ENERGY INT'L LTD - Blog Categories Management
 */
$requiredPermission = 'blog.manage';
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Blog Categories | STONE ENERGY INT'L LTD CMS";
$adminSection = "Blog Categories";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    CSRF::validateOrAbort();

    if ($_POST['action'] === 'save') {
        $catId = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
        $desc = trim($_POST['description'] ?? '');

        if (!empty($name)) {
            $data = ['name' => $name, 'slug' => $slug, 'description' => $desc];
            if ($catId > 0) {
                Database::update('blog_categories', $data, '`id` = :id', [':id' => $catId]);
                Audit::log('update_blog_category', 'blog', (string)$catId, "Updated blog category '{$name}'");
                set_flash('success', "Category '{$name}' updated.");
            } else {
                $newId = Database::insert('blog_categories', $data);
                Audit::log('create_blog_category', 'blog', (string)$newId, "Created blog category '{$name}'");
                set_flash('success', "New category '{$name}' created.");
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $catId = (int)($_POST['id'] ?? 0);
        $countPosts = (int)Database::fetchColumn("SELECT COUNT(*) FROM `blog_posts` WHERE `category_id` = :cid", [':cid' => $catId]);
        if ($countPosts > 0) {
            set_flash('error', "Cannot delete category: contains {$countPosts} articles.");
        } else {
            Database::delete('blog_categories', '`id` = :id', [':id' => $catId]);
            Audit::log('delete_blog_category', 'blog', (string)$catId, "Deleted blog category #{$catId}");
            set_flash('success', "Category deleted.");
        }
    }
    header('Location: ' . admin_url('blog-categories.php'));
    exit;
}

$categories = Database::fetchAll("SELECT c.*, (SELECT COUNT(*) FROM `blog_posts` b WHERE b.category_id = c.id) as post_count FROM `blog_categories` c ORDER BY c.id ASC");

include __DIR__ . '/includes/header.php';
?>

<div style="display: grid; grid-template-columns: 1.2fr 2fr; gap: 28px;">
    <!-- Add Category Form -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Add Topic Category</h3>
        </div>
        <div class="admin-card-body">
            <form action="<?= admin_url('blog-categories.php') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="0">

                <div class="form-row">
                    <label class="form-label-admin">Category Name <span style="color:red;">*</span></label>
                    <input type="text" name="name" class="form-control-admin" required placeholder="e.g. Procurement Insights">
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Slug (Auto if blank)</label>
                    <input type="text" name="slug" class="form-control-admin" placeholder="procurement-insights">
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Description</label>
                    <textarea name="description" class="form-control-admin" style="min-height: 80px;" placeholder="Editorial focus of this topic..."></textarea>
                </div>

                <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; justify-content: center;">
                    Save Category &rarr;
                </button>
            </form>
        </div>
    </div>

    <!-- Category List -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Blog Categories</h3>
            <a href="<?= admin_url('blog.php') ?>" class="btn-admin btn-admin-outline" style="font-size: 0.8rem;">
                Back to Articles &rarr;
            </a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Slug</th>
                        <th>Articles</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td>
                            <strong><?= e($cat['name']) ?></strong><br>
                            <small style="color: var(--admin-text-muted);"><?= e($cat['description'] ?? '') ?></small>
                        </td>
                        <td><code><?= e($cat['slug']) ?></code></td>
                        <td>
                            <span style="font-weight: 700; color: var(--admin-accent);"><?= $cat['post_count'] ?> posts</span>
                        </td>
                        <td>
                            <?php if ($cat['post_count'] == 0): ?>
                            <form action="<?= admin_url('blog-categories.php') ?>" method="POST" style="display: inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                <button type="submit" class="btn-admin btn-admin-danger btn-icon" data-confirm-delete="Delete category '<?= e($cat['name']) ?>'?" title="Delete">
                                    &times;
                                </button>
                            </form>
                            <?php else: ?>
                            <span style="font-size: 0.72rem; color: var(--admin-text-muted);">In Use</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
