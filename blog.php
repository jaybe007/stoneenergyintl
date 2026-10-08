<?php
/**
 * STONE ENERGY INT'L LTD - Blog & News System
 */
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$searchTerm = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

if (!empty($slug)) {
    // Single Post View
    $post = Database::fetchOne(
        "SELECT b.*, c.name as category_name, c.slug as category_slug, u.full_name as author_name 
         FROM `blog_posts` b 
         JOIN `blog_categories` c ON b.category_id = c.id 
         JOIN `users` u ON b.author_id = u.id 
         WHERE b.slug = :s AND b.status = 'Published' LIMIT 1",
        [':s' => $slug]
    );

    if (!$post) {
        http_response_code(404);
        include ROOT_PATH . '404.php';
        exit;
    }

    $pageTitle = $post['seo_title'] ?: ($post['title'] . " | STONE ENERGY INT'L LTD");
    $pageDescription = $post['seo_description'] ?: $post['excerpt'];
    $ogImage = upload_url($post['featured_image']);

    // Related posts
    $related = Database::fetchAll(
        "SELECT b.*, c.name as category_name 
         FROM `blog_posts` b 
         JOIN `blog_categories` c ON b.category_id = c.id 
         WHERE b.category_id = :cid AND b.id != :id AND b.status = 'Published' 
         ORDER BY b.published_at DESC LIMIT 3",
        [':cid' => $post['category_id'], ':id' => $post['id']]
    );

    include INCLUDES_PATH . 'header.php';
    ?>
    <div class="section-dark" style="padding: 50px 0; border-bottom: 3px solid var(--color-accent-500);">
        <div class="container" style="max-width: 900px;">
            <div class="breadcrumbs" style="margin-bottom: 12px; color: #94a3b8;">
                <a href="<?= url('') ?>" style="color: #cbd5e1;">Home</a> &rarr; 
                <a href="<?= url('blog.php') ?>" style="color: #cbd5e1;">Articles</a> &rarr; 
                <span><?= e($post['title']) ?></span>
            </div>
            <span class="section-tag section-tag-light"><?= e($post['category_name']) ?></span>
            <h1 style="font-size: 2.6rem; color: #fff; margin-bottom: 14px; line-height: 1.25;"><?= e($post['title']) ?></h1>
            <div style="font-size: 0.88rem; color: #cbd5e1; display: flex; gap: 16px;">
                <span>Published: <strong><?= format_date($post['published_at']) ?></strong></span>
                <span>&bull;</span>
                <span>Author: <strong><?= e($post['author_name']) ?></strong></span>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="container" style="max-width: 900px;">
            <?php if (!empty($post['featured_image'])): ?>
            <div style="margin-bottom: 36px; border-radius: var(--radius-md); overflow: hidden; max-height: 440px;">
                <img src="<?= upload_url($post['featured_image'], 'assets/images/placeholder.svg') ?>" alt="<?= e($post['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <?php endif; ?>

            <div style="font-size: 1.1rem; line-height: 1.85; color: var(--color-dark-800); margin-bottom: 40px;">
                <?= $post['content'] ?>
            </div>

            <?php if (!empty($post['tags'])): ?>
            <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; padding-top: 20px; border-top: 1px solid var(--color-gray-200); margin-bottom: 40px;">
                <span style="font-size: 0.88rem; font-weight: 600; color: var(--color-dark-600);">Tags:</span>
                <?php foreach (explode(',', $post['tags']) as $tag): ?>
                    <span style="background: var(--color-gray-100); color: var(--color-dark-700); font-size: 0.82rem; padding: 4px 10px; border-radius: var(--radius-sm); font-weight: 500;">
                        #<?= e(trim($tag)) ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Related Articles -->
            <?php if (!empty($related)): ?>
            <div style="margin-top: 60px; padding-top: 40px; border-top: 2px solid var(--color-gray-200);">
                <h3 style="font-size: 1.5rem; margin-bottom: 24px;">Related Perspectives</h3>
                <div class="blog-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                    <?php foreach ($related as $rel): ?>
                    <div class="blog-card">
                        <div class="blog-body">
                            <span style="font-size: 0.75rem; color: var(--color-accent-500); font-weight: 700; text-transform: uppercase;"><?= e($rel['category_name']) ?></span>
                            <h4 style="font-size: 1.15rem; margin: 8px 0;"><?= e($rel['title']) ?></h4>
                            <p style="font-size: 0.85rem; color: var(--color-dark-600);"><?= truncate($rel['excerpt'], 90) ?></p>
                            <a href="<?= url('blog.php?slug=' . urlencode($rel['slug'])) ?>" class="service-link">Read &rarr;</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
    include INCLUDES_PATH . 'footer.php';
    exit;
}

