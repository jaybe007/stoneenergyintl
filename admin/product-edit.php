<?php
/**
 * STONE ENERGY INT'L LTD - Create / Edit Product
 */
$requiredPermission = 'products.manage';
require_once __DIR__ . '/includes/auth-check.php';

$productId = (int)($_GET['id'] ?? 0);
$isEditing = ($productId > 0);
$product = $isEditing ? Database::fetchOne("SELECT * FROM `products` WHERE `id` = :id", [':id' => $productId]) : null;

if ($isEditing && !$product) {
    set_flash('error', 'Product record not found.');
    header('Location: ' . admin_url('products.php'));
    exit;
}

$adminPageTitle = ($isEditing ? "Edit: " . $product['name'] : "Add New Product") . " | CMS";
$adminSection = "Product Catalogue";
$errorMessage = '';

// Handle gallery image delete action
if ($isEditing && isset($_GET['action']) && $_GET['action'] === 'delete_img') {
    $imgId = (int)($_GET['img_id'] ?? 0);
    if ($imgId > 0) {
        Database::execute("DELETE FROM `product_images` WHERE `id` = :id AND `product_id` = :pid", [
            ':id' => $imgId,
            ':pid' => $productId
        ]);
        set_flash('success', 'Gallery image removed.');
        header('Location: ' . admin_url('product-edit.php?id=' . $productId));
        exit;
    }
}

