<?php
/**
 * STONE ENERGY INT'L LTD - About Us Page
 */
require_once __DIR__ . '/config/config.php';

$pageTitle = "About STONE ENERGY INT'L LTD | General Contractor & Multi-Sector Supply";
$pageDescription = "Learn about STONE ENERGY INT'L LTD, our mission, vision, core values, and corporate governance in Ibadan, Oyo State, Nigeria.";

$companyName     = setting('company_name', "STONE ENERGY INT'L LTD");
$overview        = setting('about_overview', "STONE ENERGY INT'L LTD is an indigenous Nigerian enterprise structured to deliver integrated engineering, multi-sector procurement, construction, and specialized supply solutions.");
$mission         = setting('about_mission', "To provide dependable multi-sector supply chain and general contracting solutions across Nigeria through uncompromising integrity, technical competence, and customer-first execution.");
$vision          = setting('about_vision', "To stand as Nigeria's most trusted multi-sector supply partner and contractor of choice for energy, construction, healthcare, and agro-allied enterprises.");
$coreValues      = setting('about_core_values', "Reliability, Professional Integrity, Quality Assurance, Procurement Transparency, Timely Delivery, Safety First");
$companyHistory  = setting('about_company_history', "[ADD COMPANY HISTORY - Awaiting corporate profile document]");
$mgmtMessage     = setting('about_management_message', "[ADD MANAGEMENT MESSAGE - Awaiting executive address]");
$cacNumber       = setting('cac_number', '[ADD CAC NUMBER]');
$rcNumber        = setting('rc_number', '[ADD RC NUMBER]');
$officeAddress   = setting('office_address', '22, Oyelude Layout, Aba Alfa, Ojo, Ibadan, Oyo State, Nigeria.');

include INCLUDES_PATH . 'header.php';
?>

<div class="section-dark" style="padding: 60px 0; border-bottom: 3px solid var(--color-accent-500);">
    <div class="container">
        <span class="section-tag section-tag-light">CORPORATE PROFILE</span>
        <h1 style="font-size: 2.8rem; color: #fff; margin-bottom: 12px;">About Our Organization</h1>
        <p style="color: #cbd5e1; max-width: 720px; font-size: 1.1rem; margin-bottom: 0;">
            A general contracting and multi-sector supply company founded on principles of technical competence, transparent procurement, and disciplined project delivery.
        </p>
    </div>
</div>

