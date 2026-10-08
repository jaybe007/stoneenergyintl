<?php
/**
 * STONE ENERGY INT'L LTD - Create / Edit Service
 */
$requiredPermission = 'services.manage';
require_once __DIR__ . '/includes/auth-check.php';

$serviceId = (int)($_GET['id'] ?? 0);
$isEditing = ($serviceId > 0);
$service = $isEditing ? Database::fetchOne("SELECT * FROM `services` WHERE `id` = :id", [':id' => $serviceId]) : null;

if ($isEditing && !$service) {
    set_flash('error', 'Service record not found.');
    header('Location: ' . admin_url('services.php'));
    exit;
}

$adminPageTitle = ($isEditing ? "Edit Service: " . $service['title'] : "Create New Service") . " | CMS";
$adminSection = "Services Directory";
$errorMessage = '';

$categories = Database::fetchAll("SELECT * FROM `service_categories` ORDER BY `name` ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();

    $title       = trim($_POST['title'] ?? '');
    $slug        = trim($_POST['slug'] ?? '') ?: slugify($title);
    $categoryId  = (int)($_POST['category_id'] ?? 1);
    $icon        = trim($_POST['icon'] ?? 'wrench');
    $shortDesc   = trim($_POST['short_description'] ?? '');
    $fullDesc    = trim($_POST['full_description'] ?? '');
    $features    = trim($_POST['features'] ?? '');
    $sortOrder   = (int)($_POST['sort_order'] ?? 0);
    $status      = in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active';
    $metaTitle   = trim($_POST['meta_title'] ?? '');
    $metaDesc    = trim($_POST['meta_description'] ?? '');

    if (empty($title) || empty($shortDesc) || empty($fullDesc)) {
        $errorMessage = "Please enter service title, short description, and full description.";
    } else {
        // Check unique slug
        $slugCheck = Database::fetchOne(
            "SELECT id FROM `services` WHERE `slug` = :s AND `id` != :id",
            [':s' => $slug, ':id' => $serviceId]
        );
        if ($slugCheck) {
            $slug .= '-' . time();
        }

        $data = [
            'title'             => $title,
            'slug'              => $slug,
            'category_id'       => $categoryId,
            'icon'              => $icon,
            'short_description' => $shortDesc,
            'full_description'  => $fullDesc,
            'features'          => $features,
            'sort_order'        => $sortOrder,
            'status'            => $status,
            'meta_title'        => $metaTitle ?: null,
            'meta_description'  => $metaDesc ?: null
        ];

        if ($isEditing) {
            Database::update('services', $data, '`id` = :id', [':id' => $serviceId]);
            Audit::log('update_service', 'services', (string)$serviceId, "Updated service '{$title}'");
            set_flash('success', "Service '{$title}' updated successfully.");
        } else {
            $newId = Database::insert('services', $data);
            Audit::log('create_service', 'services', (string)$newId, "Created service '{$title}'");
            set_flash('success', "New service '{$title}' created successfully.");
        }

        header('Location: ' . admin_url('services.php'));
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><?= $isEditing ? 'Edit Service' : 'Create New Service' ?></h3>
        <a href="<?= admin_url('services.php') ?>" class="btn-admin btn-admin-outline">
            &larr; Back to Services List
        </a>
    </div>
    <div class="admin-card-body">
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <span><?= e($errorMessage) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= admin_url('service-edit.php' . ($isEditing ? '?id=' . $serviceId : '')) ?>" method="POST">
            <?= csrf_field() ?>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                <div>
                    <div class="form-row">
                        <label class="form-label-admin">Service Title <span style="color:red;">*</span></label>
                        <input type="text" name="title" class="form-control-admin" required value="<?= e($service['title'] ?? '') ?>" placeholder="e.g. Oil & Gas Supply">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">URL Slug (leave blank to auto-generate)</label>
                        <input type="text" name="slug" class="form-control-admin" value="<?= e($service['slug'] ?? '') ?>" placeholder="e.g. oil-and-gas-supply">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Short Summary (Preview cards) <span style="color:red;">*</span></label>
                        <textarea name="short_description" class="form-control-admin" style="min-height: 80px;" required placeholder="Brief 1-2 sentence overview..."><?= e($service['short_description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Full HTML Description &amp; Technical Capabilities <span style="color:red;">*</span></label>
                        <textarea name="full_description" class="form-control-admin" style="min-height: 220px;" required placeholder="<p>Full description and technical details...</p>"><?= e($service['full_description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Key Capabilities / Feature Bullets (Separated with ||)</label>
                        <textarea name="features" class="form-control-admin" style="min-height: 90px;" placeholder="Turnkey building construction||Structural renovations||Site supervision..."><?= e($service['features'] ?? '') ?></textarea>
                        <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Separate each bullet with double pipes: <code>Item 1||Item 2||Item 3</code></span>
                    </div>
                </div>

                <div>
                    <div class="form-row">
                        <label class="form-label-admin">Sector Category</label>
                        <select name="category_id" class="form-control-admin" required>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($service['category_id'] ?? 1) == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Icon Symbol</label>
                        <select name="icon" class="form-control-admin">
                            <option value="fuel" <?= (($service['icon'] ?? '') === 'fuel') ? 'selected' : '' ?>>Fuel / Energy</option>
                            <option value="building" <?= (($service['icon'] ?? '') === 'building') ? 'selected' : '' ?>>Building / Civil</option>
                            <option value="heart-pulse" <?= (($service['icon'] ?? '') === 'heart-pulse') ? 'selected' : '' ?>>Healthcare / Medical</option>
                            <option value="sprout" <?= (($service['icon'] ?? '') === 'sprout') ? 'selected' : '' ?>>Agro-Allied / Sprout</option>
                            <option value="cog" <?= (($service['icon'] ?? '') === 'cog') ? 'selected' : '' ?>>Processing / Machine Cog</option>
                            <option value="briefcase" <?= (($service['icon'] ?? '') === 'briefcase') ? 'selected' : '' ?>>General Contracting / Briefcase</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control-admin" value="<?= (int)($service['sort_order'] ?? 0) ?>">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Status</label>
                        <select name="status" class="form-control-admin">
                            <option value="active" <?= (($service['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active / Published</option>
                            <option value="inactive" <?= (($service['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive / Draft</option>
                        </select>
                    </div>

                    <div class="form-row" style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--admin-border);">
                        <label class="form-label-admin">SEO Meta Title</label>
                        <input type="text" name="meta_title" class="form-control-admin" value="<?= e($service['meta_title'] ?? '') ?>" placeholder="Page title tag">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">SEO Meta Description</label>
                        <textarea name="meta_description" class="form-control-admin" style="min-height: 80px;" placeholder="Search engine snippet..."><?= e($service['meta_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--admin-border); display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?= admin_url('services.php') ?>" class="btn-admin btn-admin-outline">Cancel</a>
                <button type="submit" class="btn-admin btn-admin-primary">
                    <?= $isEditing ? 'Save Service Changes' : 'Publish Service' ?> &rarr;
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
