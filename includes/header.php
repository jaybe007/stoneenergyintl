<?php
/**
 * STONE ENERGY INT'L LTD - Main Frontend Header
 */
require_once __DIR__ . '/../config/config.php';

// Retrieve global site settings
$companyName    = setting('company_name', "STONE ENERGY INT'L LTD");
$tagline        = setting('tagline', "GENERAL CONTRACTOR & MULTI-SECTOR SUPPLY SOLUTIONS");
$phonePrimary   = setting('phone_primary', '08037745881');
$phoneSecondary = setting('phone_secondary', '08084949840');
$emailPrimary   = setting('email_primary', '[ADD COMPANY EMAIL]');
$officeAddress  = setting('office_address', '22, Oyelude Layout, Aba Alfa, Ojo, Ibadan, Oyo State, Nigeria.');
$businessHours  = setting('business_hours', 'Mon - Fri: 8:00 AM - 5:00 PM');
$logoUrl        = setting('logo_url', 'assets/images/logo.svg');
$faviconUrl     = setting('favicon_url', 'assets/images/favicon.svg');

// Dynamic SEO tags
$pageTitle       = $pageTitle ?? ($companyName . " | General Contractor & Multi-Sector Supply Solutions");
$pageDescription = $pageDescription ?? ($tagline . ". Dependable procurement and contracting solutions in Ibadan, Oyo State, and nationwide.");
$proto           = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$canonicalUrl    = $canonicalUrl ?? ($proto . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/'));
$ogImage         = $ogImage ?? asset('images/placeholder.svg');

// Analytics IDs
$gaId = setting('analytics_ga_id', '');
$gtmId = setting('analytics_gtm_id', '');
$gscTag = setting('analytics_gsc_tag', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <link rel="icon" type="image/svg+xml" href="<?= upload_url($faviconUrl, 'assets/images/favicon.svg') ?>">

    <!-- Open Graph / Meta -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">

    <?php if (!empty($gscTag)): ?>
        <meta name="google-site-verification" content="<?= e($gscTag) ?>">
    <?php endif; ?>

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "GeneralContractor",
      "name": "<?= e($companyName) ?>",
      "alternateName": "Stone Energy International",
      "description": "<?= e($tagline) ?>",
      "url": "<?= e(BASE_URL) ?>",
      "telephone": ["+234<?= ltrim($phonePrimary, '0') ?>", "+234<?= ltrim($phoneSecondary, '0') ?>"],
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "22, Oyelude Layout, Aba Alfa, Ojo",
        "addressLocality": "Ibadan",
        "addressRegion": "Oyo State",
        "addressCountry": "NG"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": 7.4395,
        "longitude": 3.9056
      },
      "openingHours": "Mo-Fr 08:00-17:00",
      "serviceArea": "Nigeria",
      "priceRange": "$$"
    }
    </script>

    <?php if (!empty($gtmId)): ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?= e($gtmId) ?>');</script>
    <!-- End Google Tag Manager -->
    <?php endif; ?>

    <?php if (!empty($gaId)): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= e($gaId) ?>');
    </script>
    <?php endif; ?>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <a href="#mainContent" class="skip-link">Skip to main content</a>

    <!-- Top Utility Bar -->
    <div class="topbar">
        <div class="container topbar-inner">
            <div class="topbar-items">
                <span class="topbar-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span><?= e($officeAddress) ?></span>
                </span>
                <span class="topbar-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span><?= e($businessHours) ?></span>
                </span>
            </div>
            <div class="topbar-items">
                <span class="topbar-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <a href="tel:<?= e($phonePrimary) ?>"><?= e($phonePrimary) ?></a> | <a href="tel:<?= e($phoneSecondary) ?>"><?= e($phoneSecondary) ?></a>
                </span>
                <span class="topbar-item">
                    <a href="<?= url('track.php') ?>" title="Track Request for Quotation">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                        <span>Track RFQ</span>
                    </a>
                </span>
                <span class="topbar-item">
                    <a href="<?= url('search.php') ?>" title="Search Services & Products">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <span>Search</span>
                    </a>
                </span>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <header class="navbar" id="siteHeader">
        <div class="container navbar-inner">
            <a href="<?= url('') ?>" class="brand-logo" title="<?= e($companyName) ?>">
                <img src="<?= upload_url($logoUrl, 'assets/images/logo.svg') ?>" alt="<?= e($companyName) ?> Logo">
            </a>

            <!-- Navigation Links -->
            <?php include INCLUDES_PATH . 'navigation.php'; ?>

            <!-- Header Action CTA -->
            <div class="nav-cta">
                <a href="<?= url('quote.php') ?>" class="btn btn-primary" id="headerQuoteBtn">
                    REQUEST A QUOTE
                </a>
                <button type="button" class="mobile-toggle" id="mobileNavToggle" aria-label="Toggle Navigation Menu" aria-expanded="false">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Global Notification Banner Container -->
    <div class="container" style="padding-top: 15px;">
        <?= render_flash() ?>
    </div>

    <main id="mainContent">
