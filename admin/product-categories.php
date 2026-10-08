<?php
/**
 * STONE ENERGY INT'L LTD - Product Categories Management
 */
$requiredPermission = 'products.manage';
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Product Categories | STONE ENERGY INT'L LTD CMS";
$adminSection = "Product Categories";

// Add or Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    CSRF::validateOrAbort();

    if ($_POST['action'] === 'save') {
        $catId = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
        $desc = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (!empty($name)) {
            $data = ['name' => $name, 'slug' => $slug, 'description' => $desc, 'sort_order' => $sortOrder];
            if ($catId > 0) {
                Database::update('product_categories', $data, '`id` = :id', [':id' => $catId]);
                Audit::log('update_category', 'products', (string)$catId, "Updated product category '{$name}'");
                set_flash('success', "Category '{$name}' updated.");
            } else {
                $newId = Database::insert('product_categories', $data);
                Audit::log('create_category', 'products', (string)$newId, "Created product category '{$name}'");
                set_flash('success', "New category '{$name}' created.");
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $catId = (int)($_POST['id'] ?? 0);
        $countProds = (int)Database::fetchColumn("SELECT COUNT(*) FROM `products` WHERE `category_id` = :cid", [':cid' => $catId]);
        if ($countProds > 0) {
            set_flash('error', "Cannot delete category: contains {$countProds} assigned products.");
        } else {
            Database::delete('product_categories', '`id` = :id', [':id' => $catId]);
            Audit::log('delete_category', 'products', (string)$catId, "Deleted product category #{$catId}");
            set_flash('success', "Category deleted.");
        }
    }
    header('Location: ' . admin_url('product-categories.php'));
    exit;
}

$categories = Database::fetchAll("SELECT c.*, (SELECT COUNT(*) FROM `products` p WHERE p.category_id = c.id) as product_count FROM `product_categories` c ORDER BY c.sort_order ASC");

include __DIR__ . '/includes/header.php';
?>

<div style="display: grid; grid-template-columns: 1.2fr 2fr; gap: 28px;">
    <!-- Add New Category Form -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Add Category</h3>
        </div>
        <div class="admin-card-body">
            <form action="<?= admin_url('product-categories.php') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="0">

                <div class="form-row">
                    <label class="form-label-admin">Category Name <span style="color:red;">*</span></label>
                    <input type="text" name="name" class="form-control-admin" required placeholder="e.g. Industrial Valves">
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Slug (Auto if blank)</label>
                    <input type="text" name="slug" class="form-control-admin" placeholder="industrial-valves">
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Description</label>
                    <textarea name="description" class="form-control-admin" style="min-height: 80px;" placeholder="Brief scope of items in this category..."></textarea>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control-admin" value="0">
                </div>

                <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; justify-content: center;">
                    Save Category &rarr;
                </button>
            </form>
        </div>
    </div>

    <!-- Category Listing -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Existing Categories</h3>
            <a href="<?= admin_url('products.php') ?>" class="btn-admin btn-admin-outline" style="font-size: 0.8rem;">
                Back to Products &rarr;
            </a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><strong>#<?= $cat['sort_order'] ?></strong></td>
                        <td>
                            <strong><?= e($cat['name']) ?></strong><br>
                            <small style="color: var(--admin-text-muted);"><?= e($cat['description'] ?? '') ?></small>
                        </td>
                        <td><code><?= e($cat['slug']) ?></code></td>
                        <td>
                            <a href="<?= admin_url('products.php?category=' . $cat['id']) ?>" style="font-weight: 700; color: var(--admin-accent);">
                                <?= $cat['product_count'] ?> items
                            </a>
                        </td>
                        <td>
                            <?php if ($cat['product_count'] == 0): ?>
                            <form action="<?= admin_url('product-categories.php') ?>" method="POST" style="display: inline;">
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
