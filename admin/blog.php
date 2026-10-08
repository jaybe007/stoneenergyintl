<?php
/**
 * STONE ENERGY INT'L LTD - Blog & Articles Management
 */
$requiredPermission = 'blog.manage';
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Articles & Blog CMS | STONE ENERGY INT'L LTD CMS";
$adminSection = "Articles & News";

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    CSRF::validateOrAbort();
    $postId = (int)($_POST['id'] ?? 0);
    if ($postId > 0) {
        $post = Database::fetchOne("SELECT title FROM `blog_posts` WHERE `id` = :id", [':id' => $postId]);
        Database::delete('blog_posts', '`id` = :id', [':id' => $postId]);
        Audit::log('delete_blog_post', 'blog', (string)$postId, "Deleted blog post '{$post['title']}'");
        set_flash('success', 'Article deleted successfully.');
    }
    header('Location: ' . admin_url('blog.php'));
    exit;
}

$posts = Database::fetchAll(
    "SELECT b.*, c.name as category_name, u.full_name as author_name 
     FROM `blog_posts` b 
     JOIN `blog_categories` c ON b.category_id = c.id 
     JOIN `users` u ON b.author_id = u.id 
     ORDER BY b.published_at DESC"
);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>News &amp; Technical Articles (<?= count($posts) ?> Total)</h3>
        <div style="display: flex; gap: 10px;">
            <a href="<?= admin_url('blog-categories.php') ?>" class="btn-admin btn-admin-outline">
                Categories
            </a>
            <a href="<?= admin_url('blog-edit.php') ?>" class="btn-admin btn-admin-primary">
                + Write New Article
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Article Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--admin-text-muted); padding: 40px;">
                        No published articles yet.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($posts as $p): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <img src="<?= upload_url($p['featured_image']) ?>" alt="" style="width: 44px; height: 44px; object-fit: cover; border-radius: 4px; border: 1px solid var(--admin-border);">
                            <div>
                                <strong><?= e($p['title']) ?></strong><br>
                                <small style="color: var(--admin-text-muted);"><?= truncate($p['excerpt'], 45) ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?= e($p['category_name']) ?></td>
                    <td><small><?= e($p['author_name']) ?></small></td>
                    <td>
                        <span class="badge-status <?= ($p['status'] === 'Published') ? 'badge-active' : 'badge-inactive' ?>">
                            <?= e($p['status']) ?>
                        </span>
                    </td>
                    <td><small><?= format_date($p['published_at']) ?></small></td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="<?= admin_url('blog-edit.php?id=' . $p['id']) ?>" class="btn-admin btn-admin-outline btn-icon" title="Edit">
                                Edit
                            </a>
                            <a href="<?= url('blog.php?slug=' . urlencode($p['slug'])) ?>" target="_blank" class="btn-admin btn-admin-outline btn-icon" title="View Public Post">
                                View
                            </a>
                            <form action="<?= admin_url('blog.php') ?>" method="POST" style="display: inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-admin btn-admin-danger btn-icon" data-confirm-delete="Delete article '<?= e($p['title']) ?>'?" title="Delete">
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
