<?php
/**
 * STONE ENERGY INT'L LTD - Corporate Site Settings
 */
$requiredPermission = 'settings.manage';
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Site Settings | STONE ENERGY INT'L LTD CMS";
$adminSection = "Corporate Settings";

// Save Settings Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();

    $settingsToSave = $_POST['settings'] ?? [];

    // Handle Primary Logo Upload
    if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
        $resLogo = Uploader::upload($_FILES['logo_file'], 'Primary Corporate Header Logo');
        if ($resLogo['success']) {
            $settingsToSave['logo_url'] = $resLogo['file_path'];
        } else {
            set_flash('error', 'Primary logo upload error: ' . $resLogo['error']);
        }
    }

    // Handle Light / Footer Logo Upload
    if (isset($_FILES['logo_light_file']) && $_FILES['logo_light_file']['error'] === UPLOAD_ERR_OK) {
        $resLight = Uploader::upload($_FILES['logo_light_file'], 'Dark Background / Light Logo');
        if ($resLight['success']) {
            $settingsToSave['logo_light_url'] = $resLight['file_path'];
        } else {
            set_flash('error', 'Light logo upload error: ' . $resLight['error']);
        }
    }

    // Handle Favicon Upload
    if (isset($_FILES['favicon_file']) && $_FILES['favicon_file']['error'] === UPLOAD_ERR_OK) {
        $resFav = Uploader::upload($_FILES['favicon_file'], 'Browser Favicon');
        if ($resFav['success']) {
            $settingsToSave['favicon_url'] = $resFav['file_path'];
        } else {
            set_flash('error', 'Favicon upload error: ' . $resFav['error']);
        }
    }

    foreach ($settingsToSave as $key => $val) {
        $val = trim($val);
        Settings::set($key, $val);
    }

    // Checkbox toggles (if unchecked in form, set to '0')
    $checkboxes = ['whatsapp_enabled', 'smtp_enabled'];
    foreach ($checkboxes as $cb) {
        if (!isset($settingsToSave[$cb])) {
            Settings::set($cb, '0');
        }
    }

    Audit::log('update_settings', 'settings', null, "Updated corporate site settings & logo assets");
    set_flash('success', 'Site settings and logo assets updated successfully.');
    header('Location: ' . admin_url('settings.php'));
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>Corporate &amp; Global System Settings</h3>
    </div>
    <div class="admin-card-body">
        <!-- Navigation Tabs -->
        <div class="tabs-nav">
            <button type="button" class="tab-link active" data-tab="tabGeneral">General &amp; Legal</button>
            <button type="button" class="tab-link" data-tab="tabLogos">Logo &amp; Brand Assets</button>
            <button type="button" class="tab-link" data-tab="tabContact">Headquarters &amp; Contact</button>
            <button type="button" class="tab-link" data-tab="tabWhatsapp">WhatsApp Widget</button>
            <button type="button" class="tab-link" data-tab="tabSocial">Social Networks</button>
            <button type="button" class="tab-link" data-tab="tabSmtp">SMTP Email Dispatch</button>
            <button type="button" class="tab-link" data-tab="tabAnalytics">SEO &amp; Analytics</button>
        </div>

        <form action="<?= admin_url('settings.php') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <!-- Tab 1: General & Legal -->
            <div id="tabGeneral" class="tab-content" style="display: block;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-row">
                        <label class="form-label-admin">Company Legal Name</label>
                        <input type="text" name="settings[company_name]" class="form-control-admin" value="<?= e(setting('company_name', "STONE ENERGY INT'L LTD")) ?>">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Brand Motto</label>
                        <input type="text" name="settings[motto]" class="form-control-admin" value="<?= e(setting('motto', "Building. Supplying. Delivering.")) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Corporate Tagline &amp; Positioning</label>
                    <input type="text" name="settings[tagline]" class="form-control-admin" value="<?= e(setting('tagline', "GENERAL CONTRACTOR & MULTI-SECTOR SUPPLY SOLUTIONS")) ?>">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-row">
                        <label class="form-label-admin">CAC Registration Number Placeholder</label>
                        <input type="text" name="settings[cac_number]" class="form-control-admin" value="<?= e(setting('cac_number', '[ADD CAC NUMBER]')) ?>">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">RC Number Placeholder</label>
                        <input type="text" name="settings[rc_number]" class="form-control-admin" value="<?= e(setting('rc_number', '[ADD RC NUMBER]')) ?>">
                    </div>
                </div>

                <!-- Logo Quick Link Callout -->
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px 20px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="background: #ffffff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 4px 8px; display: flex; align-items: center;">
                            <img src="<?= upload_url(setting('logo_url', 'assets/images/logo.svg')) ?>" alt="Logo" style="height: 32px; max-width: 120px; object-fit: contain;">
                        </div>
                        <div>
                            <strong style="color: #1e40af; font-size: 0.92rem;">Looking to upload or replace your company logo?</strong>
                            <p style="margin: 2px 0 0 0; color: #3b82f6; font-size: 0.82rem;">Upload your primary header logo, dark footer logo, or browser favicon directly.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-admin btn-admin-primary" onclick="document.querySelector('[data-tab=\'tabLogos\']').click();" style="font-size: 0.82rem;">
                        Go to Logo Upload &rarr;
                    </button>
                </div>
            </div>

            <!-- Tab 2: Brand Logo & Identity Assets -->
            <div id="tabLogos" class="tab-content" style="display: none;">
                <div style="background: #f8fafc; border: 1px solid var(--admin-border); border-radius: 8px; padding: 18px 22px; margin-bottom: 24px;">
                    <h4 style="margin: 0 0 6px 0; color: var(--admin-text); font-size: 1.05rem;">Corporate Visual Identity &amp; Logo Assets</h4>
                    <p style="margin: 0; color: var(--admin-text-muted); font-size: 0.88rem;">
                        Upload your official corporate logos and browser favicon below. All uploads automatically update the public website header, footer, client RFQ tracking portal, administrative login, and emails.
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                    <!-- Primary Logo (Light Backgrounds) -->
                    <div style="border: 1px solid var(--admin-border); border-radius: 8px; padding: 20px; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <label class="form-label-admin" style="font-weight: 700; margin-bottom: 0;">1. Primary Header Logo (Light Backgrounds)</label>
                                <span style="font-size: 0.75rem; background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-weight: 600;">Website Header &amp; Login</span>
                            </div>
                            
                            <p style="font-size: 0.82rem; color: var(--admin-text-muted); margin-bottom: 14px;">
                                Displayed on white/light backgrounds across navigation headers, quotes, and portals.
                            </p>

                            <!-- Current Preview Box -->
                            <div style="background: #ffffff; border: 2px dashed #cbd5e1; border-radius: 6px; padding: 20px; text-align: center; margin-bottom: 16px; min-height: 100px; display: flex; align-items: center; justify-content: center;">
                                <img src="<?= upload_url(setting('logo_url', 'assets/images/logo.svg')) ?>" 
                                     id="previewPrimaryLogo" 
                                     alt="Current Primary Logo" 
                                     style="max-height: 70px; max-width: 100%; object-fit: contain;">
                            </div>

                            <div class="form-row" style="margin-bottom: 12px;">
                                <label class="form-label-admin">Upload New Primary Logo File</label>
                                <input type="file" name="logo_file" accept=".png,.jpg,.jpeg,.webp,.svg" class="form-control-admin" onchange="previewImage(this, 'previewPrimaryLogo')">
                                <small style="color: var(--admin-text-muted); font-size: 0.75rem;">Supported: PNG (transparent background recommended), SVG, WEBP, JPG. Max 10MB.</small>
                            </div>
                        </div>

                        <div class="form-row" style="margin-bottom: 0; padding-top: 10px; border-top: 1px solid #f1f5f9;">
                            <label class="form-label-admin" style="font-size: 0.78rem;">Or Asset Path / URL</label>
                            <input type="text" name="settings[logo_url]" class="form-control-admin" style="font-size: 0.82rem;" value="<?= e(setting('logo_url', 'assets/images/logo.svg')) ?>">
                        </div>
                    </div>

                    <!-- Light / White Logo (Dark Backgrounds) -->
                    <div style="border: 1px solid var(--admin-border); border-radius: 8px; padding: 20px; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <label class="form-label-admin" style="font-weight: 700; margin-bottom: 0;">2. Footer &amp; Dark Mode Logo (Dark Backgrounds)</label>
                                <span style="font-size: 0.75rem; background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 4px; font-weight: 600;">Dark Footer &amp; Sidebar</span>
                            </div>
                            
                            <p style="font-size: 0.82rem; color: var(--admin-text-muted); margin-bottom: 14px;">
                                Displayed on dark corporate footer and admin navigation sidebar for high contrast.
                            </p>

                            <!-- Current Preview Box (Dark Background) -->
                            <div style="background: #0a192f; border: 2px dashed #334155; border-radius: 6px; padding: 20px; text-align: center; margin-bottom: 16px; min-height: 100px; display: flex; align-items: center; justify-content: center;">
                                <img src="<?= upload_url(setting('logo_light_url', setting('logo_url', 'assets/images/logo-light.svg'))) ?>" 
                                     id="previewLightLogo" 
                                     alt="Current Light Logo" 
                                     style="max-height: 70px; max-width: 100%; object-fit: contain;">
                            </div>

                            <div class="form-row" style="margin-bottom: 12px;">
                                <label class="form-label-admin">Upload New Light/White Logo</label>
                                <input type="file" name="logo_light_file" accept=".png,.jpg,.jpeg,.webp,.svg" class="form-control-admin" onchange="previewImage(this, 'previewLightLogo')">
                                <small style="color: var(--admin-text-muted); font-size: 0.75rem;">Supported: PNG (white/transparent), SVG, WEBP. Max 10MB.</small>
                            </div>
                        </div>

                        <div class="form-row" style="margin-bottom: 0; padding-top: 10px; border-top: 1px solid #f1f5f9;">
                            <label class="form-label-admin" style="font-size: 0.78rem;">Or Asset Path / URL</label>
                            <input type="text" name="settings[logo_light_url]" class="form-control-admin" style="font-size: 0.82rem;" value="<?= e(setting('logo_light_url', 'assets/images/logo-light.svg')) ?>">
                        </div>
                    </div>
                </div>

                <!-- Favicon Card -->
                <div style="border: 1px solid var(--admin-border); border-radius: 8px; padding: 20px; background: #ffffff; max-width: 650px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <label class="form-label-admin" style="font-weight: 700; margin-bottom: 0;">3. Browser Tab Favicon</label>
                        <span style="font-size: 0.75rem; background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-weight: 600;">Tab Icon</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 16px;">
                        <div style="width: 56px; height: 56px; border: 1px solid var(--admin-border); border-radius: 8px; background: #f8fafc; display: flex; align-items: center; justify-content: center;">
                            <img src="<?= upload_url(setting('favicon_url', 'assets/images/favicon.svg')) ?>" 
                                 id="previewFavicon" 
                                 alt="Favicon Preview" 
                                 style="width: 32px; height: 32px; object-fit: contain;">
                        </div>
                        <div style="flex-grow: 1;">
                            <input type="file" name="favicon_file" accept=".ico,.png,.svg" class="form-control-admin" onchange="previewImage(this, 'previewFavicon')">
                            <small style="color: var(--admin-text-muted); font-size: 0.75rem;">Supported: ICO, PNG, SVG (Recommended size: 32x32 or 64x64 square).</small>
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom: 0;">
                        <label class="form-label-admin" style="font-size: 0.78rem;">Or Asset Path / URL</label>
                        <input type="text" name="settings[favicon_url]" class="form-control-admin" style="font-size: 0.82rem;" value="<?= e(setting('favicon_url', 'assets/images/favicon.svg')) ?>">
                    </div>
                </div>
            </div>

            <!-- Tab 2: Headquarters & Contact -->
            <div id="tabContact" class="tab-content" style="display: none;">
                <div class="form-row">
                    <label class="form-label-admin">Official Headquarters Physical Address</label>
                    <textarea name="settings[office_address]" class="form-control-admin" style="min-height: 80px;"><?= e(setting('office_address', "22, Oyelude Layout, Aba Alfa, Ojo, Ibadan, Oyo State, Nigeria.")) ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-row">
                        <label class="form-label-admin">Primary Phone Hotline</label>
                        <input type="text" name="settings[phone_primary]" class="form-control-admin" value="<?= e(setting('phone_primary', '08037745881')) ?>">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Secondary Phone Hotline</label>
                        <input type="text" name="settings[phone_secondary]" class="form-control-admin" value="<?= e(setting('phone_secondary', '08084949840')) ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-row">
                        <label class="form-label-admin">Primary Corporate Email</label>
                        <input type="text" name="settings[email_primary]" class="form-control-admin" value="<?= e(setting('email_primary', '[ADD COMPANY EMAIL]')) ?>">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Support / RFQ Email</label>
                        <input type="text" name="settings[email_support]" class="form-control-admin" value="<?= e(setting('email_support', '[ADD SUPPORT EMAIL]')) ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-row">
                        <label class="form-label-admin">Base City &amp; State</label>
                        <input type="text" name="settings[city]" class="form-control-admin" value="<?= e(setting('city', 'Ibadan')) ?>">
                    </div>
                    <div class="form-row">
                        <label class="form-label-admin">Official Business Hours</label>
                        <input type="text" name="settings[business_hours]" class="form-control-admin" value="<?= e(setting('business_hours', 'Monday - Friday: 8:00 AM - 5:00 PM')) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Google Maps Embed Code / Iframe or Embed URL</label>
                    <textarea name="settings[google_maps_embed]" class="form-control-admin" style="min-height: 80px;" placeholder="<iframe src=&quot;https://www.google.com/maps/embed?...&quot; ...></iframe> or https://maps.google.com/..."><?= e(setting('google_maps_embed', '')) ?></textarea>
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Paste the Google Maps &lt;iframe&gt; embed code or embed URL for the physical headquarters in Ibadan.</span>
                </div>
            </div>

            <!-- Tab 3: WhatsApp Widget -->
            <div id="tabWhatsapp" class="tab-content" style="display: none;">
                <div class="form-row">
                    <label style="display: flex; align-items: center; gap: 10px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="settings[whatsapp_enabled]" value="1" <?= Settings::getBool('whatsapp_enabled', true) ? 'checked' : '' ?>>
                        <span>Enable Floating WhatsApp Live Button on Public Website</span>
                    </label>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">WhatsApp Number (International format without '+' sign)</label>
                    <input type="text" name="settings[whatsapp_number]" class="form-control-admin" value="<?= e(setting('whatsapp_number', '2348037745881')) ?>" placeholder="2348037745881">
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Example: 2348037745881</span>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Default Pre-filled WhatsApp Greeting Message</label>
                    <textarea name="settings[whatsapp_message]" class="form-control-admin" style="min-height: 80px;"><?= e(setting('whatsapp_message', "Hello Stone Energy Int'l Ltd, I would like to inquire about your general contracting and supply solutions.")) ?></textarea>
                </div>
            </div>

            <!-- Tab 4: Social Networks -->
            <div id="tabSocial" class="tab-content" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-row">
                        <label class="form-label-admin">LinkedIn Company Profile</label>
                        <input type="text" name="settings[social_linkedin]" class="form-control-admin" value="<?= e(setting('social_linkedin')) ?>" placeholder="https://linkedin.com/company/...">
                    </div>
                    <div class="form-row">
                        <label class="form-label-admin">Facebook Page URL</label>
                        <input type="text" name="settings[social_facebook]" class="form-control-admin" value="<?= e(setting('social_facebook')) ?>" placeholder="https://facebook.com/...">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-row">
                        <label class="form-label-admin">X / Twitter Handle or URL</label>
                        <input type="text" name="settings[social_twitter]" class="form-control-admin" value="<?= e(setting('social_twitter')) ?>" placeholder="https://x.com/...">
                    </div>
                    <div class="form-row">
                        <label class="form-label-admin">Instagram Profile</label>
                        <input type="text" name="settings[social_instagram]" class="form-control-admin" value="<?= e(setting('social_instagram')) ?>" placeholder="https://instagram.com/...">
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">YouTube Channel</label>
                    <input type="text" name="settings[social_youtube]" class="form-control-admin" value="<?= e(setting('social_youtube')) ?>" placeholder="https://youtube.com/...">
                </div>
            </div>

            <!-- Tab 5: SMTP Dispatch -->
            <div id="tabSmtp" class="tab-content" style="display: none;">
                <div class="form-row">
                    <label style="display: flex; align-items: center; gap: 10px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="settings[smtp_enabled]" value="1" <?= Settings::getBool('smtp_enabled', false) ? 'checked' : '' ?>>
                        <span>Enable Live Socket SMTP Dispatch (Falls back to PHP mail when unchecked)</span>
                    </label>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                    <div class="form-row">
                        <label class="form-label-admin">SMTP Server Host</label>
                        <input type="text" name="settings[smtp_host]" class="form-control-admin" value="<?= e(setting('smtp_host', 'localhost')) ?>" placeholder="mail.stoneenergyintl.com">
                    </div>
                    <div class="form-row">
                        <label class="form-label-admin">SMTP Port</label>
                        <input type="number" name="settings[smtp_port]" class="form-control-admin" value="<?= e(setting('smtp_port', '587')) ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-row">
                        <label class="form-label-admin">SMTP Username</label>
                        <input type="text" name="settings[smtp_username]" class="form-control-admin" value="<?= e(setting('smtp_username')) ?>">
                    </div>
                    <div class="form-row">
                        <label class="form-label-admin">SMTP Password</label>
                        <input type="password" name="settings[smtp_password]" class="form-control-admin" value="<?= e(setting('smtp_password')) ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                    <div class="form-row">
                        <label class="form-label-admin">Encryption</label>
                        <select name="settings[smtp_encryption]" class="form-control-admin">
                            <option value="tls" <?= (setting('smtp_encryption') === 'tls') ? 'selected' : '' ?>>TLS (Port 587)</option>
                            <option value="ssl" <?= (setting('smtp_encryption') === 'ssl') ? 'selected' : '' ?>>SSL (Port 465)</option>
                            <option value="none" <?= (setting('smtp_encryption') === 'none') ? 'selected' : '' ?>>None (Port 25)</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label class="form-label-admin">From Email Address</label>
                        <input type="email" name="settings[smtp_from_email]" class="form-control-admin" value="<?= e(setting('smtp_from_email', 'noreply@stoneenergyintl.com')) ?>">
                    </div>
                    <div class="form-row">
                        <label class="form-label-admin">From Display Name</label>
                        <input type="text" name="settings[smtp_from_name]" class="form-control-admin" value="<?= e(setting('smtp_from_name', "STONE ENERGY INT'L LTD")) ?>">
                    </div>
                </div>
            </div>

            <!-- Tab 6: SEO & Analytics -->
            <div id="tabAnalytics" class="tab-content" style="display: none;">
                <div class="form-row">
                    <label class="form-label-admin">Google Analytics 4 Measurement ID</label>
                    <input type="text" name="settings[analytics_ga_id]" class="form-control-admin" value="<?= e(setting('analytics_ga_id')) ?>" placeholder="G-XXXXXXXXXX">
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Google Tag Manager Container ID</label>
                    <input type="text" name="settings[analytics_gtm_id]" class="form-control-admin" value="<?= e(setting('analytics_gtm_id')) ?>" placeholder="GTM-XXXXXX">
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Google Search Console Verification Tag</label>
                    <input type="text" name="settings[analytics_gsc_tag]" class="form-control-admin" value="<?= e(setting('analytics_gsc_tag')) ?>" placeholder="google-site-verification token">
                </div>
            </div>

            <div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--admin-border);">
                <button type="submit" class="btn-admin btn-admin-primary" style="padding: 12px 28px; font-size: 0.95rem;">
                    Save All Site Settings &rarr;
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input, targetId) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewEl = document.getElementById(targetId);
            if (previewEl) {
                previewEl.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.replace('#', '') || (new URLSearchParams(window.location.search)).get('tab');
    if (hash) {
        const targetBtn = document.querySelector(`.tab-link[data-tab="${hash}"]`);
        if (targetBtn) {
            targetBtn.click();
        }
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
