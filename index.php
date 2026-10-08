<?php
/**
 * STONE ENERGY INT'L LTD - Homepage
 */
require_once __DIR__ . '/config/config.php';

$pageTitle = "STONE ENERGY INT'L LTD | General Contractor & Multi-Sector Supply Solutions";
$pageDescription = "STONE ENERGY INT'L LTD is a Nigerian general contracting and multi-sector supply company providing solutions across oil & gas, construction, healthcare, agro-allied and agroprocessing industries.";

// Load dynamic data from DB
$services = Database::fetchAll("SELECT * FROM `services` WHERE `status` = 'active' ORDER BY `sort_order` ASC LIMIT 6");
$featuredProducts = Database::fetchAll("SELECT p.*, c.name as category_name FROM `products` p JOIN `product_categories` c ON p.category_id = c.id WHERE p.status = 'published' AND p.featured = 1 ORDER BY p.id DESC LIMIT 4");
$featuredProjects = Database::fetchAll("SELECT p.*, c.name as category_name FROM `projects` p JOIN `project_categories` c ON p.category_id = c.id WHERE p.published_status = 'published' ORDER BY p.id DESC LIMIT 3");
$recentBlog = Database::fetchAll("SELECT b.*, c.name as category_name FROM `blog_posts` b JOIN `blog_categories` c ON b.category_id = c.id WHERE b.status = 'Published' ORDER BY b.published_at DESC LIMIT 3");

// Homepage section toggles from CMS
$heroEnabled       = Settings::getBool('section_hero_enabled', true);
$servicesEnabled   = Settings::getBool('section_services_enabled', true);
$whyChooseEnabled  = Settings::getBool('section_why_choose_enabled', true);
$productsEnabled   = Settings::getBool('section_products_enabled', true);
$projectsEnabled   = Settings::getBool('section_projects_enabled', true);
$quoteCtaEnabled   = Settings::getBool('section_quote_cta_enabled', true);

include INCLUDES_PATH . 'header.php';
?>

<!-- 1. HERO SECTION -->
<?php if ($heroEnabled): 
    $customHeroBg = setting('hero_image', '');
    $heroStyle = !empty($customHeroBg) ? 'style="background-image: linear-gradient(rgba(10, 25, 47, 0.88), rgba(5, 12, 24, 0.94)), url(\'' . upload_url($customHeroBg) . '\'); background-size: cover; background-position: center;"' : '';
?>
<section class="hero" <?= $heroStyle ?>>
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <span>GENERAL CONTRACTOR &amp; MULTI-SECTOR SUPPLY</span>
            </div>
            
            <h1 class="hero-title">
                Building, Supplying and Delivering Solutions <span>That Move Businesses Forward.</span>
            </h1>
            
            <p class="hero-subtitle">
                <?= e(setting('hero_subheadline', 'STONE ENERGY INT\'L LTD is a Nigerian general contracting and multi-sector supply company providing solutions across oil & gas, construction, healthcare, agro-allied and agroprocessing industries.')) ?>
            </p>
            
            <div class="hero-actions">
                <a href="<?= url(setting('hero_cta_primary_link', 'quote.php')) ?>" class="btn btn-primary btn-lg">
                    <?= e(setting('hero_cta_primary_text', 'REQUEST A QUOTE')) ?>
                </a>
                <a href="<?= url(setting('hero_cta_secondary_link', 'services.php')) ?>" class="btn btn-outline-gold btn-lg">
                    <?= e(setting('hero_cta_secondary_text', 'EXPLORE OUR SERVICES')) ?>
                </a>
            </div>

            <!-- Core Operational Pillars -->
            <div class="hero-pillars">
                <div class="pillar-item">
                    <div class="pillar-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                    </div>
                    <div class="pillar-text">Oil &amp; Gas Supply Solutions</div>
                </div>
                <div class="pillar-item">
                    <div class="pillar-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="9" y1="22" x2="9" y2="22.01"></line><line x1="15" y1="22" x2="15" y2="22.01"></line></svg>
                    </div>
                    <div class="pillar-text">Building &amp; Civil Construction</div>
                </div>
                <div class="pillar-item">
                    <div class="pillar-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                    </div>
                    <div class="pillar-text">Hospital Equipment &amp; Medical</div>
                </div>
                <div class="pillar-item">
                    <div class="pillar-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <div class="pillar-text">Agro-Allied &amp; Processing</div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 2. CORE BUSINESS SECTORS -->
