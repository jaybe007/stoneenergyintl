<?php
/**
 * STONE ENERGY INT'L LTD - Create / Edit Project
 */
$requiredPermission = 'projects.manage';
require_once __DIR__ . '/includes/auth-check.php';

$projectId = (int)($_GET['id'] ?? 0);
$isEditing = ($projectId > 0);
$project = $isEditing ? Database::fetchOne("SELECT * FROM `projects` WHERE `id` = :id", [':id' => $projectId]) : null;

if ($isEditing && !$project) {
    set_flash('error', 'Project record not found.');
    header('Location: ' . admin_url('projects.php'));
    exit;
}

$adminPageTitle = ($isEditing ? "Edit: " . $project['title'] : "Add New Project") . " | CMS";
$adminSection = "Project Portfolio";
$errorMessage = '';

// Handle gallery image delete action
if ($isEditing && isset($_GET['action']) && $_GET['action'] === 'delete_img') {
    $imgId = (int)($_GET['img_id'] ?? 0);
    if ($imgId > 0) {
        Database::execute("DELETE FROM `project_images` WHERE `id` = :id AND `project_id` = :pid", [
            ':id' => $imgId,
            ':pid' => $projectId
        ]);
        set_flash('success', 'Project gallery photo removed.');
        header('Location: ' . admin_url('project-edit.php?id=' . $projectId));
        exit;
    }
}

