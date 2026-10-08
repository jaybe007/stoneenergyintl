<?php
/**
 * STONE ENERGY INT'L LTD - Homepage & About Section CMS
 */
$requiredPermission = 'pages.manage';
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Homepage & About CMS | STONE ENERGY INT'L LTD CMS";
$adminSection = "Homepage & About CMS";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();

    $settings = $_POST['settings'] ?? [];
    foreach ($settings as $key => $val) {
        Settings::set($key, trim($val));
    }

    // Handle Hero Background Image upload
    if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadHero = Uploader::upload($_FILES['hero_image_file'], 'Hero Section Background');
        if ($uploadHero['success']) {
            Settings::set('hero_image', $uploadHero['file_path']);
        }
    }
    if (!empty($_POST['remove_hero_image'])) {
        Settings::set('hero_image', '');
    }

    // Toggle checkboxes
    $sectionToggles = [
        'section_hero_enabled',
        'section_services_enabled',
        'section_why_choose_enabled',
        'section_products_enabled',
        'section_projects_enabled',
        'section_quote_cta_enabled',
        'section_testimonials_enabled'
    ];

    foreach ($sectionToggles as $sec) {
        Settings::set($sec, isset($settings[$sec]) ? '1' : '0');
    }

    Audit::log('update_homepage_cms', 'pages', null, "Updated homepage & about CMS layout sections");
    set_flash('success', 'Homepage & About section content updated successfully.');
    header('Location: ' . admin_url('homepage-cms.php'));
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>Homepage &amp; Corporate Narrative Content</h3>
    </div>
    <div class="admin-card-body">
        <div class="tabs-nav">
            <button type="button" class="tab-link active" data-tab="tabHero">Hero Section</button>
            <button type="button" class="tab-link" data-tab="tabSections">Homepage Section Toggles</button>
            <button type="button" class="tab-link" data-tab="tabAbout">About Page Content</button>
        </div>

        <form action="<?= admin_url('homepage-cms.php') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <!-- Tab 1: Hero Section -->
            <div id="tabHero" class="tab-content" style="display: block;">
                <div class="form-row">
                    <label class="form-label-admin">Hero Main Headline</label>
                    <textarea name="settings[hero_headline]" class="form-control-admin" style="min-height: 80px; font-weight: 700; font-size: 1.1rem;"><?= e(setting('hero_headline', "Building, Supplying and Delivering Solutions That Move Businesses Forward.")) ?></textarea>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Hero Sub-Headline Narrative</label>
                    <textarea name="settings[hero_subheadline]" class="form-control-admin" style="min-height: 90px;"><?= e(setting('hero_subheadline', "STONE ENERGY INT'L LTD is a Nigerian general contracting and multi-sector supply company providing solutions across oil & gas, construction, healthcare, agro-allied and agroprocessing industries.")) ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-row">
                        <label class="form-label-admin">Primary CTA Button Label</label>
                        <input type="text" name="settings[hero_cta_primary_text]" class="form-control-admin" value="<?= e(setting('hero_cta_primary_text', "REQUEST A QUOTE")) ?>">
                    </div>
                    <div class="form-row">
                        <label class="form-label-admin">Primary CTA Link</label>
                        <input type="text" name="settings[hero_cta_primary_link]" class="form-control-admin" value="<?= e(setting('hero_cta_primary_link', "quote.php")) ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-row">
                        <label class="form-label-admin">Secondary CTA Button Label</label>
                        <input type="text" name="settings[hero_cta_secondary_text]" class="form-control-admin" value="<?= e(setting('hero_cta_secondary_text', "EXPLORE OUR SERVICES")) ?>">
                    </div>
                    <div class="form-row">
                        <label class="form-label-admin">Secondary CTA Link</label>
                        <input type="text" name="settings[hero_cta_secondary_link]" class="form-control-admin" value="<?= e(setting('hero_cta_secondary_link', "services.php")) ?>">
                    </div>
                </div>

                <!-- Hero Background Image -->
                <div class="form-row" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--admin-border);">
                    <label class="form-label-admin">Custom Hero Background Image (Optional)</label>
                    <input type="file" name="hero_image_file" class="form-control-admin" accept=".jpg,.jpeg,.png,.webp">
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Upload a high-resolution dark/industrial image to appear behind the hero section.</span>

                    <?php 
                    $heroImg = setting('hero_image', '');
                    if (!empty($heroImg)): 
                    ?>
                    <div style="margin-top: 12px; display: flex; align-items: center; gap: 16px; background: #f8fafc; border: 1px solid var(--admin-border); padding: 12px; border-radius: 6px;">
                        <img src="<?= upload_url($heroImg) ?>" alt="Hero Preview" style="height: 60px; width: 100px; object-fit: cover; border-radius: 4px;">
                        <div style="flex-grow: 1; font-size: 0.85rem;">
                            <strong>Current Background:</strong> <code><?= e($heroImg) ?></code>
                        </div>
                        <label style="margin: 0; font-size: 0.8rem; color: #dc2626; cursor: pointer;">
                            <input type="checkbox" name="remove_hero_image" value="1"> Remove Image
                        </label>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab 2: Homepage Section Visibility -->
            <div id="tabSections" class="tab-content" style="display: none;">
                <p style="color: var(--admin-text-muted); font-size: 0.9rem; margin-bottom: 20px;">
                    Toggle homepage components on or off according to operational requirements:
                </p>

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <label style="display: flex; align-items: center; gap: 12px; font-weight: 600; cursor: pointer; padding: 12px; background: var(--admin-body-bg); border-radius: 6px;">
                        <input type="checkbox" name="settings[section_hero_enabled]" value="1" <?= Settings::getBool('section_hero_enabled', true) ? 'checked' : '' ?>>
                        <span>Enable Hero Section &amp; Mission Pillars</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 12px; font-weight: 600; cursor: pointer; padding: 12px; background: var(--admin-body-bg); border-radius: 6px;">
                        <input type="checkbox" name="settings[section_services_enabled]" value="1" <?= Settings::getBool('section_services_enabled', true) ? 'checked' : '' ?>>
                        <span>Enable Integrated Multi-Sector Services Grid</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 12px; font-weight: 600; cursor: pointer; padding: 12px; background: var(--admin-body-bg); border-radius: 6px;">
                        <input type="checkbox" name="settings[section_why_choose_enabled]" value="1" <?= Settings::getBool('section_why_choose_enabled', true) ? 'checked' : '' ?>>
                        <span>Enable "Why Choose Stone Energy" Value Pillars</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 12px; font-weight: 600; cursor: pointer; padding: 12px; background: var(--admin-body-bg); border-radius: 6px;">
                        <input type="checkbox" name="settings[section_products_enabled]" value="1" <?= Settings::getBool('section_products_enabled', true) ? 'checked' : '' ?>>
                        <span>Enable Featured Products Catalogue Showcase</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 12px; font-weight: 600; cursor: pointer; padding: 12px; background: var(--admin-body-bg); border-radius: 6px;">
                        <input type="checkbox" name="settings[section_projects_enabled]" value="1" <?= Settings::getBool('section_projects_enabled', true) ? 'checked' : '' ?>>
                        <span>Enable Project Portfolio Showcase</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 12px; font-weight: 600; cursor: pointer; padding: 12px; background: var(--admin-body-bg); border-radius: 6px;">
                        <input type="checkbox" name="settings[section_quote_cta_enabled]" value="1" <?= Settings::getBool('section_quote_cta_enabled', true) ? 'checked' : '' ?>>
                        <span>Enable Fast RFQ Commercial Banner</span>
                    </label>
                </div>
            </div>

            <!-- Tab 3: About Page Content -->
            <div id="tabAbout" class="tab-content" style="display: none;">
                <div class="form-row">
                    <label class="form-label-admin">Company Overview</label>
                    <textarea name="settings[about_overview]" class="form-control-admin" style="min-height: 120px;"><?= e(setting('about_overview')) ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-row">
                        <label class="form-label-admin">Mission Statement</label>
                        <textarea name="settings[about_mission]" class="form-control-admin" style="min-height: 90px;"><?= e(setting('about_mission')) ?></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Vision Statement</label>
                        <textarea name="settings[about_vision]" class="form-control-admin" style="min-height: 90px;"><?= e(setting('about_vision')) ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Core Values (Comma-separated)</label>
                    <input type="text" name="settings[about_core_values]" class="form-control-admin" value="<?= e(setting('about_core_values')) ?>">
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Management Message (Editable placeholder)</label>
                    <textarea name="settings[about_management_message]" class="form-control-admin" style="min-height: 100px;"><?= e(setting('about_management_message')) ?></textarea>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Company History &amp; Growth (Editable placeholder)</label>
                    <textarea name="settings[about_company_history]" class="form-control-admin" style="min-height: 100px;"><?= e(setting('about_company_history')) ?></textarea>
                </div>
            </div>

            <div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--admin-border);">
                <button type="submit" class="btn-admin btn-admin-primary" style="padding: 12px 28px; font-size: 0.95rem;">
                    Update Homepage &amp; About Content &rarr;
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