<?php if ($servicesEnabled): ?>
<section class="section" id="servicesSection">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">OUR EXPERTISE</span>
            <h2 class="section-title">Integrated Multi-Sector Solutions</h2>
            <p class="section-subtitle">
                We bridge procurement and project management gaps with reliable execution across Nigeria's vital industrial sectors.
            </p>
        </div>

        <div class="services-grid">
            <?php foreach ($services as $srv): ?>
            <div class="service-card">
                <div class="service-icon-box">
                    <?php if ($srv['icon'] === 'fuel'): ?>
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18"></path><path d="M15 10h4a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-4"></path><path d="M7 11h4"></path></svg>
                    <?php elseif ($srv['icon'] === 'building'): ?>
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="9" y1="22" x2="9" y2="22.01"></line><line x1="15" y1="22" x2="15" y2="22.01"></line><line x1="8" y1="6" x2="10" y2="6"></line><line x1="14" y1="6" x2="16" y2="6"></line><line x1="8" y1="10" x2="10" y2="10"></line><line x1="14" y1="10" x2="16" y2="10"></line></svg>
                    <?php elseif ($srv['icon'] === 'heart-pulse'): ?>
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                    <?php elseif ($srv['icon'] === 'sprout'): ?>
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 20h10"></path><path d="M10 20c5.5-2.5.8-6.4 3-10"></path><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"></path><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4.1.9-4.9 2z"></path></svg>
                    <?php elseif ($srv['icon'] === 'cog'): ?>
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    <?php else: ?>
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    <?php endif; ?>
                </div>

                <h3><?= e($srv['title']) ?></h3>
                <p><?= e($srv['short_description']) ?></p>

                <?php if (!empty($srv['features'])): 
                    $feats = array_slice(explode('||', $srv['features']), 0, 3);
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

                <a href="<?= url('services.php?slug=' . urlencode($srv['slug'])) ?>" class="service-link">
                    Explore Service Details &rarr;
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 3. WHY CHOOSE US SECTION -->
<?php if ($whyChooseEnabled): ?>
<section class="section section-dark">
    <div class="container">
        <div class="section-header">
            <span class="section-tag section-tag-light">WHY STONE ENERGY</span>
            <h2 class="section-title">Built on Accountability, Quality and Procurement Discipline</h2>
            <p class="section-subtitle">
                Our approach provides clients with dependable single-point contracting coordination, minimizing risk and ensuring timely project delivery.
            </p>
        </div>

        <div class="why-grid">
            <div class="why-card">
                <div class="why-number">01</div>
                <h3>Multi-Sector Procurement Scope</h3>
                <p>Consolidated procurement networks across energy, construction rebars, clinical hardware, and agricultural inputs without middleman markups.</p>
            </div>
            <div class="why-card">
                <div class="why-number">02</div>
                <h3>General Contracting Rigor</h3>
                <p>Hands-on project supervision, bill of quantities discipline, and verified subcontracting management for commercial and civil projects.</p>
            </div>
            <div class="why-card">
                <div class="why-number">03</div>
                <h3>Ibadan Base, Nationwide Reach</h3>
                <p>Strategically headquartered in Ibadan, Oyo State, with logistics pathways servicing commercial hubs across South-West Nigeria and beyond.</p>
            </div>
            <div class="why-card">
                <div class="why-number">04</div>
                <h3>Transparent Specification Matching</h3>
                <p>Strict verification against engineering drawings and clinical standards before consignment dispatch. No unverified substitutes.</p>
            </div>
            <div class="why-card">
                <div class="why-number">05</div>
                <h3>Structured RFQ Turnaround</h3>
                <p>Every commercial inquiry receives a tracked RFQ number and rapid technical analysis to guarantee prompt commercial bidding.</p>
            </div>
            <div class="why-card">
                <div class="why-number">06</div>
                <h3>Uncompromising Safety & Integrity</h3>
                <p>Safety standards adhered to at construction sites and warehouse dispatch points, safeguarding personnel and assets.</p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 4. FEATURED PRODUCTS CATALOGUE PREVIEW -->
