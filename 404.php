<?php
/**
 * STONE ENERGY INT'L LTD - 404 Not Found Error Page
 */
require_once __DIR__ . '/config/config.php';
http_response_code(404);

$pageTitle = "404 - Page Not Found | STONE ENERGY INT'L LTD";
$pageDescription = "The requested resource could not be located on STONE ENERGY INT'L LTD.";

include INCLUDES_PATH . 'header.php';
?>

<section class="section" style="padding: 100px 0; text-align: center;">
    <div class="container" style="max-width: 600px;">
        <span class="section-tag" style="background: #fee2e2; color: #b91c1c;">ERROR 404</span>
        <h1 style="font-size: 3.5rem; color: var(--color-primary-900); margin: 12px 0;">Page Not Located</h1>
        <p style="font-size: 1.1rem; color: var(--color-dark-600); margin-bottom: 30px;">
            The URL you requested could not be found on this server. It may have been relocated or updated.
        </p>
        <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
            <a href="<?= url('') ?>" class="btn btn-primary">Return to Homepage</a>
            <a href="<?= url('services.php') ?>" class="btn btn-navy">View Services</a>
            <a href="<?= url('contact.php') ?>" class="btn btn-outline-gold">Contact Office</a>
        </div>
    </div>
</section>

<?php include INCLUDES_PATH . 'footer.php'; ?>