// Blog Listing
$pageTitle = "News, Contracting & Procurement Insights | STONE ENERGY INT'L LTD";
$pageDescription = "Read the latest updates and industry perspectives on general contracting, hospital supplies, oil and gas logistics, and agroprocessing in Nigeria.";

$categories = Database::fetchAll("SELECT * FROM `blog_categories` ORDER BY `id` ASC");

$countSql = "SELECT COUNT(*) FROM `blog_posts` b JOIN `blog_categories` c ON b.category_id = c.id WHERE b.status = 'Published' ";
$sql = "SELECT b.*, c.name as category_name, c.slug as category_slug, u.full_name as author_name 
        FROM `blog_posts` b 
        JOIN `blog_categories` c ON b.category_id = c.id 
        JOIN `users` u ON b.author_id = u.id 
        WHERE b.status = 'Published' ";
$params = [];

if (!empty($categorySlug)) {
    $countSql .= "AND c.slug = :cslug ";
    $sql .= "AND c.slug = :cslug ";
    $params[':cslug'] = $categorySlug;
}

if (!empty($searchTerm)) {
    $countSql .= "AND (b.title LIKE :st OR b.excerpt LIKE :st OR b.content LIKE :st) ";
    $sql .= "AND (b.title LIKE :st OR b.excerpt LIKE :st OR b.content LIKE :st) ";
    $params[':st'] = "%{$searchTerm}%";
}

$totalPosts = (int)Database::fetchColumn($countSql, $params);
$totalPages = ceil($totalPosts / $perPage);

$sql .= "ORDER BY b.published_at DESC LIMIT {$perPage} OFFSET {$offset}";
$posts = Database::fetchAll($sql, $params);

include INCLUDES_PATH . 'header.php';
?>

<div class="section-dark" style="padding: 60px 0; border-bottom: 3px solid var(--color-accent-500);">
    <div class="container">
        <span class="section-tag section-tag-light">KNOWLEDGE &amp; MARKET INSIGHTS</span>
        <h1 style="font-size: 2.8rem; color: #fff; margin-bottom: 12px;">News &amp; Industry Articles</h1>
        <p style="color: #cbd5e1; max-width: 720px; font-size: 1.1rem; margin-bottom: 0;">
            Perspectives on general contracting, healthcare equipment standards, agricultural value chains, and industrial procurement in Nigeria.
        </p>
    </div>
</div>

<section class="section">
    <div class="container">
        <!-- Toolbar -->
        <div class="catalog-toolbar">
            <div class="category-pills">
                <a href="<?= url('blog.php') ?>" class="pill-btn <?= empty($categorySlug) ? 'active' : '' ?>">All Topics</a>
                <?php foreach ($categories as $cat): ?>
                <a href="<?= url('blog.php?category=' . urlencode($cat['slug'])) ?>" class="pill-btn <?= ($categorySlug === $cat['slug']) ? 'active' : '' ?>">
                    <?= e($cat['name']) ?>
                </a>
                <?php endforeach; ?>
            </div>

            <form action="<?= url('blog.php') ?>" method="GET" style="display: flex; gap: 8px;">
                <?php if (!empty($categorySlug)): ?>
                    <input type="hidden" name="category" value="<?= e($categorySlug) ?>">
                <?php endif; ?>
                <input type="text" name="search" class="form-control" placeholder="Search articles..." value="<?= e($searchTerm) ?>" style="padding: 8px 14px; font-size: 0.88rem; width: 200px;">
                <button type="submit" class="btn btn-navy btn-sm">Search</button>
            </form>
        </div>

        <?php if (empty($posts)): ?>
        <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); padding: 60px 20px; text-align: center;">
            <h3 style="font-size: 1.3rem; margin-bottom: 8px;">No Articles Found</h3>
            <p style="color: var(--color-dark-600); margin-bottom: 20px;">No published articles matched your search or category selection.</p>
            <a href="<?= url('blog.php') ?>" class="btn btn-outline-gold btn-sm">Reset</a>
        </div>
        <?php else: ?>
        <div class="blog-grid">
            <?php foreach ($posts as $p): ?>
            <div class="blog-card">
                <div class="blog-thumb">
                    <img src="<?= upload_url($p['featured_image'], 'assets/images/placeholder.svg') ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                </div>
                <div class="blog-body">
                    <div class="blog-meta">
                        <span><?= format_date($p['published_at']) ?></span>
                        <span>&bull;</span>
                        <span><?= e($p['category_name']) ?></span>
                    </div>
                    <h3><?= e($p['title']) ?></h3>
                    <p><?= truncate($p['excerpt'], 140) ?></p>
                    <a href="<?= url('blog.php?slug=' . urlencode($p['slug'])) ?>" class="service-link">
                        Read Full Article &rarr;
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: center; gap: 8px; margin-top: 48px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= url('blog.php?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>" class="pill-btn <?= ($page === $i) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<?php include INCLUDES_PATH . 'footer.php'; ?>