<?php if ($productsEnabled && !empty($featuredProducts)): ?>
<section class="section section-gray">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">PRODUCT CATALOGUE</span>
            <h2 class="section-title">Industrial Equipment &amp; Procurement Supplies</h2>
            <p class="section-subtitle">
                Explore our catalog across energy, civil construction, hospital devices, and agricultural inputs.
            </p>
        </div>

        <div class="products-grid">
            <?php foreach ($featuredProducts as $prod): ?>
            <div class="product-card">
                <div class="product-thumb">
                    <img src="<?= upload_url($prod['image'], 'assets/images/placeholder.svg') ?>" alt="<?= e($prod['name']) ?>" loading="lazy">
                    <span class="product-badge"><?= e($prod['category_name']) ?></span>
                </div>
                <div class="product-body">
                    <div class="product-sku">SKU: <?= e($prod['sku'] ?? 'N/A') ?></div>
                    <h3><?= e($prod['name']) ?></h3>
                    <p><?= e($prod['short_description']) ?></p>
                    <div class="product-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-modal-target="quickRfqModal" data-quote-item="<?= e($prod['name'] . ' (SKU: ' . ($prod['sku'] ?? '') . ')') ?>">
                            Request a Quote
                        </button>
                        <a href="<?= url('products.php?slug=' . urlencode($prod['slug'])) ?>" style="font-size: 0.82rem; font-weight: 600;">
                            Details &rarr;
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="<?= url('products.php') ?>" class="btn btn-navy">
                View Full Product Catalogue &rarr;
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 5. FEATURED PROJECT PORTFOLIO -->
<?php if ($projectsEnabled && !empty($featuredProjects)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">PROJECT PORTFOLIO</span>
            <h2 class="section-title">General Contracting &amp; Supply Executions</h2>
            <p class="section-subtitle">
                A selection of ongoing, upcoming, and completed project deliveries coordinated by our engineering and logistics teams.
            </p>
        </div>

        <div class="projects-grid">
            <?php foreach ($featuredProjects as $proj): 
                $statusClass = match ($proj['status']) {
                    'Completed' => 'status-completed',
                    'Ongoing' => 'status-ongoing',
                    default => 'status-upcoming'
                };
            ?>
            <div class="project-card">
                <div class="project-thumb">
                    <img src="<?= upload_url($proj['featured_image'], 'assets/images/placeholder.svg') ?>" alt="<?= e($proj['title']) ?>" loading="lazy">
                    <span class="status-badge <?= $statusClass ?>"><?= e($proj['status']) ?></span>
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

                    <div style="margin-top: auto; padding-top: 14px; border-top: 1px solid var(--color-gray-100);">
                        <a href="<?= url('projects.php?slug=' . urlencode($proj['slug'])) ?>" class="service-link">
                            View Project Scope &rarr;
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="<?= url('projects.php') ?>" class="btn btn-navy">
                Explore Complete Portfolio &rarr;
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 6. HIGH-IMPACT RFQ CTA BANNER -->
<?php if ($quoteCtaEnabled): ?>
<section class="section" style="padding-top: 0;">
    <div class="container">
        <div class="rfq-feature-banner">
            <div>
                <span class="section-tag section-tag-light">GET A CUSTOM BID</span>
                <h2 style="color: #fff; font-size: 2.1rem; margin: 8px 0 12px 0;">Have a Project or Procurement Requirement?</h2>
                <p style="color: #cbd5e1; max-width: 600px; margin-bottom: 0;">
                    Submit your Bill of Quantities, equipment list, or civil construction specifications. We review all technical requirements and respond with competitive quotes.
                </p>
            </div>
            <div style="flex-shrink: 0;">
                <a href="<?= url('quote.php') ?>" class="btn btn-primary btn-lg" style="font-size: 1.05rem;">
                    SUBMIT RFQ NOW
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 7. RECENT NEWS & INSIGHTS -->
<?php if (!empty($recentBlog)): ?>
<section class="section section-gray">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">INDUSTRY INSIGHTS</span>
            <h2 class="section-title">Latest Articles &amp; Supply Updates</h2>
            <p class="section-subtitle">
                Practical perspectives on general contracting, clinical procurement, and agricultural supply chains in Nigeria.
            </p>
        </div>

        <div class="blog-grid">
            <?php foreach ($recentBlog as $post): ?>
            <div class="blog-card">
                <div class="blog-thumb">
                    <img src="<?= upload_url($post['featured_image'], 'assets/images/placeholder.svg') ?>" alt="<?= e($post['title']) ?>" loading="lazy">
                </div>
                <div class="blog-body">
                    <div class="blog-meta">
                        <span><?= format_date($post['published_at']) ?></span>
                        <span>&bull;</span>
                        <span><?= e($post['category_name']) ?></span>
                    </div>
                    <h3><?= e($post['title']) ?></h3>
                    <p><?= truncate($post['excerpt'], 120) ?></p>
                    <a href="<?= url('blog.php?slug=' . urlencode($post['slug'])) ?>" class="service-link">
                        Read Full Article &rarr;
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include INCLUDES_PATH . 'footer.php'; ?>