<!-- 1. Corporate Overview & Governance -->
<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 48px; align-items: start;">
            <div>
                <span class="section-tag">WHO WE ARE</span>
                <h2 class="section-title">Integrated Solutions for Complex Commercial Needs</h2>
                <div style="font-size: 1.05rem; line-height: 1.7; color: var(--color-dark-700);">
                    <p><?= nl2br(e($overview)) ?></p>
                    <p>
                        With operational coordination centered in Ibadan, Oyo State, <strong><?= e($companyName) ?></strong> operates at the nexus of multiple key economic sectors. Whether supplying vital industrial equipment to energy operators, executing civil structural upgrades, outfitting healthcare institutions with medical hardware, or distributing agricultural inputs to agroprocessors, we uphold singular accountability.
                    </p>
                </div>

                <div style="margin-top: 32px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div style="background: var(--color-gray-100); padding: 20px; border-radius: var(--radius-md); border-left: 4px solid var(--color-accent-500);">
                        <h4 style="font-size: 1rem; margin-bottom: 6px;">Corporate Identity</h4>
                        <div style="font-size: 0.88rem; color: var(--color-dark-600);">
                            Registration: <strong><?= e($cacNumber) ?></strong><br>
                            RC Number: <strong><?= e($rcNumber) ?></strong>
                        </div>
                    </div>
                    <div style="background: var(--color-gray-100); padding: 20px; border-radius: var(--radius-md); border-left: 4px solid var(--color-primary-900);">
                        <h4 style="font-size: 1rem; margin-bottom: 6px;">Headquarters</h4>
                        <div style="font-size: 0.88rem; color: var(--color-dark-600);">
                            <?= e($officeAddress) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Card: Mission & Vision -->
            <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-lg); padding: 36px; box-shadow: var(--shadow-md);">
                <div style="margin-bottom: 30px;">
                    <div style="display: inline-flex; align-items: center; gap: 8px; color: var(--color-accent-500); font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="12 8 8 12 12 16 16 12 12 8"></polygon></svg>
                        OUR MISSION
                    </div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 10px;">Driven by Purpose</h3>
                    <p style="font-size: 0.95rem; color: var(--color-dark-600); line-height: 1.6; margin-bottom: 0;">
                        <?= e($mission) ?>
                    </p>
                </div>

                <div style="border-top: 1px solid var(--color-gray-200); padding-top: 24px; margin-bottom: 30px;">
                    <div style="display: inline-flex; align-items: center; gap: 8px; color: var(--color-accent-500); font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        OUR VISION
                    </div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 10px;">Aiming for Excellence</h3>
                    <p style="font-size: 0.95rem; color: var(--color-dark-600); line-height: 1.6; margin-bottom: 0;">
                        <?= e($vision) ?>
                    </p>
                </div>

                <div style="border-top: 1px solid var(--color-gray-200); padding-top: 24px;">
                    <div style="display: inline-flex; align-items: center; gap: 8px; color: var(--color-accent-500); font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                        CORE VALUES
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;">
                        <?php foreach (explode(',', $coreValues) as $val): ?>
                            <span style="background: var(--color-accent-100); color: var(--color-accent-600); padding: 4px 12px; border-radius: var(--radius-full); font-size: 0.8rem; font-weight: 700;">
                                <?= e(trim($val)) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Management Message & Corporate History (Placeholders without fake fabrication) -->
<section class="section section-gray">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 36px;">
            <div style="background: var(--color-white); padding: 36px; border-radius: var(--radius-md); border: 1px solid var(--color-gray-200);">
                <span class="section-tag">LEADERSHIP STATEMENT</span>
                <h3 style="font-size: 1.45rem; margin-bottom: 16px;">Management Message</h3>
                <div style="font-size: 0.95rem; color: var(--color-dark-600); line-height: 1.7;">
                    <?= nl2br(e($mgmtMessage)) ?>
                </div>
                <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--color-gray-100); font-size: 0.85rem; color: var(--color-gray-400);">
                    <em>Official statement editable via Admin CMS Site Settings.</em>
                </div>
            </div>

            <div style="background: var(--color-white); padding: 36px; border-radius: var(--radius-md); border: 1px solid var(--color-gray-200);">
                <span class="section-tag">OUR JOURNEY</span>
                <h3 style="font-size: 1.45rem; margin-bottom: 16px;">Corporate History &amp; Growth</h3>
                <div style="font-size: 0.95rem; color: var(--color-dark-600); line-height: 1.7;">
                    <?= nl2br(e($companyHistory)) ?>
                </div>
                <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--color-gray-100); font-size: 0.85rem; color: var(--color-gray-400);">
                    <em>Corporate milestones and registration history editable via Admin CMS.</em>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Direct RFQ Call to Action -->
<section class="section">
    <div class="container" style="text-align: center; max-width: 780px;">
        <span class="section-tag">PARTNER WITH US</span>
        <h2 class="section-title">Ready to Discuss Your Project Requirements?</h2>
        <p class="section-subtitle" style="margin-bottom: 30px;">
            Whether you require an extensive bill of materials, turnkey building contracting, or clinical equipment sourcing, our team is equipped to assist.
        </p>
        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <a href="<?= url('quote.php') ?>" class="btn btn-primary btn-lg">REQUEST A QUOTE</a>
            <a href="<?= url('contact.php') ?>" class="btn btn-navy btn-lg">CONTACT HEADQUARTERS</a>
        </div>
    </div>
</section>

<?php include INCLUDES_PATH . 'footer.php'; ?>
