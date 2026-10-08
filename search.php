<?php
/**
 * STONE ENERGY INT'L LTD - Global Site Search
 */
require_once __DIR__ . '/config/config.php';

$query = trim($_GET['q'] ?? '');

$services = [];
$products = [];
$projects = [];
$articles = [];
$faqs     = [];

if (!empty($query)) {
    $param = "%{$query}%";

    // 1. Services
    $services = Database::fetchAll(
        "SELECT id, title, slug, short_description FROM `services` 
         WHERE `status` = 'active' AND (`title` LIKE :p OR `short_description` LIKE :p OR `full_description` LIKE :p) LIMIT 6",
        [':p' => $param]
    );

    // 2. Products
    $products = Database::fetchAll(
        "SELECT id, name, slug, sku, short_description FROM `products` 
         WHERE `status` = 'published' AND (`name` LIKE :p OR `short_description` LIKE :p OR `specifications` LIKE :p OR `sku` LIKE :p) LIMIT 6",
        [':p' => $param]
    );

    // 3. Projects
    $projects = Database::fetchAll(
        "SELECT id, title, slug, location, description, status FROM `projects` 
         WHERE `published_status` = 'published' AND (`title` LIKE :p OR `description` LIKE :p OR `location` LIKE :p OR `scope` LIKE :p) LIMIT 6",
        [':p' => $param]
    );

    // 4. Blog Articles
    $articles = Database::fetchAll(
        "SELECT id, title, slug, excerpt, published_at FROM `blog_posts` 
         WHERE `status` = 'Published' AND (`title` LIKE :p OR `excerpt` LIKE :p OR `content` LIKE :p) LIMIT 6",
        [':p' => $param]
    );

    // 5. FAQs
    $faqs = Database::fetchAll(
        "SELECT id, question, answer, category FROM `faqs` 
         WHERE `status` = 'active' AND (`question` LIKE :p OR `answer` LIKE :p) LIMIT 6",
        [':p' => $param]
    );
}

$pageTitle = "Search Results" . (!empty($query) ? " for '{$query}'" : "") . " | STONE ENERGY INT'L LTD";
$pageDescription = "Find corporate services, products, civil projects, and procurement information.";

include INCLUDES_PATH . 'header.php';
?>

<div class="section-dark" style="padding: 50px 0; border-bottom: 3px solid var(--color-accent-500);">
    <div class="container" style="max-width: 800px; text-align: center;">
        <span class="section-tag section-tag-light">SITE SEARCH</span>
        <h1 style="font-size: 2.4rem; color: #fff; margin-bottom: 16px;">Search Our Directory</h1>
        
        <form action="<?= url('search.php') ?>" method="GET" style="display: flex; gap: 10px; max-width: 600px; margin: 0 auto;">
            <input type="text" name="q" class="form-control" placeholder="Search products, services, projects, rebars, valves..." value="<?= e($query) ?>" required style="padding: 14px 18px; font-size: 1rem;">
            <button type="submit" class="btn btn-primary" style="padding: 14px 28px;">Search</button>
        </form>
    </div>
</div>

<section class="section">
    <div class="container" style="max-width: 880px;">
        <?php if (empty($query)): ?>
            <p style="text-align: center; color: var(--color-dark-600); font-size: 1.05rem;">
                Enter a keyword above to search through our services, product catalog, ongoing projects, and articles.
            </p>
        <?php else: 
            $totalFound = count($services) + count($products) + count($projects) + count($articles) + count($faqs);
        ?>
            <h2 style="font-size: 1.5rem; margin-bottom: 28px; padding-bottom: 12px; border-bottom: 1px solid var(--color-gray-200);">
                Found <?= $totalFound ?> result<?= ($totalFound === 1 ? '' : 's') ?> for "<span style="color: var(--color-accent-600);"><?= e($query) ?></span>"
            </h2>

            <?php if ($totalFound === 0): ?>
                <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); padding: 50px 20px; text-align: center;">
                    <p style="color: var(--color-dark-600); margin-bottom: 16px;">No exact matches found. Try alternative keywords or submit an inquiry directly.</p>
                    <a href="<?= url('quote.php') ?>" class="btn btn-primary btn-sm">Request Custom Quote</a>
                </div>
            <?php else: ?>

                <!-- Services Results -->
                <?php if (!empty($services)): ?>
                <div style="margin-bottom: 36px;">
                    <h3 style="font-size: 1.25rem; color: var(--color-primary-900); margin-bottom: 14px;">Services</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($services as $s): ?>
                        <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-sm); padding: 18px;">
                            <h4 style="font-size: 1.1rem; margin-bottom: 6px;">
                                <a href="<?= url('services.php?slug=' . urlencode($s['slug'])) ?>"><?= e($s['title']) ?></a>
                            </h4>
                            <p style="font-size: 0.88rem; color: var(--color-dark-600); margin-bottom: 0;"><?= e($s['short_description']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Products Results -->
                <?php if (!empty($products)): ?>
                <div style="margin-bottom: 36px;">
                    <h3 style="font-size: 1.25rem; color: var(--color-primary-900); margin-bottom: 14px;">Product Catalogue</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($products as $p): ?>
                        <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-sm); padding: 18px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="font-size: 0.75rem; color: var(--color-gray-400); font-weight: 600;">SKU: <?= e($p['sku'] ?? 'N/A') ?></span>
                                <h4 style="font-size: 1.1rem; margin: 4px 0;">
                                    <a href="<?= url('products.php?slug=' . urlencode($p['slug'])) ?>"><?= e($p['name']) ?></a>
                                </h4>
                                <p style="font-size: 0.88rem; color: var(--color-dark-600); margin-bottom: 0;"><?= e($p['short_description']) ?></p>
                            </div>
                            <a href="<?= url('quote.php?item=' . urlencode($p['name'])) ?>" class="btn btn-outline-gold btn-sm" style="flex-shrink: 0; margin-left: 20px;">
                                Quote
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Projects Results -->
                <?php if (!empty($projects)): ?>
                <div style="margin-bottom: 36px;">
                    <h3 style="font-size: 1.25rem; color: var(--color-primary-900); margin-bottom: 14px;">Projects Portfolio</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($projects as $pr): ?>
                        <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-sm); padding: 18px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <h4 style="font-size: 1.1rem; margin-bottom: 0;">
                                    <a href="<?= url('projects.php?slug=' . urlencode($pr['slug'])) ?>"><?= e($pr['title']) ?></a>
                                </h4>
                                <span class="badge-status badge-new" style="font-size: 0.7rem;"><?= e($pr['status']) ?></span>
                            </div>
                            <p style="font-size: 0.88rem; color: var(--color-dark-600); margin-bottom: 0;"><?= truncate($pr['description'], 120) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- FAQs Results -->
                <?php if (!empty($faqs)): ?>
                <div style="margin-bottom: 36px;">
                    <h3 style="font-size: 1.25rem; color: var(--color-primary-900); margin-bottom: 14px;">Frequently Asked Questions</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($faqs as $fq): ?>
                        <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-sm); padding: 18px;">
                            <h4 style="font-size: 1.05rem; margin-bottom: 6px; color: var(--color-primary-900);"><?= e($fq['question']) ?></h4>
                            <p style="font-size: 0.88rem; color: var(--color-dark-600); margin-bottom: 0;"><?= e($fq['answer']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include INCLUDES_PATH . 'footer.php'; ?>
