<?php
/**
 * STONE ENERGY INT'L LTD - Legal & Static Pages Management
 */
$requiredPermission = 'pages.manage';
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Pages & Legal CMS | STONE ENERGY INT'L LTD CMS";
$adminSection = "Pages & Legal";

$editSlug = trim($_GET['slug'] ?? '');
$editPage = !empty($editSlug) ? Database::fetchOne("SELECT * FROM `pages` WHERE `slug` = :s", [':s' => $editSlug]) : null;

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();
    $slug = trim($_POST['slug'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $metaTitle = trim($_POST['meta_title'] ?? '');
    $metaDesc = trim($_POST['meta_description'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['published', 'draft'], true) ? $_POST['status'] : 'published';

    if (!empty($slug) && !empty($title)) {
        Database::update('pages', [
            'title'            => $title,
            'content'          => $content,
            'meta_title'       => $metaTitle ?: null,
            'meta_description' => $metaDesc ?: null,
            'status'           => $status
        ], '`slug` = :s', [':s' => $slug]);

        Audit::log('update_page', 'pages', $slug, "Updated page content for '{$title}'");
        set_flash('success', "Page '{$title}' updated successfully.");
        header('Location: ' . admin_url('pages.php?slug=' . urlencode($slug)));
        exit;
    }
}

$allPages = Database::fetchAll("SELECT * FROM `pages` ORDER BY `id` ASC");

include __DIR__ . '/includes/header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 28px;">
    <!-- Pages Navigation List -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Static Pages</h3>
        </div>
        <div style="padding: 12px;">
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <?php foreach ($allPages as $pg): 
                    $isSelected = ($editSlug === $pg['slug']) || (empty($editSlug) && $pg === $allPages[0]);
                    if (empty($editSlug) && $pg === $allPages[0]) {
                        $editPage = $pg;
                    }
                ?>
                <a href="<?= admin_url('pages.php?slug=' . urlencode($pg['slug'])) ?>" class="btn-admin <?= $isSelected ? 'btn-admin-primary' : 'btn-admin-outline' ?>" style="justify-content: space-between; text-align: left;">
                    <span><?= e($pg['title']) ?></span>
                    <span style="font-size: 0.75rem; opacity: 0.8;"><?= e($pg['status']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Page Content Editor -->
    <?php if ($editPage): ?>
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Edit: <?= e($editPage['title']) ?></h3>
            <a href="<?= url($editPage['slug'] . '.php') ?>" target="_blank" class="btn-admin btn-admin-outline" style="font-size: 0.8rem;">
                View Public Page &rarr;
            </a>
        </div>
        <div class="admin-card-body">
            <form action="<?= admin_url('pages.php') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="slug" value="<?= e($editPage['slug']) ?>">

                <div class="form-row">
                    <label class="form-label-admin">Page Title</label>
                    <input type="text" name="title" class="form-control-admin" required value="<?= e($editPage['title']) ?>">
                </div>

                <div class="form-row">
                    <label class="form-label-admin">HTML Content</label>
                    <textarea name="content" class="form-control-admin" style="min-height: 280px;" required><?= e($editPage['content']) ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-row">
                        <label class="form-label-admin">SEO Meta Title</label>
                        <input type="text" name="meta_title" class="form-control-admin" value="<?= e($editPage['meta_title'] ?? '') ?>">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Visibility Status</label>
                        <select name="status" class="form-control-admin">
                            <option value="published" <?= ($editPage['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                            <option value="draft" <?= ($editPage['status'] === 'draft') ? 'selected' : '' ?>>Draft / Hidden</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">SEO Meta Description</label>
                    <textarea name="meta_description" class="form-control-admin" style="min-height: 70px;"><?= e($editPage['meta_description'] ?? '') ?></textarea>
                </div>

                <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--admin-border);">
                    <button type="submit" class="btn-admin btn-admin-primary">
                        Save Page Changes &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
