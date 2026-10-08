<?php
/**
 * STONE ENERGY INT'L LTD - Dynamic XML Sitemap Generator
 * Outputs valid XML conforming to Sitemaps.org standard
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = rtrim(BASE_URL, '/') . '/';

$staticPages = [
    ['loc' => '', 'freq' => 'weekly', 'prio' => '1.0'],
    ['loc' => 'about.php', 'freq' => 'monthly', 'prio' => '0.8'],
    ['loc' => 'services.php', 'freq' => 'weekly', 'prio' => '0.9'],
    ['loc' => 'products.php', 'freq' => 'weekly', 'prio' => '0.9'],
    ['loc' => 'projects.php', 'freq' => 'weekly', 'prio' => '0.85'],
    ['loc' => 'industries.php', 'freq' => 'monthly', 'prio' => '0.8'],
    ['loc' => 'quote.php', 'freq' => 'monthly', 'prio' => '0.8'],
    ['loc' => 'contact.php', 'freq' => 'monthly', 'prio' => '0.7'],
    ['loc' => 'blog.php', 'freq' => 'weekly', 'prio' => '0.8'],
    ['loc' => 'privacy.php', 'freq' => 'yearly', 'prio' => '0.3'],
    ['loc' => 'terms.php', 'freq' => 'yearly', 'prio' => '0.3'],
    ['loc' => 'cookie-policy.php', 'freq' => 'yearly', 'prio' => '0.3'],
    ['loc' => 'disclaimer.php', 'freq' => 'yearly', 'prio' => '0.3'],
];

// Query dynamic items
$services = Database::fetchAll("SELECT `slug`, `updated_at` FROM `services` WHERE `status` = 'active'");
$products = Database::fetchAll("SELECT `slug`, `updated_at` FROM `products` WHERE `status` = 'published'");
$projects = Database::fetchAll("SELECT `slug`, `updated_at` FROM `projects` WHERE `published_status` = 'published'");
$blogs = Database::fetchAll("SELECT `slug`, `published_at` FROM `blog_posts` WHERE `status` = 'Published'");

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($staticPages as $p): ?>
    <url>
        <loc><?= htmlspecialchars($baseUrl . $p['loc'], ENT_XML1, 'UTF-8') ?></loc>
        <changefreq><?= $p['freq'] ?></changefreq>
        <priority><?= $p['prio'] ?></priority>
    </url>
<?php endforeach; ?>

<?php foreach ($services as $s): ?>
    <url>
        <loc><?= htmlspecialchars($baseUrl . 'services.php?slug=' . urlencode($s['slug']), ENT_XML1, 'UTF-8') ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($s['updated_at'] ?? 'now')) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.85</priority>
    </url>
<?php endforeach; ?>

<?php foreach ($products as $pr): ?>
    <url>
        <loc><?= htmlspecialchars($baseUrl . 'products.php?slug=' . urlencode($pr['slug']), ENT_XML1, 'UTF-8') ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($pr['updated_at'] ?? 'now')) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
<?php endforeach; ?>

<?php foreach ($projects as $pj): ?>
    <url>
        <loc><?= htmlspecialchars($baseUrl . 'projects.php?slug=' . urlencode($pj['slug']), ENT_XML1, 'UTF-8') ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($pj['updated_at'] ?? 'now')) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
<?php endforeach; ?>

<?php foreach ($blogs as $b): ?>
    <url>
        <loc><?= htmlspecialchars($baseUrl . 'blog.php?slug=' . urlencode($b['slug']), ENT_XML1, 'UTF-8') ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($b['published_at'] ?? 'now')) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
<?php endforeach; ?>
</urlset>
