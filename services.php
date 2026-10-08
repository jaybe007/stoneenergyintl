<?php
/**
 * STONE ENERGY INT'L LTD - Services Directory & Single Service View
 */
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');

if (!empty($slug)) {
    // Single Service Detail
    $service = Database::fetchOne(
        "SELECT s.*, c.name as category_name 
         FROM `services` s 
         JOIN `service_categories` c ON s.category_id = c.id 
         WHERE s.slug = :s AND s.status = 'active' LIMIT 1",
        [':s' => $slug]
    );

    if (!$service) {
        http_response_code(404);
        include ROOT_PATH . '404.php';
        exit;
    }

    $pageTitle = $service['meta_title'] ?: ($service['title'] . " | STONE ENERGY INT'L LTD");
    $pageDescription = $service['meta_description'] ?: $service['short_description'];
    
    // Other services for sidebar
    $otherServices = Database::fetchAll("SELECT title, slug FROM `services` WHERE `status` = 'active' AND `id` != :id ORDER BY `sort_order` ASC", [':id' => $service['id']]);

    include INCLUDES_PATH . 'header.php';
    ?>
    <div class="section-dark" style="padding: 60px 0; border-bottom: 3px solid var(--color-accent-500);">
        <div class="container">
            <span class="section-tag section-tag-light"><?= e($service['category_name']) ?></span>
            <h1 style="font-size: 2.8rem; color: #fff; margin-bottom: 12px;"><?= e($service['title']) ?></h1>
            <p style="color: #cbd5e1; max-width: 720px; font-size: 1.1rem; margin-bottom: 0;">
                <?= e($service['short_description']) ?>
            </p>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 48px; align-items: start;">
                <!-- Main Service Content -->
                <div>
                    <div style="font-size: 1.05rem; line-height: 1.8; color: var(--color-dark-700); margin-bottom: 36px;">
                        <?= $service['full_description'] ?>
                    </div>

                    <?php if (!empty($service['features'])): 
                        $features = explode('||', $service['features']);
                    ?>
                    <div style="background: var(--color-gray-100); border-radius: var(--radius-md); padding: 32px; margin-bottom: 40px; border-left: 4px solid var(--color-accent-500);">
                        <h3 style="font-size: 1.3rem; margin-bottom: 18px; color: var(--color-primary-900);">Core Service Scope &amp; Deliverables</h3>
                        <div style="display: grid; grid-template-columns: 1fr; gap: 12px;">
                            <?php foreach ($features as $f): ?>
                            <div style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.95rem; color: var(--color-dark-800);">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--color-accent-500); flex-shrink: 0; margin-top: 3px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span><?= e(trim($f)) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Box -->
                    <div class="rfq-feature-banner" style="margin-top: 30px;">
                        <div>
                            <h3 style="color: #fff; font-size: 1.5rem; margin-bottom: 8px;">Request a Commercial Quote</h3>
                            <p style="color: #cbd5e1; margin-bottom: 0; font-size: 0.95rem;">
                                Submit specifications or your Bill of Quantities for <strong><?= e($service['title']) ?></strong>.
                            </p>
                        </div>
                        <a href="<?= url('quote.php?service=' . urlencode($service['title'])) ?>" class="btn btn-primary btn-lg">
                            SUBMIT RFQ
                        </a>
                    </div>
                </div>

                <!-- Sidebar Directory -->
                <div>
                    <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); padding: 26px; margin-bottom: 30px; box-shadow: var(--shadow-sm);">
                        <h4 style="font-size: 1.1rem; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1px solid var(--color-gray-200);">All Services</h4>
                        <ul style="display: flex; flex-direction: column; gap: 10px;">
                            <?php foreach ($otherServices as $os): ?>
                            <li>
                                <a href="<?= url('services.php?slug=' . urlencode($os['slug'])) ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-radius: var(--radius-sm); background: var(--color-gray-100); color: var(--color-dark-800); font-size: 0.88rem; font-weight: 600;">
                                    <span><?= e($os['title']) ?></span>
                                    <span style="color: var(--color-accent-500);">&rarr;</span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Direct Contact Assistance Box -->
                    <div style="background: var(--color-primary-900); color: #fff; border-radius: var(--radius-md); padding: 28px;">
                        <h4 style="color: #fff; font-size: 1.15rem; margin-bottom: 10px;">Need Direct Assistance?</h4>
                        <p style="color: #cbd5e1; font-size: 0.88rem; margin-bottom: 20px;">
                            Speak directly with our technical procurement coordinators in Ibadan.
                        </p>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <a href="tel:<?= e(setting('phone_primary', '08037745881')) ?>" class="btn btn-outline-gold btn-sm" style="width: 100%;">
                                Call: <?= e(setting('phone_primary', '08037745881')) ?>
                            </a>
                            <a href="<?= url('contact.php') ?>" class="btn btn-outline-light btn-sm" style="width: 100%;">
                                Contact Headquarters
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php
    include INCLUDES_PATH . 'footer.php';
    exit;
}

// Full Services Listing
$pageTitle = "General Contracting & Multi-Sector Services | STONE ENERGY INT'L LTD";
$pageDescription = "Explore our six primary contracting and supply sectors: Oil & Gas Supply, Building Construction, Hospital Equipment, Agro-Allied, Agroprocessing, and General Contracting.";

$services = Database::fetchAll("SELECT s.*, c.name as category_name FROM `services` s JOIN `service_categories` c ON s.category_id = c.id WHERE s.status = 'active' ORDER BY s.sort_order ASC");

include INCLUDES_PATH . 'header.php';
?>

<div class="section-dark" style="padding: 60px 0; border-bottom: 3px solid var(--color-accent-500);">
    <div class="container">
        <span class="section-tag section-tag-light">SERVICES &amp; CAPABILITIES</span>
        <h1 style="font-size: 2.8rem; color: #fff; margin-bottom: 12px;">Multi-Sector Contracting &amp; Supply</h1>
        <p style="color: #cbd5e1; max-width: 720px; font-size: 1.1rem; margin-bottom: 0;">
            Dependable procurement coordination, project management, and turnkey execution across energy, civil engineering, healthcare, and agro-allied industries in Nigeria.
        </p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="services-grid">
            <?php foreach ($services as $srv): ?>
            <div class="service-card">
                <div class="service-icon-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                </div>
                <div style="font-size: 0.76rem; font-weight: 700; color: var(--color-accent-500); text-transform: uppercase; margin-bottom: 4px;">
                    <?= e($srv['category_name']) ?>
                </div>
                <h3><?= e($srv['title']) ?></h3>
                <p><?= e($srv['short_description']) ?></p>

                <?php if (!empty($srv['features'])): 
                    $feats = array_slice(explode('||', $srv['features']), 0, 4);
                ?>
                <ul class="service-features-list">
                    <?php foreach ($feats as $f): ?>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span><?= e(trim($f)) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; padding-top: 14px; border-top: 1px solid var(--color-gray-100);">
                    <a href="<?= url('services.php?slug=' . urlencode($srv['slug'])) ?>" class="service-link">
                        Service Details &rarr;
                    </a>
                    <a href="<?= url('quote.php?service=' . urlencode($srv['title'])) ?>" class="btn btn-outline-gold btn-sm">
                        Quote &rarr;
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include INCLUDES_PATH . 'footer.php'; ?>
