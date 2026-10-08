<?php
/**
 * STONE ENERGY INT'L LTD - 403 Forbidden Access Page
 */
require_once __DIR__ . '/config/config.php';
http_response_code(403);

$pageTitle = "403 - Forbidden Access | STONE ENERGY INT'L LTD";
$pageDescription = "Access to this area is restricted to authorized personnel only.";

include INCLUDES_PATH . 'header.php';
?>

<section class="section" style="padding: 100px 0; text-align: center;">
    <div class="container" style="max-width: 600px;">
        <span class="section-tag" style="background: #fee2e2; color: #b91c1c;">ERROR 403</span>
        <h1 style="font-size: 3.2rem; color: var(--color-primary-900); margin: 12px 0;">Access Restricted</h1>
        <p style="font-size: 1.1rem; color: var(--color-dark-600); margin-bottom: 30px;">
            You do not possess the required administrative privileges to view or perform operations on this resource.
        </p>
        <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
            <a href="<?= url('') ?>" class="btn btn-primary">Return to Homepage</a>
            <a href="<?= admin_url('login.php') ?>" class="btn btn-navy">Admin Portal</a>
        </div>
    </div>
</section>

<?php include INCLUDES_PATH . 'footer.php'; ?>
