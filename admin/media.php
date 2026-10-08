<?php
/**
 * STONE ENERGY INT'L LTD - Media Library Management
 */
$requiredPermission = 'media.manage';
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Media Library | STONE ENERGY INT'L LTD CMS";
$adminSection = "Media Library";
$errorMessage = '';

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    CSRF::validateOrAbort();
    $altText = trim($_POST['alt_text'] ?? '');

    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = Uploader::upload($_FILES['media_file'], $altText);
        if ($uploadRes['success']) {
            set_flash('success', "Media file '{$uploadRes['filename']}' uploaded successfully.");
        } else {
            set_flash('error', "Upload failed: " . $uploadRes['error']);
        }
    } else {
        set_flash('error', "Please select a valid file to upload.");
    }
    header('Location: ' . admin_url('media.php'));
    exit;
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    CSRF::validateOrAbort();
    $mediaId = (int)($_POST['id'] ?? 0);
    if ($mediaId > 0) {
        Uploader::delete($mediaId);
        set_flash('success', 'Media asset removed successfully.');
    }
    header('Location: ' . admin_url('media.php'));
    exit;
}

$searchTerm = trim($_GET['search'] ?? '');
$sql = "SELECT m.*, u.username FROM `media` m LEFT JOIN `users` u ON m.uploaded_by = u.id ";
$params = [];

if (!empty($searchTerm)) {
    $sql .= "WHERE (m.original_filename LIKE :q OR m.alt_text LIKE :q) ";
    $params[':q'] = "%{$searchTerm}%";
}

$sql .= "ORDER BY m.id DESC";
$mediaFiles = Database::fetchAll($sql, $params);

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>Media Library (<?= count($mediaFiles) ?> Assets)</h3>
        <button type="button" class="btn-admin btn-admin-primary" onclick="document.getElementById('uploadModal').style.display='flex'">
            + Upload Media File
        </button>
    </div>

    <!-- Search Toolbar -->
    <div style="padding: 16px 24px; border-bottom: 1px solid var(--admin-border); background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
        <form action="<?= admin_url('media.php') ?>" method="GET" style="display: flex; gap: 8px;">
            <input type="text" name="search" class="form-control-admin" placeholder="Search filename..." value="<?= e($searchTerm) ?>" style="width: 240px;">
            <button type="submit" class="btn-admin btn-admin-primary">Filter</button>
            <?php if (!empty($searchTerm)): ?>
                <a href="<?= admin_url('media.php') ?>" class="btn-admin btn-admin-outline">Clear</a>
            <?php endif; ?>
        </form>
        <span style="font-size: 0.8rem; color: var(--admin-text-muted);">
            Permitted: JPG, PNG, WEBP, PDF (Max 10MB)
        </span>
    </div>

    <div class="admin-card-body">
        <?php if (empty($mediaFiles)): ?>
        <div style="text-align: center; padding: 50px 20px; color: var(--admin-text-muted);">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; color: var(--admin-border);"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
            <p>No media files uploaded yet.</p>
        </div>
        <?php else: ?>
        <div class="media-grid">
            <?php foreach ($mediaFiles as $m): 
                $isPdf = ($m['mime_type'] === 'application/pdf');
                $sizeKb = round($m['file_size'] / 1024, 1);
            ?>
            <div class="media-card">
                <div class="media-thumb">
                    <?php if ($isPdf): ?>
                        <div style="text-align: center; color: #dc2626;">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                            <div style="font-size: 0.72rem; font-weight: 700;">PDF Document</div>
                        </div>
                    <?php else: ?>
                        <img src="<?= upload_url($m['file_path']) ?>" alt="<?= e($m['alt_text']) ?>" loading="lazy">
                    <?php endif; ?>
                </div>
                <div class="media-info">
                    <div class="media-title" title="<?= e($m['original_filename']) ?>"><?= e($m['original_filename']) ?></div>
                    <div style="color: var(--admin-text-muted); font-size: 0.7rem; margin-top: 2px;">
                        <?= $sizeKb ?> KB &bull; <?= format_date($m['created_at']) ?>
                    </div>
                    <div style="margin-top: 8px; display: flex; gap: 4px; justify-content: space-between;">
                        <button type="button" class="btn-admin btn-admin-outline btn-icon" style="font-size: 0.7rem; padding: 3px 6px;" onclick="copyToClipboard('<?= e($m['file_path']) ?>', 'Path copied!')">
                            Copy Path
                        </button>
                        <a href="<?= upload_url($m['file_path']) ?>" target="_blank" class="btn-admin btn-admin-outline btn-icon" style="font-size: 0.7rem; padding: 3px 6px;">
                            View
                        </a>
                        <form action="<?= admin_url('media.php') ?>" method="POST" style="display: inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn-admin btn-admin-danger btn-icon" style="padding: 3px 6px; font-size: 0.7rem;" data-confirm-delete="Delete this file?" title="Delete">
                                &times;
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Upload Modal Window -->
<div id="uploadModal" style="display: none; position: fixed; inset: 0; background: rgba(10,25,47,0.7); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 10px; width: 100%; max-width: 480px; padding: 30px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 1.25rem;">Upload Media File</h3>
            <button type="button" onclick="document.getElementById('uploadModal').style.display='none'" style="background:none; border:none; font-size: 1.5rem; cursor:pointer;">&times;</button>
        </div>
        <form action="<?= admin_url('media.php') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="upload">

            <div class="form-row">
                <label class="form-label-admin">Select File (JPG, PNG, WEBP, PDF up to 10MB) <span style="color:red;">*</span></label>
                <input type="file" name="media_file" class="form-control-admin" required accept=".jpg,.jpeg,.png,.webp,.pdf">
            </div>

            <div class="form-row">
                <label class="form-label-admin">Descriptive Alt Text</label>
                <input type="text" name="alt_text" class="form-control-admin" placeholder="e.g. Industrial Pipe Valve Inspection">
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-admin btn-admin-outline" onclick="document.getElementById('uploadModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-admin btn-admin-primary">Upload File &rarr;</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
