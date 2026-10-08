<?php
/**
 * STONE ENERGY INT'L LTD - Project Portfolio & Detail
 */
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

if (!empty($slug)) {
    // Single Project View
    $project = Database::fetchOne(
        "SELECT p.*, c.name as category_name 
         FROM `projects` p 
         JOIN `project_categories` c ON p.category_id = c.id 
         WHERE p.slug = :s AND p.published_status = 'published' LIMIT 1",
        [':s' => $slug]
    );

    if (!$project) {
        http_response_code(404);
        include ROOT_PATH . '404.php';
        exit;
    }

    $pageTitle = $project['title'] . " | Project Case Details | STONE ENERGY INT'L LTD";
    $pageDescription = truncate($project['description'], 160);
    $gallery = Database::fetchAll("SELECT * FROM `project_images` WHERE `project_id` = :pid ORDER BY `sort_order` ASC", [':pid' => $project['id']]);

    include INCLUDES_PATH . 'header.php';
    ?>
    <div class="section-dark" style="padding: 50px 0; border-bottom: 3px solid var(--color-accent-500);">
        <div class="container">
            <div class="breadcrumbs" style="margin-bottom: 12px; color: #94a3b8;">
                <a href="<?= url('') ?>" style="color: #cbd5e1;">Home</a> &rarr; 
                <a href="<?= url('projects.php') ?>" style="color: #cbd5e1;">Portfolio</a> &rarr; 
                <span><?= e($project['title']) ?></span>
            </div>
            <h1 style="font-size: 2.4rem; color: #fff; margin-bottom: 8px;"><?= e($project['title']) ?></h1>
            <div style="display: flex; gap: 14px; align-items: center; font-size: 0.9rem; color: #cbd5e1;">
                <span>Category: <strong><?= e($project['category_name']) ?></strong></span>
                <span>&bull;</span>
                <span>Location: <strong><?= e($project['location']) ?></strong></span>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div style="display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 48px; align-items: start;">
                <!-- Main Project Scope & Description -->
                <div>
                    <div style="margin-bottom: <?= !empty($gallery) ? '12px' : '30px' ?>; border-radius: var(--radius-md); overflow: hidden; max-height: 440px; background: #000;">
                        <img id="mainProjectImg" src="<?= upload_url($project['featured_image'], 'assets/images/placeholder.svg') ?>" alt="<?= e($project['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>

                    <?php if (!empty($gallery)): ?>
                    <div style="display: flex; gap: 10px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 28px;">
                        <div style="width: 80px; height: 60px; border: 2px solid var(--color-accent-500); border-radius: var(--radius-sm); overflow: hidden; cursor: pointer; flex-shrink: 0;" onclick="document.getElementById('mainProjectImg').src='<?= upload_url($project['featured_image'], 'assets/images/placeholder.svg') ?>'">
                            <img src="<?= upload_url($project['featured_image'], 'assets/images/placeholder.svg') ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <?php foreach ($gallery as $g): ?>
                        <div style="width: 80px; height: 60px; border: 1px solid var(--color-gray-300); border-radius: var(--radius-sm); overflow: hidden; cursor: pointer; flex-shrink: 0;" onclick="document.getElementById('mainProjectImg').src='<?= upload_url($g['image_path']) ?>'">
                            <img src="<?= upload_url($g['image_path']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <h2 style="font-size: 1.6rem; margin-bottom: 16px;">Execution Details &amp; Summary</h2>
                    <div style="font-size: 1rem; line-height: 1.8; color: var(--color-dark-700); margin-bottom: 32px;">
                        <?= $project['description'] ?>
                    </div>

                    <?php if (!empty($project['scope'])): 
                        $scopes = explode('||', $project['scope']);
                    ?>
                    <div style="background: var(--color-gray-100); border-radius: var(--radius-md); padding: 28px; margin-bottom: 36px; border-left: 4px solid var(--color-accent-500);">
                        <h3 style="font-size: 1.2rem; margin-bottom: 16px; color: var(--color-primary-900);">Contracting Scope of Work</h3>
                        <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                            <?php foreach ($scopes as $sc): ?>
                            <div style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.92rem; color: var(--color-dark-800);">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--color-accent-500); flex-shrink: 0; margin-top: 2px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span><?= e(trim($sc)) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="rfq-feature-banner">
                        <div>
                            <h3 style="color: #fff; font-size: 1.4rem; margin-bottom: 6px;">Initiating a Similar Project?</h3>
                            <p style="color: #cbd5e1; margin-bottom: 0; font-size: 0.9rem;">
                                Consult with our engineering and procurement team for estimates.
                            </p>
                        </div>
                        <a href="<?= url('quote.php') ?>" class="btn btn-primary">
                            REQUEST A QUOTE
                        </a>
                    </div>
                </div>

                <!-- Meta Sidebar -->
                <div>
                    <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); padding: 28px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
                        <h4 style="font-size: 1.15rem; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1px solid var(--color-gray-200);">Project Overview</h4>
                        <table style="width: 100%; font-size: 0.9rem; border-collapse: collapse;">
                            <tbody>
                                <tr style="border-bottom: 1px solid var(--color-gray-100);">
                                    <td style="padding: 10px 0; color: var(--color-dark-600); font-weight: 600;">Status</td>
                                    <td style="padding: 10px 0;">
                                        <?php
                                        $sClass = match ($project['status']) {
                                            'Completed' => 'status-completed',
                                            'Ongoing' => 'status-ongoing',
                                            default => 'status-upcoming'
                                        };
                                        ?>
                                        <span class="status-badge <?= $sClass ?>" style="position: static;"><?= e($project['status']) ?></span>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--color-gray-100);">
                                    <td style="padding: 10px 0; color: var(--color-dark-600); font-weight: 600;">Location</td>
                                    <td style="padding: 10px 0; color: var(--color-dark-800); font-weight: 600;"><?= e($project['location']) ?></td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--color-gray-100);">
                                    <td style="padding: 10px 0; color: var(--color-dark-600); font-weight: 600;">Client Entity</td>
                                    <td style="padding: 10px 0; color: var(--color-dark-800);"><?= e($project['client']) ?></td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--color-gray-100);">
                                    <td style="padding: 10px 0; color: var(--color-dark-600); font-weight: 600;">Sector</td>
                                    <td style="padding: 10px 0; color: var(--color-dark-800);"><?= e($project['category_name']) ?></td>
                                </tr>
                                <?php if ($project['start_date']): ?>
                                <tr style="border-bottom: 1px solid var(--color-gray-100);">
                                    <td style="padding: 10px 0; color: var(--color-dark-600); font-weight: 600;">Commencement</td>
                                    <td style="padding: 10px 0; color: var(--color-dark-800);"><?= format_date($project['start_date']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($project['completion_date']): ?>
                                <tr>
                                    <td style="padding: 10px 0; color: var(--color-dark-600); font-weight: 600;">Completion</td>
                                    <td style="padding: 10px 0; color: var(--color-dark-800);"><?= format_date($project['completion_date']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
    include INCLUDES_PATH . 'footer.php';
    exit;
}

// Listing
$pageTitle = "Project Portfolio | STONE ENERGY INT'L LTD";
$pageDescription = "View general contracting executions, upcoming builds, ongoing civil projects, and procurement supply records.";

$sql = "SELECT p.*, c.name as category_name 
        FROM `projects` p 
        JOIN `project_categories` c ON p.category_id = c.id 
        WHERE p.published_status = 'published' ";
$params = [];

if (!empty($statusFilter) && in_array($statusFilter, ['Upcoming', 'Ongoing', 'Completed'], true)) {
    $sql .= "AND p.status = :status ";
    $params[':status'] = $statusFilter;
}

$sql .= "ORDER BY p.id DESC";
$projects = Database::fetchAll($sql, $params);

include INCLUDES_PATH . 'header.php';
?>

<div class="section-dark" style="padding: 60px 0; border-bottom: 3px solid var(--color-accent-500);">
    <div class="container">
        <span class="section-tag section-tag-light">TRACK RECORD &amp; EXECUTION</span>
        <h1 style="font-size: 2.8rem; color: #fff; margin-bottom: 12px;">Project Portfolio</h1>
        <p style="color: #cbd5e1; max-width: 720px; font-size: 1.1rem; margin-bottom: 0;">
            A transparent overview of our general contracting works, civil renovations, and multi-sector supply consignments across Nigeria.
        </p>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- Status Filter Tabs -->
        <div class="catalog-toolbar">
            <div class="category-pills">
                <a href="<?= url('projects.php') ?>" class="pill-btn <?= empty($statusFilter) ? 'active' : '' ?>">
                    All Projects
                </a>
                <a href="<?= url('projects.php?status=Ongoing') ?>" class="pill-btn <?= ($statusFilter === 'Ongoing') ? 'active' : '' ?>">
                    Ongoing Works
                </a>
                <a href="<?= url('projects.php?status=Completed') ?>" class="pill-btn <?= ($statusFilter === 'Completed') ? 'active' : '' ?>">
                    Completed Executions
                </a>
                <a href="<?= url('projects.php?status=Upcoming') ?>" class="pill-btn <?= ($statusFilter === 'Upcoming') ? 'active' : '' ?>">
                    Upcoming Contracts
                </a>
            </div>
        </div>

        <?php if (empty($projects)): ?>
        <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); padding: 60px 20px; text-align: center;">
            <h3 style="font-size: 1.3rem; margin-bottom: 8px;">No Projects Listed</h3>
            <p style="color: var(--color-dark-600); margin-bottom: 20px;">No projects currently registered under this category status.</p>
            <a href="<?= url('projects.php') ?>" class="btn btn-outline-gold btn-sm">View All Projects</a>
        </div>
        <?php else: ?>
        <div class="projects-grid">
            <?php foreach ($projects as $proj): 
                $sClass = match ($proj['status']) {
                    'Completed' => 'status-completed',
                    'Ongoing' => 'status-ongoing',
                    default => 'status-upcoming'
                };
            ?>
            <div class="project-card">
                <div class="project-thumb">
                    <img src="<?= upload_url($proj['featured_image'], 'assets/images/placeholder.svg') ?>" alt="<?= e($proj['title']) ?>" loading="lazy">
                    <span class="status-badge <?= $sClass ?>"><?= e($proj['status']) ?></span>
                </div>
                <div class="project-body">
                    <div class="project-meta">
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <?= e($proj['location']) ?>
                        </span>
                        <span>&bull;</span>
                        <span><?= e($proj['category_name']) ?></span>
                    </div>

                    <h3><?= e($proj['title']) ?></h3>
                    <p><?= truncate($proj['description'], 130) ?></p>

                    <?php if (!empty($proj['scope'])): 
                        $scopes = array_slice(explode('||', $proj['scope']), 0, 2);
                    ?>
                    <div class="scope-pills">
                        <?php foreach ($scopes as $sc): ?>
                            <span class="scope-pill"><?= e(trim($sc)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div style="margin-top: auto; padding-top: 14px; border-top: 1px solid var(--color-gray-100); display: flex; justify-content: space-between; align-items: center;">
                        <a href="<?= url('projects.php?slug=' . urlencode($proj['slug'])) ?>" class="service-link">
                            Case Details &rarr;
                        </a>
                        <span style="font-size: 0.78rem; color: var(--color-gray-400);">Client: <?= e($proj['client']) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include INCLUDES_PATH . 'footer.php'; ?>
