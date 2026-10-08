<?php
/**
 * STONE ENERGY INT'L LTD - Industries Served
 */
require_once __DIR__ . '/config/config.php';

$pageTitle = "Industries Served | STONE ENERGY INT'L LTD";
$pageDescription = "STONE ENERGY INT'L LTD delivers tailored procurement and general contracting solutions across energy, construction, healthcare, and agribusiness sectors in Nigeria.";

include INCLUDES_PATH . 'header.php';
?>

<div class="section-dark" style="padding: 60px 0; border-bottom: 3px solid var(--color-accent-500);">
    <div class="container">
        <span class="section-tag section-tag-light">SECTOR ALIGNMENT</span>
        <h1 style="font-size: 2.8rem; color: #fff; margin-bottom: 12px;">Industries We Serve</h1>
        <p style="color: #cbd5e1; max-width: 720px; font-size: 1.1rem; margin-bottom: 0;">
            Tailored supply chain networks and contracting capabilities engineered to meet the stringent demands of core economic sectors.
        </p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="services-grid" style="grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));">
            <!-- 1. Oil & Gas -->
            <div class="service-card">
                <div class="service-icon-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18"></path><path d="M15 10h4a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-4"></path><path d="M7 11h4"></path></svg>
                </div>
                <h3>Oil &amp; Gas / Energy</h3>
                <p>
                    Downstream operations, depot facilities, and industrial users require reliable sourcing of pipeline valves, industrial fittings, flow equipment, and safety consumables. We handle procurement logistics with complete documentation.
                </p>
                <div style="margin-top: auto; padding-top: 14px; border-top: 1px solid var(--color-gray-100);">
                    <a href="<?= url('services.php?slug=oil-and-gas-supply') ?>" class="service-link">View Procurement Scope &rarr;</a>
                </div>
            </div>

            <!-- 2. Building Construction -->
            <div class="service-card">
                <div class="service-icon-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="9" y1="22" x2="9" y2="22.01"></line><line x1="15" y1="22" x2="15" y2="22.01"></line></svg>
                </div>
                <h3>Civil &amp; Commercial Construction</h3>
                <p>
                    From multi-story commercial real estate to civil infrastructure, we act as a principal general contractor or bulk materials supplier, coordinating reinforcing steel, cement, aggregates, and site engineering oversight.
                </p>
                <div style="margin-top: auto; padding-top: 14px; border-top: 1px solid var(--color-gray-100);">
                    <a href="<?= url('services.php?slug=building-construction') ?>" class="service-link">View Contracting Scope &rarr;</a>
                </div>
            </div>

            <!-- 3. Healthcare -->
            <div class="service-card">
                <div class="service-icon-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                </div>
                <h3>Hospitals &amp; Healthcare Facilities</h3>
                <p>
                    Supplying public hospitals, private clinics, and medical centers with durable ward furniture, ICU beds, diagnostic instruments, and sterile consumables to keep frontline patient care running smoothly.
                </p>
                <div style="margin-top: auto; padding-top: 14px; border-top: 1px solid var(--color-gray-100);">
                    <a href="<?= url('services.php?slug=hospital-equipment-consumables-supply') ?>" class="service-link">View Medical Scope &rarr;</a>
                </div>
            </div>

            <!-- 4. Agriculture -->
            <div class="service-card">
                <div class="service-icon-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 20h10"></path><path d="M10 20c5.5-2.5.8-6.4 3-10"></path><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"></path></svg>
                </div>
                <h3>Agro-Allied &amp; Farm Clusters</h3>
                <p>
                    Procuring high-grade agricultural inputs, fertilizers, certified seed stocks, and coordinating bulk crop distribution for commercial farming enterprises and regional aggregators.
                </p>
                <div style="margin-top: auto; padding-top: 14px; border-top: 1px solid var(--color-gray-100);">
                    <a href="<?= url('services.php?slug=agro-allied') ?>" class="service-link">View Agro Scope &rarr;</a>
                </div>
            </div>

            <!-- 5. Agroprocessing -->
            <div class="service-card">
                <div class="service-icon-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                </div>
                <h3>Agroprocessing &amp; Milling</h3>
                <p>
                    Supporting cassava processors, grain millers, and food processing lines with durable processing machinery, replacement spare parts, and consistent raw material supply streams.
                </p>
                <div style="margin-top: auto; padding-top: 14px; border-top: 1px solid var(--color-gray-100);">
                    <a href="<?= url('services.php?slug=agroprocessing') ?>" class="service-link">View Processing Scope &rarr;</a>
                </div>
            </div>

            <!-- 6. General Institutional & Corporate -->
            <div class="service-card">
                <div class="service-icon-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                </div>
                <h3>Corporate &amp; Public Contracting</h3>
                <p>
                    Executing consolidated tenders, corporate office remodeling, specialized hardware procurement packages, and facility maintenance contracts with structured reporting.
                </p>
                <div style="margin-top: auto; padding-top: 14px; border-top: 1px solid var(--color-gray-100);">
                    <a href="<?= url('services.php?slug=general-contracting') ?>" class="service-link">View Contracting Scope &rarr;</a>
                </div>
            </div>
        </div>

        <div class="rfq-feature-banner" style="margin-top: 50px;">
            <div>
                <h3 style="color: #fff; font-size: 1.6rem; margin-bottom: 8px;">Explore Procurement Partnerships</h3>
                <p style="color: #cbd5e1; margin-bottom: 0;">
                    Consult with our procurement specialists regarding customized supply contracts for your industry.
                </p>
            </div>
            <a href="<?= url('quote.php') ?>" class="btn btn-primary btn-lg">REQUEST A QUOTE</a>
        </div>
    </div>
</section>

<?php include INCLUDES_PATH . 'footer.php'; ?>
