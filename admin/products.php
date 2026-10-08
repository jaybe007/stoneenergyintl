<?php
/**
 * STONE ENERGY INT'L LTD - Products Management
 */
$requiredPermission = 'products.manage';
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Products Management | STONE ENERGY INT'L LTD CMS";
$adminSection = "Product Catalogue";

$categoryFilter = (int)($_GET['category'] ?? 0);
$searchTerm = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    CSRF::validateOrAbort();
    $prodId = (int)($_POST['id'] ?? 0);
    if ($prodId > 0) {
        $p = Database::fetchOne("SELECT name FROM `products` WHERE `id` = :id", [':id' => $prodId]);
        Database::delete('products', '`id` = :id', [':id' => $prodId]);
        Audit::log('delete_product', 'products', (string)$prodId, "Deleted product '{$p['name']}'");
        set_flash('success', 'Product deleted successfully.');
    }
    header('Location: ' . admin_url('products.php?' . http_build_query($_GET)));
    exit;
}

// Build query
$whereClauses = ["1=1"];
$params = [];

if ($categoryFilter > 0) {
    $whereClauses[] = "p.category_id = :cid";
    $params[':cid'] = $categoryFilter;
}

if (!empty($searchTerm)) {
    $whereClauses[] = "(p.name LIKE :q OR p.sku LIKE :q OR p.short_description LIKE :q)";
    $params[':q'] = "%{$searchTerm}%";
}

$whereSql = implode(' AND ', $whereClauses);
$totalProducts = (int)Database::fetchColumn("SELECT COUNT(*) FROM `products` p WHERE {$whereSql}", $params);
$totalPages = ceil($totalProducts / $perPage);

$products = Database::fetchAll(
    "SELECT p.*, c.name as category_name 
     FROM `products` p 
     JOIN `product_categories` c ON p.category_id = c.id 
     WHERE {$whereSql} 
     ORDER BY p.id DESC LIMIT {$perPage} OFFSET {$offset}",
    $params
);

$categories = Database::fetchAll("SELECT * FROM `product_categories` ORDER BY `sort_order` ASC");

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>Product Catalog (<?= $totalProducts ?> Items)</h3>
        <div style="display: flex; gap: 10px;">
            <a href="<?= admin_url('product-categories.php') ?>" class="btn-admin btn-admin-outline">
                Manage Categories
            </a>
            <a href="<?= admin_url('product-edit.php') ?>" class="btn-admin btn-admin-primary">
                + Add New Product
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div style="padding: 16px 24px; border-bottom: 1px solid var(--admin-border); background: #f8fafc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <form action="<?= admin_url('products.php') ?>" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <select name="category" class="form-control-admin" style="width: 200px;">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($categoryFilter === (int)$cat['id']) ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="search" class="form-control-admin" placeholder="Search name, SKU..." value="<?= e($searchTerm) ?>" style="width: 220px;">
            <button type="submit" class="btn-admin btn-admin-primary">Filter</button>
            <?php if (!empty($searchTerm) || $categoryFilter > 0): ?>
                <a href="<?= admin_url('products.php') ?>" class="btn-admin btn-admin-outline">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Item / SKU</th>
                    <th>Category</th>
                    <th>Availability</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--admin-text-muted); padding: 40px;">
                        No catalogue products match your criteria.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <img src="<?= upload_url($p['image']) ?>" alt="" style="width: 44px; height: 44px; object-fit: cover; border-radius: 4px; border: 1px solid var(--admin-border);">
                            <div>
                                <strong><?= e($p['name']) ?></strong><br>
                                <code style="font-size: 0.76rem; color: var(--admin-accent);">SKU: <?= e($p['sku'] ?? 'N/A') ?></code>
                            </div>
                        </div>
                    </td>
                    <td><?= e($p['category_name']) ?></td>
                    <td>
                        <span style="font-size: 0.82rem; font-weight: 600;">
                            <?= ucwords(str_replace('_', ' ', $p['availability'])) ?>
                        </span>
                    </td>
                    <td>
                        <?= $p['featured'] ? '<span class="badge-status badge-approved">Yes</span>' : '<span style="color: var(--admin-text-muted);">No</span>' ?>
                    </td>
                    <td>
                        <span class="badge-status <?= ($p['status'] === 'published') ? 'badge-active' : 'badge-inactive' ?>">
                            <?= e($p['status']) ?>
                        </span>
                    </td>
                    <td><small><?= format_date($p['created_at']) ?></small></td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="<?= admin_url('product-edit.php?id=' . $p['id']) ?>" class="btn-admin btn-admin-outline btn-icon" title="Edit">
                                Edit
                            </a>
                            <a href="<?= url('products.php?slug=' . urlencode($p['slug'])) ?>" target="_blank" class="btn-admin btn-admin-outline btn-icon" title="View Public Page">
                                View
                            </a>
                            <form action="<?= admin_url('products.php') ?>" method="POST" style="display: inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-admin btn-admin-danger btn-icon" data-confirm-delete="Delete product '<?= e($p['name']) ?>'?" title="Delete">
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

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div style="padding: 16px 24px; border-top: 1px solid var(--admin-border); display: flex; justify-content: center; gap: 6px;">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="<?= admin_url('products.php?' . http_build_query(array_merge($_GET, ['page' => $p]))) ?>" class="btn-admin <?= ($page === $p) ? 'btn-admin-primary' : 'btn-admin-outline' ?>" style="padding: 4px 10px; font-size: 0.8rem;">
                <?= $p ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