$categories = Database::fetchAll("SELECT * FROM `project_categories` ORDER BY `name` ASC");
$galleryImages = $isEditing ? Database::fetchAll("SELECT * FROM `project_images` WHERE `project_id` = :id ORDER BY `sort_order` ASC, `id` ASC", [':id' => $projectId]) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();

    $title       = trim($_POST['title'] ?? '');
    $slug        = trim($_POST['slug'] ?? '') ?: slugify($title);
    $categoryId  = (int)($_POST['category_id'] ?? 1);
    $location    = trim($_POST['location'] ?? '');
    $client      = trim($_POST['client'] ?? '') ?: '[ADD CLIENT NAME]';
    $description = trim($_POST['description'] ?? '');
    $scope       = trim($_POST['scope'] ?? '');
    $startDate   = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $compDate    = !empty($_POST['completion_date']) ? $_POST['completion_date'] : null;
    $status      = in_array($_POST['status'] ?? '', ['Upcoming', 'Ongoing', 'Completed'], true) ? $_POST['status'] : 'Upcoming';
    $pubStatus   = in_array($_POST['published_status'] ?? '', ['published', 'draft'], true) ? $_POST['published_status'] : 'published';
    $imagePath   = $project['featured_image'] ?? null;

    if (empty($title) || empty($location) || empty($description)) {
        $errorMessage = "Please enter project title, location, and description.";
    } else {
        // Handle Featured Image upload
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $uploadRes = Uploader::upload($_FILES['featured_image'], 'Project image for ' . $title);
            if ($uploadRes['success']) {
                $imagePath = $uploadRes['file_path'];
            } else {
                $errorMessage = "Image upload failed: " . $uploadRes['error'];
            }
        }

        if (empty($errorMessage)) {
            // Check slug
            $slugCheck = Database::fetchOne(
                "SELECT id FROM `projects` WHERE `slug` = :s AND `id` != :id",
                [':s' => $slug, ':id' => $projectId]
            );
            if ($slugCheck) {
                $slug .= '-' . time();
            }

            $data = [
                'title'            => $title,
                'slug'             => $slug,
                'category_id'      => $categoryId,
                'location'         => $location,
                'client'           => $client,
                'description'      => $description,
                'scope'            => $scope,
                'start_date'       => $startDate,
                'completion_date'  => $compDate,
                'status'           => $status,
                'featured_image'   => $imagePath,
                'published_status' => $pubStatus
            ];

            $targetProjectId = $projectId;
            if ($isEditing) {
                Database::update('projects', $data, '`id` = :id', [':id' => $projectId]);
                Audit::log('update_project', 'projects', (string)$projectId, "Updated project '{$title}'");
                set_flash('success', "Project '{$title}' updated successfully.");
            } else {
                $targetProjectId = (int)Database::insert('projects', $data);
                Audit::log('create_project', 'projects', (string)$targetProjectId, "Created project '{$title}'");
                set_flash('success', "New project '{$title}' created successfully.");
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
                        $gRes = Uploader::upload($singleFile, 'Project Gallery for ' . $title);
                        if ($gRes['success']) {
                            Database::insert('project_images', [
                                'project_id' => $targetProjectId,
                                'image_path' => $gRes['file_path'],
                                'caption'    => null,
                                'sort_order' => $i
                            ]);
                        }
                    }
                }
            }

            header('Location: ' . admin_url('projects.php'));
            exit;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><?= $isEditing ? 'Edit Project Entry' : 'Add New Project' ?></h3>
        <a href="<?= admin_url('projects.php') ?>" class="btn-admin btn-admin-outline">
            &larr; Back to Portfolio
        </a>
    </div>
    <div class="admin-card-body">
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <span><?= e($errorMessage) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= admin_url('project-edit.php' . ($isEditing ? '?id=' . $projectId : '')) ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 28px;">
                <div>
                    <div class="form-row">
                        <label class="form-label-admin">Project Title <span style="color:red;">*</span></label>
                        <input type="text" name="title" class="form-control-admin" required value="<?= e($project['title'] ?? '') ?>" placeholder="e.g. Commercial Office Complex Renovation & Facility Upgrade">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-row">
                            <label class="form-label-admin">Project Location <span style="color:red;">*</span></label>
                            <input type="text" name="location" class="form-control-admin" required value="<?= e($project['location'] ?? '') ?>" placeholder="e.g. Ibadan, Oyo State">
                        </div>
                        <div class="form-row">
                            <label class="form-label-admin">Client Organization</label>
                            <input type="text" name="client" class="form-control-admin" value="<?= e($project['client'] ?? '[ADD CLIENT NAME]') ?>" placeholder="[ADD CLIENT NAME]">
                            <span style="font-size: 0.72rem; color: var(--admin-text-muted);">Leave placeholder or enter client legal name</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">URL Slug (Auto if blank)</label>
                        <input type="text" name="slug" class="form-control-admin" value="<?= e($project['slug'] ?? '') ?>" placeholder="custom-slug">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Project Description &amp; Technical Execution <span style="color:red;">*</span></label>
                        <textarea name="description" class="form-control-admin" style="min-height: 180px;" required placeholder="<p>Detailed project narrative, background, and delivery notes...</p>"><?= e($project['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Scope of Work Deliverables (Separated with ||)</label>
                        <textarea name="scope" class="form-control-admin" style="min-height: 90px;" placeholder="Civil masonry||Structural reinforcement||Electrical upgrade..."><?= e($project['scope'] ?? '') ?></textarea>
                        <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Separate each deliverable with: <code>Item 1||Item 2||Item 3</code></span>
                    </div>
                </div>

                <div>
                    <div class="form-row">
                        <label class="form-label-admin">Sector Category</label>
                        <select name="category_id" class="form-control-admin" required>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($project['category_id'] ?? 1) == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Execution Status</label>
                        <select name="status" class="form-control-admin">
                            <option value="Upcoming" <?= (($project['status'] ?? '') === 'Upcoming') ? 'selected' : '' ?>>Upcoming Contract</option>
                            <option value="Ongoing" <?= (($project['status'] ?? 'Ongoing') === 'Ongoing') ? 'selected' : '' ?>>Ongoing Execution</option>
                            <option value="Completed" <?= (($project['status'] ?? '') === 'Completed') ? 'selected' : '' ?>>Completed Project</option>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-row">
                            <label class="form-label-admin">Start Date</label>
                            <input type="date" name="start_date" class="form-control-admin" value="<?= e($project['start_date'] ?? '') ?>">
                        </div>
                        <div class="form-row">
                            <label class="form-label-admin">Completion Date</label>
                            <input type="date" name="completion_date" class="form-control-admin" value="<?= e($project['completion_date'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Visibility</label>
                        <select name="published_status" class="form-control-admin">
                            <option value="published" <?= (($project['published_status'] ?? 'published') === 'published') ? 'selected' : '' ?>>Published</option>
                            <option value="draft" <?= (($project['published_status'] ?? '') === 'draft') ? 'selected' : '' ?>>Draft / Hidden</option>
                        </select>
                    </div>

                    <!-- Image Upload -->
                    <div class="form-row" style="margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--admin-border);">
                        <label class="form-label-admin">Featured Project Image</label>
                        <input type="file" name="featured_image" class="form-control-admin" accept=".jpg,.jpeg,.png,.webp" data-preview-target="previewProjThumb">
                        <span style="font-size: 0.75rem; color: var(--admin-text-muted);">JPG, PNG, or WEBP up to 10MB</span>
                        
                        <div style="margin-top: 12px; height: 140px; border: 1px solid var(--admin-border); border-radius: 6px; overflow: hidden; background: #000; display: flex; align-items: center; justify-content: center;">
                            <img id="previewProjThumb" src="<?= upload_url($project['featured_image'] ?? null) ?>" alt="Preview" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                        </div>
                    </div>

                    <!-- Project Gallery Upload -->
                    <div class="form-row" style="margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--admin-border);">
                        <label class="form-label-admin">Additional Gallery Photos</label>
                        <input type="file" name="gallery_images[]" class="form-control-admin" accept=".jpg,.jpeg,.png,.webp" multiple>
                        <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Select multiple project site photos to append to gallery.</span>

                        <?php if (!empty($galleryImages)): ?>
                            <div style="margin-top: 14px;">
                                <label style="font-size: 0.78rem; font-weight: 600; color: var(--admin-text-muted); display: block; margin-bottom: 6px;">Site Gallery (<?= count($galleryImages) ?> photos):</label>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                                    <?php foreach ($galleryImages as $gImg): ?>
                                    <div style="position: relative; border: 1px solid var(--admin-border); border-radius: 4px; overflow: hidden; height: 70px; background: #000;">
                                        <img src="<?= upload_url($gImg['image_path']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <a href="<?= admin_url('project-edit.php?id=' . $projectId . '&action=delete_img&img_id=' . $gImg['id']) ?>" 
                                           onclick="return confirm('Delete this gallery photo?');" 
                                           style="position: absolute; top: 2px; right: 2px; background: rgba(220, 38, 38, 0.85); color: #fff; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; text-decoration: none; font-weight: bold;" title="Delete photo">
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
                <a href="<?= admin_url('projects.php') ?>" class="btn-admin btn-admin-outline">Cancel</a>
                <button type="submit" class="btn-admin btn-admin-primary">
                    <?= $isEditing ? 'Save Project Details' : 'Publish Project' ?> &rarr;
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
