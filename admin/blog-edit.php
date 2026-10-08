<?php
/**
 * STONE ENERGY INT'L LTD - Create / Edit Blog Post
 */
$requiredPermission = 'blog.manage';
require_once __DIR__ . '/includes/auth-check.php';

$postId = (int)($_GET['id'] ?? 0);
$isEditing = ($postId > 0);
$post = $isEditing ? Database::fetchOne("SELECT * FROM `blog_posts` WHERE `id` = :id", [':id' => $postId]) : null;

if ($isEditing && !$post) {
    set_flash('error', 'Post record not found.');
    header('Location: ' . admin_url('blog.php'));
    exit;
}

$adminPageTitle = ($isEditing ? "Edit: " . $post['title'] : "Write Article") . " | CMS";
$adminSection = "Articles & News";
$errorMessage = '';

$categories = Database::fetchAll("SELECT * FROM `blog_categories` ORDER BY `name` ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();

    $title       = trim($_POST['title'] ?? '');
    $slug        = trim($_POST['slug'] ?? '') ?: slugify($title);
    $categoryId  = (int)($_POST['category_id'] ?? 1);
    $excerpt     = trim($_POST['excerpt'] ?? '');
    $content     = trim($_POST['content'] ?? '');
    $tags        = trim($_POST['tags'] ?? '');
    $seoTitle    = trim($_POST['seo_title'] ?? '');
    $seoDesc     = trim($_POST['seo_description'] ?? '');
    $status      = in_array($_POST['status'] ?? '', ['Draft', 'Published', 'Archived'], true) ? $_POST['status'] : 'Published';
    $pubDate     = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s');
    $imagePath   = $post['featured_image'] ?? null;

    if (empty($title) || empty($excerpt) || empty($content)) {
        $errorMessage = "Please enter article title, excerpt, and full content.";
    } else {
        // Handle Featured Image upload
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $uploadRes = Uploader::upload($_FILES['featured_image'], 'Article image for ' . $title);
            if ($uploadRes['success']) {
                $imagePath = $uploadRes['file_path'];
            } else {
                $errorMessage = "Image upload failed: " . $uploadRes['error'];
            }
        }

        if (empty($errorMessage)) {
            // Check slug collision
            $slugCheck = Database::fetchOne(
                "SELECT id FROM `blog_posts` WHERE `slug` = :s AND `id` != :id",
                [':s' => $slug, ':id' => $postId]
            );
            if ($slugCheck) {
                $slug .= '-' . time();
            }

            $data = [
                'title'           => $title,
                'slug'            => $slug,
                'category_id'     => $categoryId,
                'author_id'       => Auth::id() ?: 1,
                'excerpt'         => $excerpt,
                'content'         => $content,
                'tags'            => $tags ?: null,
                'seo_title'       => $seoTitle ?: null,
                'seo_description' => $seoDesc ?: null,
                'status'          => $status,
                'published_at'    => $pubDate,
                'featured_image'  => $imagePath
            ];

            if ($isEditing) {
                Database::update('blog_posts', $data, '`id` = :id', [':id' => $postId]);
                Audit::log('update_blog_post', 'blog', (string)$postId, "Updated blog post '{$title}'");
                set_flash('success', "Article '{$title}' updated successfully.");
            } else {
                $newId = Database::insert('blog_posts', $data);
                Audit::log('create_blog_post', 'blog', (string)$newId, "Created blog post '{$title}'");
                set_flash('success', "New article '{$title}' published.");
            }

            header('Location: ' . admin_url('blog.php'));
            exit;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><?= $isEditing ? 'Edit Article' : 'Write New Article' ?></h3>
        <a href="<?= admin_url('blog.php') ?>" class="btn-admin btn-admin-outline">
            &larr; Back to Articles
        </a>
    </div>
    <div class="admin-card-body">
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <span><?= e($errorMessage) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= admin_url('blog-edit.php' . ($isEditing ? '?id=' . $postId : '')) ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 28px;">
                <div>
                    <div class="form-row">
                        <label class="form-label-admin">Article Title <span style="color:red;">*</span></label>
                        <input type="text" name="title" class="form-control-admin" required value="<?= e($post['title'] ?? '') ?>" placeholder="e.g. Key Considerations When Selecting a General Contractor in Ibadan">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">URL Slug (Auto if blank)</label>
                        <input type="text" name="slug" class="form-control-admin" value="<?= e($post['slug'] ?? '') ?>" placeholder="custom-slug">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Article Excerpt (Summary for cards) <span style="color:red;">*</span></label>
                        <textarea name="excerpt" class="form-control-admin" style="min-height: 80px;" required placeholder="Brief introductory summary..."><?= e($post['excerpt'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Full HTML Content <span style="color:red;">*</span></label>
                        <textarea name="content" class="form-control-admin" style="min-height: 260px;" required placeholder="<p>Full article body with paragraphs and subheadings...</p>"><?= e($post['content'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Article Tags (Comma separated)</label>
                        <input type="text" name="tags" class="form-control-admin" value="<?= e($post['tags'] ?? '') ?>" placeholder="Construction, Contracting, Ibadan, Healthcare">
                    </div>
                </div>

                <div>
                    <div class="form-row">
                        <label class="form-label-admin">Article Category</label>
                        <select name="category_id" class="form-control-admin" required>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($post['category_id'] ?? 1) == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Publishing Status</label>
                        <select name="status" class="form-control-admin">
                            <option value="Published" <?= (($post['status'] ?? 'Published') === 'Published') ? 'selected' : '' ?>>Published / Live</option>
                            <option value="Draft" <?= (($post['status'] ?? '') === 'Draft') ? 'selected' : '' ?>>Draft / Offline</option>
                            <option value="Archived" <?= (($post['status'] ?? '') === 'Archived') ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Publication Date</label>
                        <input type="datetime-local" name="published_at" class="form-control-admin" value="<?= !empty($post['published_at']) ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : date('Y-m-d\TH:i') ?>">
                    </div>

                    <!-- Image Upload -->
                    <div class="form-row" style="margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--admin-border);">
                        <label class="form-label-admin">Featured Article Image</label>
                        <input type="file" name="featured_image" class="form-control-admin" accept=".jpg,.jpeg,.png,.webp" data-preview-target="previewBlogThumb">
                        <span style="font-size: 0.75rem; color: var(--admin-text-muted);">JPG, PNG, or WEBP up to 10MB</span>
                        
                        <div style="margin-top: 12px; height: 130px; border: 1px solid var(--admin-border); border-radius: 6px; overflow: hidden; background: #f8fafc; display: flex; align-items: center; justify-content: center;">
                            <img id="previewBlogThumb" src="<?= upload_url($post['featured_image'] ?? null) ?>" alt="Preview" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                        </div>
                    </div>

                    <!-- SEO Fields -->
                    <div class="form-row" style="margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--admin-border);">
                        <label class="form-label-admin">SEO Meta Title</label>
                        <input type="text" name="seo_title" class="form-control-admin" value="<?= e($post['seo_title'] ?? '') ?>" placeholder="SEO Title">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">SEO Meta Description</label>
                        <textarea name="seo_description" class="form-control-admin" style="min-height: 70px;" placeholder="Search snippet..."><?= e($post['seo_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--admin-border); display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?= admin_url('blog.php') ?>" class="btn-admin btn-admin-outline">Cancel</a>
                <button type="submit" class="btn-admin btn-admin-primary">
                    <?= $isEditing ? 'Save Article' : 'Publish Article' ?> &rarr;
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
