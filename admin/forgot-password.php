<?php
/**
 * STONE ENERGY INT'L LTD - Password Reset Request
 */
require_once __DIR__ . '/../config/config.php';

$success = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();

    $email = trim($_POST['email'] ?? '');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please provide a valid registered email address.";
    } else {
        $token = Auth::createPasswordResetToken($email);
        if ($token) {
            Mailer::sendPasswordReset($email, $token);
        }
        // Always show success message to prevent user enumeration attacks
        $success = true;
    }
}

$companyName = setting('company_name', "STONE ENERGY INT'L LTD");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | <?= e($companyName) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <style>
        body.login-body {
            background: radial-gradient(circle at 50% 10%, #1e3a8a 0%, #0a192f 60%, #050c18 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 12px;
            width: 100%;
            max-width: 440px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
        }
    </style>
</head>
<body class="login-body">
    <div class="login-card">
        <div style="text-align: center; margin-bottom: 24px;">
            <img src="<?= upload_url(setting('logo_url', 'assets/images/logo.svg')) ?>" alt="<?= e($companyName) ?>" style="max-height: 48px; max-width: 240px; object-fit: contain; margin-bottom: 12px;">
            <h2 style="font-size: 1.35rem; color: #0a192f;">Password Recovery</h2>
            <p style="font-size: 0.85rem; color: #64748b;">Enter your email to receive a secure reset link</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom: 20px; font-size: 0.88rem;">
                <span>If the account exists in our system, a password reset email has been dispatched. Please check your inbox.</span>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="<?= admin_url('login.php') ?>" class="btn-admin btn-admin-primary">Return to Sign In</a>
            </div>
        <?php else: ?>
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px; font-size: 0.86rem;">
                    <span><?= e($errorMessage) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= admin_url('forgot-password.php') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="form-row">
                    <label class="form-label-admin">Registered Email Address</label>
                    <input type="email" name="email" class="form-control-admin" required placeholder="admin@organization.com" autofocus>
                </div>
                <div style="margin-top: 24px;">
                    <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; justify-content: center; padding: 12px;">
                        Send Password Reset Link &rarr;
                    </button>
                </div>
            </form>
            <div style="margin-top: 24px; text-align: center;">
                <a href="<?= admin_url('login.php') ?>" style="font-size: 0.82rem; color: var(--admin-accent); font-weight: 600;">
                    &larr; Back to Sign In
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