$categories = Database::fetchAll("SELECT * FROM `product_categories` ORDER BY `sort_order` ASC");
$galleryImages = $isEditing ? Database::fetchAll("SELECT * FROM `product_images` WHERE `product_id` = :id ORDER BY `sort_order` ASC, `id` ASC", [':id' => $productId]) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();

    $name          = trim($_POST['name'] ?? '');
    $slug          = trim($_POST['slug'] ?? '') ?: slugify($name);
    $sku           = trim($_POST['sku'] ?? '');
    $categoryId    = (int)($_POST['category_id'] ?? 1);
    $shortDesc     = trim($_POST['short_description'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $specs         = trim($_POST['specifications'] ?? '');
    $brand         = trim($_POST['brand'] ?? '');
    $manufacturer  = trim($_POST['manufacturer'] ?? '');
    $availability  = in_array($_POST['availability'] ?? '', ['in_stock', 'available_on_order', 'procured_on_demand'], true) ? $_POST['availability'] : 'available_on_order';
    $featured      = !empty($_POST['featured']) ? 1 : 0;
    $status        = in_array($_POST['status'] ?? '', ['published', 'draft', 'archived'], true) ? $_POST['status'] : 'published';
    $imagePath     = $product['image'] ?? null;
    $datasheetPath = $product['datasheet'] ?? null;

    if (!empty($_POST['remove_datasheet'])) {
        $datasheetPath = null;
    }

    if (empty($name) || empty($shortDesc)) {
        $errorMessage = "Please enter product name and short description.";
    } else {
        // Handle primary image upload if provided
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $uploadRes = Uploader::upload($_FILES['product_image'], 'Product image for ' . $name);
            if ($uploadRes['success']) {
                $imagePath = $uploadRes['file_path'];
            } else {
                $errorMessage = "Image upload failed: " . $uploadRes['error'];
            }
        }

        // Handle datasheet document upload (PDF)
        if (empty($errorMessage) && isset($_FILES['product_datasheet']) && $_FILES['product_datasheet']['error'] === UPLOAD_ERR_OK) {
            $uploadDs = Uploader::upload($_FILES['product_datasheet'], 'Datasheet for ' . $name);
            if ($uploadDs['success']) {
                $datasheetPath = $uploadDs['file_path'];
            } else {
                $errorMessage = "Datasheet upload failed: " . $uploadDs['error'];
            }
        }

        if (empty($errorMessage)) {
            // Slug collision check
            $slugCheck = Database::fetchOne(
                "SELECT id FROM `products` WHERE `slug` = :s AND `id` != :id",
                [':s' => $slug, ':id' => $productId]
            );
            if ($slugCheck) {
                $slug .= '-' . time();
            }

            $data = [
                'name'              => $name,
                'slug'              => $slug,
                'sku'               => $sku ?: null,
                'category_id'       => $categoryId,
                'short_description' => $shortDesc,
                'description'       => $description,
                'specifications'    => $specs ?: null,
                'brand'             => $brand ?: null,
                'manufacturer'      => $manufacturer ?: null,
                'image'             => $imagePath,
                'datasheet'         => $datasheetPath,
                'availability'      => $availability,
                'featured'          => $featured,
                'status'            => $status
            ];

            $targetProductId = $productId;
            if ($isEditing) {
                Database::update('products', $data, '`id` = :id', [':id' => $productId]);
                Audit::log('update_product', 'products', (string)$productId, "Updated product '{$name}'");
                set_flash('success', "Product '{$name}' updated successfully.");
            } else {
                $targetProductId = (int)Database::insert('products', $data);
                Audit::log('create_product', 'products', (string)$targetProductId, "Created product '{$name}'");
                set_flash('success', "Product '{$name}' created successfully.");
            }

            // Handle multi-image gallery upload
            if (isset($_FILES['gallery_images']) && is_array($_FILES['gallery_images']['name'])) {
                $fCount = count($_FILES['gallery_images']['name']);
                for ($i = 0; $i < $fCount; $i++) {
                    if ($_FILES['gallery_images']['error'][$i] === UPLOAD_ERR_OK) {
                        $singleFile = [
                            'name'     => $_FILES['gallery_images']['name'][$i],
                            'type'     => $_FILES['gallery_images']['type'][$i],
                            'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                            'error'    => $_FILES['gallery_images']['error'][$i],
                            'size'     => $_FILES['gallery_images']['size'][$i]
                        ];
                        $gRes = Uploader::upload($singleFile, 'Gallery for ' . $name);
                        if ($gRes['success']) {
                            Database::insert('product_images', [
                                'product_id' => $targetProductId,
                                'image_path' => $gRes['file_path'],
                                'is_primary' => 0,
                                'sort_order' => $i
                            ]);
                        }
                    }
                }
            }

            header('Location: ' . admin_url('products.php'));
            exit;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><?= $isEditing ? 'Edit Product Item' : 'Add New Product' ?></h3>
        <a href="<?= admin_url('products.php') ?>" class="btn-admin btn-admin-outline">
            &larr; Back to Catalog
        </a>
    </div>
    <div class="admin-card-body">
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <span><?= e($errorMessage) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= admin_url('product-edit.php' . ($isEditing ? '?id=' . $productId : '')) ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 28px;">
                <!-- Left Details -->
                <div>
                    <div class="form-row">
                        <label class="form-label-admin">Product Name <span style="color:red;">*</span></label>
                        <input type="text" name="name" class="form-control-admin" required value="<?= e($product['name'] ?? '') ?>" placeholder="e.g. Forged Carbon Steel Flanged Ball Valve">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-row">
                            <label class="form-label-admin">Item SKU Code</label>
                            <input type="text" name="sku" class="form-control-admin" value="<?= e($product['sku'] ?? '') ?>" placeholder="e.g. SEI-OG-010">
                        </div>
                        <div class="form-row">
                            <label class="form-label-admin">URL Slug (Auto if blank)</label>
                            <input type="text" name="slug" class="form-control-admin" value="<?= e($product['slug'] ?? '') ?>" placeholder="custom-slug">
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Short Summary (Catalogue cards) <span style="color:red;">*</span></label>
                        <textarea name="short_description" class="form-control-admin" style="min-height: 80px;" required placeholder="Brief procurement summary..."><?= e($product['short_description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Full HTML Description</label>
                        <textarea name="description" class="form-control-admin" style="min-height: 180px;" placeholder="<p>Detailed material specifications, operational capabilities...</p>"><?= e($product['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Technical Specifications (Separated with ||)</label>
                        <textarea name="specifications" class="form-control-admin" style="min-height: 100px;" placeholder="Material: ASTM Carbon Steel||Pressure: ANSI 300#||Size: 2 Inch to 12 Inch..."><?= e($product['specifications'] ?? '') ?></textarea>
                        <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Format: <code>Property: Value||Property 2: Value 2</code></span>
                    </div>
                </div>

                <!-- Right Sidebar Options -->
                <div>
                    <div class="form-row">
                        <label class="form-label-admin">Product Category</label>
                        <select name="category_id" class="form-control-admin" required>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($product['category_id'] ?? 1) == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Availability</label>
                        <select name="availability" class="form-control-admin">
                            <option value="available_on_order" <?= (($product['availability'] ?? '') === 'available_on_order') ? 'selected' : '' ?>>Available on Order</option>
                            <option value="in_stock" <?= (($product['availability'] ?? '') === 'in_stock') ? 'selected' : '' ?>>In Stock / Immediate Dispatch</option>
                            <option value="procured_on_demand" <?= (($product['availability'] ?? '') === 'procured_on_demand') ? 'selected' : '' ?>>Procured on Demand / Custom Spec</option>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-row">
                            <label class="form-label-admin">Brand</label>
                            <input type="text" name="brand" class="form-control-admin" value="<?= e($product['brand'] ?? '') ?>" placeholder="Brand">
                        </div>
                        <div class="form-row">
                            <label class="form-label-admin">Manufacturer</label>
                            <input type="text" name="manufacturer" class="form-control-admin" value="<?= e($product['manufacturer'] ?? '') ?>" placeholder="Manufacturer">
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Featured in Homepage</label>
                        <select name="featured" class="form-control-admin">
                            <option value="0" <?= empty($product['featured']) ? 'selected' : '' ?>>No</option>
                            <option value="1" <?= !empty($product['featured']) ? 'selected' : '' ?>>Yes &mdash; Show on Homepage</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Publishing Status</label>
                        <select name="status" class="form-control-admin">
                            <option value="published" <?= (($product['status'] ?? 'published') === 'published') ? 'selected' : '' ?>>Published / Visible</option>
                            <option value="draft" <?= (($product['status'] ?? '') === 'draft') ? 'selected' : '' ?>>Draft / Hidden</option>
                            <option value="archived" <?= (($product['status'] ?? '') === 'archived') ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>

                    <!-- Image Upload -->
                    <div class="form-row" style="margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--admin-border);">
                        <label class="form-label-admin">Primary Product Image</label>
                        <input type="file" name="product_image" class="form-control-admin" accept=".jpg,.jpeg,.png,.webp" data-preview-target="previewProductThumb">
                        <span style="font-size: 0.75rem; color: var(--admin-text-muted);">JPG, PNG, or WEBP up to 10MB</span>
                        
                        <div style="margin-top: 12px; height: 140px; border: 1px solid var(--admin-border); border-radius: 6px; overflow: hidden; background: #f8fafc; display: flex; align-items: center; justify-content: center;">
                            <img id="previewProductThumb" src="<?= upload_url($product['image'] ?? null) ?>" alt="Preview" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                        </div>
                    </div>

                    <!-- Technical Datasheet Upload -->
                    <div class="form-row" style="margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--admin-border);">
                        <label class="form-label-admin">Technical Datasheet (PDF)</label>
                        <input type="file" name="product_datasheet" class="form-control-admin" accept=".pdf">
                        <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Upload PDF manufacturer or mill specification sheet.</span>

                        <?php if (!empty($product['datasheet'])): ?>
                            <div style="margin-top: 10px; padding: 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; font-size: 0.82rem;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <a href="<?= upload_url($product['datasheet']) ?>" target="_blank" style="color: #1d4ed8; font-weight: 600;">
                                        View Current Datasheet &rarr;
                                    </a>
                                    <label style="margin: 0; font-size: 0.78rem; color: #dc2626; cursor: pointer;">
                                        <input type="checkbox" name="remove_datasheet" value="1"> Remove
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Product Gallery Upload -->
                    <div class="form-row" style="margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--admin-border);">
                        <label class="form-label-admin">Additional Gallery Photos</label>
                        <input type="file" name="gallery_images[]" class="form-control-admin" accept=".jpg,.jpeg,.png,.webp" multiple>
                        <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Select multiple photos to append to product gallery.</span>

                        <?php if (!empty($galleryImages)): ?>
                            <div style="margin-top: 14px;">
                                <label style="font-size: 0.78rem; font-weight: 600; color: var(--admin-text-muted); display: block; margin-bottom: 6px;">Current Gallery (<?= count($galleryImages) ?> images):</label>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                                    <?php foreach ($galleryImages as $gImg): ?>
                                    <div style="position: relative; border: 1px solid var(--admin-border); border-radius: 4px; overflow: hidden; height: 70px; background: #fff;">
                                        <img src="<?= upload_url($gImg['image_path']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <a href="<?= admin_url('product-edit.php?id=' . $productId . '&action=delete_img&img_id=' . $gImg['id']) ?>" 
                                           onclick="return confirm('Delete this gallery photo?');" 
                                           style="position: absolute; top: 2px; right: 2px; background: rgba(220, 38, 38, 0.85); color: #fff; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; text-decoration: none; font-weight: bold;" title="Delete image">
                                            &times;
                                        </a>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--admin-border); display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?= admin_url('products.php') ?>" class="btn-admin btn-admin-outline">Cancel</a>
                <button type="submit" class="btn-admin btn-admin-primary">
                    <?= $isEditing ? 'Save Product Details' : 'Add Product to Catalog' ?> &rarr;
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
