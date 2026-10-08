<?php
/**
 * STONE ENERGY INT'L LTD - Main Frontend Footer
 */
$companyName    = setting('company_name', "STONE ENERGY INT'L LTD");
$tagline        = setting('tagline', "GENERAL CONTRACTOR & MULTI-SECTOR SUPPLY SOLUTIONS");
$motto          = setting('motto', "Building. Supplying. Delivering.");
$phonePrimary   = setting('phone_primary', '08037745881');
$phoneSecondary = setting('phone_secondary', '08084949840');
$emailPrimary   = setting('email_primary', '[ADD COMPANY EMAIL]');
$officeAddress  = setting('office_address', '22, Oyelude Layout, Aba Alfa, Ojo, Ibadan, Oyo State, Nigeria.');
$cacNumber      = setting('cac_number', '[ADD CAC NUMBER]');
$whatsappNum    = setting('whatsapp_number', '2348037745881');
$whatsappMsg    = setting('whatsapp_message', 'Hello Stone Energy Int\'l Ltd, I would like to inquire about your services.');
$whatsappActive = Settings::getBool('whatsapp_enabled', true);
$currentYear    = date('Y');
?>
    </main>

    <!-- Floating WhatsApp Action -->
    <?php if ($whatsappActive && !empty($whatsappNum)): ?>
    <a href="https://wa.me/<?= e($whatsappNum) ?>?text=<?= urlencode($whatsappMsg) ?>" 
       class="floating-whatsapp" 
       target="_blank" 
       rel="noopener noreferrer" 
       title="Chat with our Procurement Team on WhatsApp">
        <svg viewBox="0 0 24 24">
            <path d="M12.031 2C6.495 2 2 6.496 2 12.033c0 1.98.577 3.827 1.575 5.385L2.348 22l4.743-1.229a10.007 10.007 0 0 0 4.94 1.265h.004c5.536 0 10.031-4.496 10.031-10.033C22.066 6.496 17.571 2 12.031 2zm5.836 14.199c-.244.688-1.42 1.309-1.97 1.393-.51.077-1.168.109-3.791-.983-3.354-1.397-5.512-4.832-5.68-5.056-.168-.224-1.36-1.81-1.36-3.453 0-1.642.862-2.451 1.168-2.787.306-.336.669-.42.892-.42.224 0 .448.002.645.012.208.01.488-.078.763.582.285.688.97 2.37.1054 2.542.084.172.14.374.028.598-.112.224-.168.364-.336.56-.168.196-.353.438-.504.588-.168.168-.344.351-.148.688.196.336.872 1.436 1.87 2.325 1.284 1.144 2.365 1.498 2.701 1.666.336.168.532.14.73-.084.196-.224.84-0.98.1064-1.316.224-.336.448-.28.756-.168.308.112 1.956.923 2.292 1.091.336.168.56.252.644.392.084.14.084.812-.16 1.5z"/>
        </svg>
        <span>Chat on WhatsApp</span>
    </a>
    <?php endif; ?>

    <!-- Corporate Footer -->
    <footer class="site-footer">
        <div class="container footer-grid">
            <!-- Company Overview & Positioning -->
            <div class="footer-about">
                <img src="<?= asset('images/logo-light.svg') ?>" alt="<?= e($companyName) ?> Logo Light">
                <p>
                    <strong><?= e($companyName) ?></strong> is a premier Nigerian general contracting and multi-sector supply solutions partner. We deliver procurement reliability and construction competence across oil & gas, building infrastructure, medical healthcare, and agribusiness.
                </p>
                <p style="font-size: 0.85rem; color: #cbd5e1;">
                    <em>"<?= e($motto) ?>"</em>
                </p>
                <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 10px;">
                    <span>Registration: <strong><?= e($cacNumber) ?></strong></span>
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="footer-nav">
                <h4 class="footer-title">Corporate</h4>
                <ul class="footer-links">
                    <li><a href="<?= url('') ?>">Home</a></li>
                    <li><a href="<?= url('about.php') ?>">About Our Firm</a></li>
                    <li><a href="<?= url('services.php') ?>">Services Directory</a></li>
                    <li><a href="<?= url('products.php') ?>">Product Catalogue</a></li>
                    <li><a href="<?= url('projects.php') ?>">Project Portfolio</a></li>
                    <li><a href="<?= url('industries.php') ?>">Industries Served</a></li>
                    <li><a href="<?= url('blog.php') ?>">News &amp; Insights</a></li>
                    <li><a href="<?= url('track.php') ?>">Track RFQ / Tender</a></li>
                    <li><a href="<?= url('contact.php') ?>">Contact Headquarters</a></li>
                </ul>
            </div>

            <!-- Core Business Areas -->
            <div class="footer-sectors">
                <h4 class="footer-title">Business Areas</h4>
                <ul class="footer-links">
                    <li><a href="<?= url('services.php?slug=oil-and-gas-supply') ?>">Oil &amp; Gas Supply</a></li>
                    <li><a href="<?= url('services.php?slug=building-construction') ?>">Building Construction</a></li>
                    <li><a href="<?= url('services.php?slug=hospital-equipment-consumables-supply') ?>">Hospital Equipment</a></li>
                    <li><a href="<?= url('services.php?slug=agro-allied') ?>">Agro-Allied</a></li>
                    <li><a href="<?= url('services.php?slug=agroprocessing') ?>">Agroprocessing</a></li>
                    <li><a href="<?= url('services.php?slug=general-contracting') ?>">General Contracting</a></li>
                </ul>
            </div>

            <!-- Headquarters & Contact Information -->
            <div class="footer-contact">
                <h4 class="footer-title">Headquarters</h4>
                <ul>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span><?= e($officeAddress) ?></span>
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <div>
                            <a href="tel:<?= e($phonePrimary) ?>" style="color: #cbd5e1;"><?= e($phonePrimary) ?></a><br>
                            <a href="tel:<?= e($phoneSecondary) ?>" style="color: #cbd5e1;"><?= e($phoneSecondary) ?></a>
                        </div>
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <span><?= e($emailPrimary) ?></span>
                    </li>
                </ul>
                <div style="margin-top: 18px;">
                    <a href="<?= url('quote.php') ?>" class="btn btn-outline-gold btn-sm">
                        SUBMIT AN RFQ
                    </a>
                </div>
            </div>
        </div>

        <!-- Copyright & Legal -->
        <div class="container footer-bottom">
            <div>
                &copy; <?= $currentYear ?> <?= e($companyName) ?>. All Rights Reserved. &bull; Designed by <a href="https://cloudcurrentng.com" target="_blank" rel="noopener" style="color: var(--color-accent-400); text-decoration: none;">cloudcurrentng.com</a>
            </div>
            <div class="footer-legal-links">
                <a href="<?= url('privacy.php') ?>">Privacy Policy</a>
                <a href="<?= url('terms.php') ?>">Terms &amp; Conditions</a>
                <a href="<?= url('cookie-policy.php') ?>">Cookie Policy</a>
                <a href="<?= url('disclaimer.php') ?>">Disclaimer</a>
                <a href="<?= admin_url('login.php') ?>" style="opacity: 0.6;">Admin Portal</a>
            </div>
        </div>
    </footer>

    <!-- RFQ Quick Trigger Modal -->
    <?php include INCLUDES_PATH . 'rfq-modal.php'; ?>

    <!-- Vanilla JavaScript Bundle -->
    <script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
