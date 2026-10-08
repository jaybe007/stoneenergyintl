<?php
/**
 * STONE ENERGY INT'L LTD - Product Catalogue & Single Product View
 */
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$searchTerm = trim($_GET['search'] ?? '');

if (!empty($slug)) {
    // Single Product Detail
    $product = Database::fetchOne(
        "SELECT p.*, c.name as category_name, c.slug as category_slug 
         FROM `products` p 
         JOIN `product_categories` c ON p.category_id = c.id 
         WHERE p.slug = :s AND p.status = 'published' LIMIT 1",
        [':s' => $slug]
    );

    if (!$product) {
        http_response_code(404);
        include ROOT_PATH . '404.php';
        exit;
    }

    $pageTitle = $product['name'] . " | Product Specifications | STONE ENERGY INT'L LTD";
    $pageDescription = $product['short_description'];
    $gallery = Database::fetchAll("SELECT * FROM `product_images` WHERE `product_id` = :pid ORDER BY `sort_order` ASC", [':pid' => $product['id']]);

    include INCLUDES_PATH . 'header.php';
    ?>
    <div class="section-dark" style="padding: 50px 0; border-bottom: 3px solid var(--color-accent-500);">
        <div class="container">
            <div class="breadcrumbs" style="margin-bottom: 12px; color: #94a3b8;">
                <a href="<?= url('') ?>" style="color: #cbd5e1;">Home</a> &rarr; 
                <a href="<?= url('products.php') ?>" style="color: #cbd5e1;">Catalogue</a> &rarr; 
                <a href="<?= url('products.php?category=' . urlencode($product['category_slug'])) ?>" style="color: #cbd5e1;"><?= e($product['category_name']) ?></a> &rarr; 
                <span><?= e($product['name']) ?></span>
            </div>
            <h1 style="font-size: 2.4rem; color: #fff; margin-bottom: 8px;"><?= e($product['name']) ?></h1>
            <div style="display: flex; gap: 14px; align-items: center; font-size: 0.9rem; color: #cbd5e1;">
                <span>Category: <strong><?= e($product['category_name']) ?></strong></span>
                <span>&bull;</span>
                <span>SKU: <strong><?= e($product['sku'] ?? 'N/A') ?></strong></span>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: start;">
                <!-- Product Image Gallery -->
                <div>
                    <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); height: 380px;">
                        <img id="mainProductImg" src="<?= upload_url($product['image'], 'assets/images/placeholder.svg') ?>" alt="<?= e($product['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <?php if (!empty($gallery)): ?>
                    <div style="display: flex; gap: 10px; margin-top: 14px;">
                        <div style="width: 80px; height: 80px; border: 2px solid var(--color-accent-500); border-radius: var(--radius-sm); overflow: hidden; cursor: pointer;" onclick="document.getElementById('mainProductImg').src='<?= upload_url($product['image']) ?>'">
                            <img src="<?= upload_url($product['image']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <?php foreach ($gallery as $g): ?>
                        <div style="width: 80px; height: 80px; border: 1px solid var(--color-gray-300); border-radius: var(--radius-sm); overflow: hidden; cursor: pointer;" onclick="document.getElementById('mainProductImg').src='<?= upload_url($g['image_path']) ?>'">
                            <img src="<?= upload_url($g['image_path']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Product Specifications & Quote Trigger -->
                <div>
                    <div style="margin-bottom: 20px;">
                        <?php
                        $availLabel = match ($product['availability']) {
                            'in_stock' => 'In Stock / Ready for Dispatch',
                            'available_on_order' => 'Available on Order',
                            'procured_on_demand' => 'Procured on Demand / Specification',
                            default => 'Available on Order'
                        };
                        ?>
                        <span class="status-badge status-ongoing" style="position: static; font-size: 0.82rem; padding: 6px 14px;">
                            <?= e($availLabel) ?>
                        </span>
                    </div>

                    <h2 style="font-size: 1.8rem; margin-bottom: 16px;"><?= e($product['name']) ?></h2>
                    <p style="font-size: 1.05rem; color: var(--color-dark-600); line-height: 1.6; margin-bottom: 24px;">
                        <?= e($product['short_description']) ?>
                    </p>

                    <!-- Technical Specifications -->
                    <?php if (!empty($product['specifications'])): 
                        $specs = explode('||', $product['specifications']);
                    ?>
                    <div style="background: var(--color-gray-100); border-radius: var(--radius-md); padding: 24px; margin-bottom: 28px;">
                        <h4 style="font-size: 1.05rem; margin-bottom: 14px;">Technical Specifications</h4>
                        <table style="width: 100%; font-size: 0.88rem; border-collapse: collapse;">
                            <tbody>
                                <?php foreach ($specs as $s): 
                                    $parts = explode(':', $s, 2);
                                ?>
                                <tr style="border-bottom: 1px solid var(--color-gray-200);">
                                    <td style="padding: 8px 0; font-weight: 600; color: var(--color-dark-800); width: 40%;"><?= e(trim($parts[0] ?? '')) ?></td>
                                    <td style="padding: 8px 0; color: var(--color-dark-600);"><?= e(trim($parts[1] ?? '')) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <!-- Action: Request a Quote (Prices not shown) -->
                    <div style="background: #f8fafc; border: 1.5px solid var(--color-accent-400); border-radius: var(--radius-md); padding: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <span style="font-size: 0.88rem; color: var(--color-dark-600);">Pricing Structure:</span>
                            <span style="font-size: 1.15rem; font-weight: 700; color: var(--color-accent-600);">Custom Commercial Quotation</span>
                        </div>
                        <p style="font-size: 0.85rem; color: var(--color-dark-600); margin-bottom: 18px;">
                            We supply in commercial and project quantities with verified mill certificates and manufacturer documentation.
                        </p>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <a href="<?= url('quote.php?item=' . urlencode($product['name'] . ' (SKU: ' . ($product['sku'] ?? '') . ')')) ?>" class="btn btn-primary" style="flex-grow: 1;">
                                REQUEST A QUOTE FOR THIS PRODUCT
                            </a>
                            <button type="button" class="btn btn-navy" data-modal-target="quickRfqModal" data-quote-item="<?= e($product['name'] . ' (SKU: ' . ($product['sku'] ?? '') . ')') ?>">
                                Quick RFQ Modal
                            </button>
                        </div>

                        <?php if (!empty($product['datasheet'])): ?>
                        <div style="margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--color-gray-300);">
                            <a href="<?= upload_url($product['datasheet']) ?>" target="_blank" class="btn btn-outline-gold" style="width: 100%; justify-content: center; font-size: 0.88rem;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                DOWNLOAD TECHNICAL DATASHEET (PDF) &darr;
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($product['description'])): ?>
                    <div style="margin-top: 32px; font-size: 0.95rem; line-height: 1.7; color: var(--color-dark-700);">
                        <h4 style="font-size: 1.1rem; margin-bottom: 10px;">Product Overview</h4>
                        <?= $product['description'] ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php
    include INCLUDES_PATH . 'footer.php';
    exit;
}

// Product Listing Page
$pageTitle = "Product Catalogue & Procurement Supply | STONE ENERGY INT'L LTD";
$pageDescription = "Browse our multi-sector product catalogue across oil & gas, construction steel, hospital medical hardware, clinical consumables, and agroprocessing inputs.";

$categories = Database::fetchAll("SELECT * FROM `product_categories` ORDER BY `sort_order` ASC");

$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM `products` p 
        JOIN `product_categories` c ON p.category_id = c.id 
        WHERE p.status = 'published' ";
$params = [];

if (!empty($categorySlug)) {
    $sql .= "AND c.slug = :cslug ";
    $params[':cslug'] = $categorySlug;
}

if (!empty($searchTerm)) {
    $sql .= "AND (p.name LIKE :search OR p.short_description LIKE :search OR p.sku LIKE :search) ";
    $params[':search'] = "%{$searchTerm}%";
}

$sql .= "ORDER BY p.id DESC";
$products = Database::fetchAll($sql, $params);

include INCLUDES_PATH . 'header.php';
?>

<div class="section-dark" style="padding: 60px 0; border-bottom: 3px solid var(--color-accent-500);">
    <div class="container">
        <span class="section-tag section-tag-light">PROCUREMENT CATALOGUE</span>
        <h1 style="font-size: 2.8rem; color: #fff; margin-bottom: 12px;">Product Catalogue</h1>
        <p style="color: #cbd5e1; max-width: 720px; font-size: 1.1rem; margin-bottom: 0;">
            Procurement solutions across energy equipment, reinforcing steel, medical hospital hardware, clinical consumables, and agro-allied supplies.
        </p>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- Search & Filter Controls -->
        <div class="catalog-toolbar">
            <div class="category-pills">
                <a href="<?= url('products.php') ?>" class="pill-btn <?= empty($categorySlug) ? 'active' : '' ?>">
                    All Products
                </a>
                <?php foreach ($categories as $cat): ?>
                <a href="<?= url('products.php?category=' . urlencode($cat['slug'])) ?>" class="pill-btn <?= ($categorySlug === $cat['slug']) ? 'active' : '' ?>">
                    <?= e($cat['name']) ?>
                </a>
                <?php endforeach; ?>
            </div>

            <form action="<?= url('products.php') ?>" method="GET" style="display: flex; gap: 8px;">
                <?php if (!empty($categorySlug)): ?>
                    <input type="hidden" name="category" value="<?= e($categorySlug) ?>">
                <?php endif; ?>
                <input type="text" name="search" class="form-control" placeholder="Search SKU, name..." value="<?= e($searchTerm) ?>" style="padding: 8px 14px; font-size: 0.88rem; width: 220px;">
                <button type="submit" class="btn btn-navy btn-sm">Filter</button>
            </form>
        </div>

        <!-- Products Grid -->
        <?php if (empty($products)): ?>
        <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); padding: 60px 20px; text-align: center;">
            <h3 style="font-size: 1.3rem; margin-bottom: 8px;">No Products Found</h3>
            <p style="color: var(--color-dark-600); margin-bottom: 20px;">No catalogue items match your current filter criteria.</p>
            <a href="<?= url('products.php') ?>" class="btn btn-outline-gold btn-sm">Reset Filters</a>
        </div>
        <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $prod): ?>
            <div class="product-card filterable-item" data-category="<?= e($prod['category_slug']) ?>">
                <div class="product-thumb">
                    <img src="<?= upload_url($prod['image'], 'assets/images/placeholder.svg') ?>" alt="<?= e($prod['name']) ?>" loading="lazy">
                    <span class="product-badge"><?= e($prod['category_name']) ?></span>
                </div>
                <div class="product-body">
                    <div class="product-sku">SKU: <?= e($prod['sku'] ?? 'N/A') ?></div>
                    <h3><?= e($prod['name']) ?></h3>
                    <p><?= e($prod['short_description']) ?></p>
                    <div class="product-footer">
                        <a href="<?= url('quote.php?item=' . urlencode($prod['name'] . ' (SKU: ' . ($prod['sku'] ?? '') . ')')) ?>" class="btn btn-primary btn-sm">
                            Request a Quote
                        </a>
                        <a href="<?= url('products.php?slug=' . urlencode($prod['slug'])) ?>" style="font-size: 0.82rem; font-weight: 600; color: var(--color-primary-900);">
                            Specs &rarr;
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include INCLUDES_PATH . 'footer.php'; ?>
