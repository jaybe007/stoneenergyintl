<?php
/**
 * STONE ENERGY INT'L LTD - Password Reset Token Submission
 */
require_once __DIR__ . '/../config/config.php';

$rawToken = trim($_GET['token'] ?? $_POST['token'] ?? '');
$validEmail = !empty($rawToken) ? Auth::verifyResetToken($rawToken) : null;

$success = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();

    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($validEmail)) {
        $errorMessage = "This password reset token is invalid or has expired.";
    } elseif (strlen($newPassword) < 8) {
        $errorMessage = "The new password must contain at least 8 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = "Passwords do not match. Please verify.";
    } else {
        $reset = Auth::resetPasswordWithToken($rawToken, $newPassword);
        if ($reset) {
            $success = true;
        } else {
            $errorMessage = "Failed to update password. Token may have expired.";
        }
    }
}

$companyName = setting('company_name', "STONE ENERGY INT'L LTD");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | <?= e($companyName) ?></title>
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
            <img src="<?= asset('images/logo.svg') ?>" alt="<?= e($companyName) ?>" style="height: 44px; margin-bottom: 12px;">
            <h2 style="font-size: 1.35rem; color: #0a192f;">Set New Password</h2>
            <p style="font-size: 0.85rem; color: #64748b;">Enter your new authorized admin credentials</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom: 20px; font-size: 0.88rem;">
                <span>Your password has been successfully updated. You may now log in with your new password.</span>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="<?= admin_url('login.php') ?>" class="btn-admin btn-admin-primary">Sign In Now &rarr;</a>
            </div>
        <?php elseif (empty($validEmail)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px; font-size: 0.86rem;">
                <span>This password reset link is invalid or has expired. Please request a new link.</span>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="<?= admin_url('forgot-password.php') ?>" class="btn-admin btn-admin-outline">Request New Link</a>
            </div>
        <?php else: ?>
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px; font-size: 0.86rem;">
                    <span><?= e($errorMessage) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= admin_url('reset-password.php') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($rawToken) ?>">

                <div class="form-row">
                    <label class="form-label-admin">New Password (min 8 chars)</label>
                    <input type="password" name="new_password" class="form-control-admin" required autocomplete="new-password" placeholder="••••••••••••" autofocus>
                </div>

                <div class="form-row">
                    <label class="form-label-admin">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control-admin" required autocomplete="new-password" placeholder="••••••••••••">
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; justify-content: center; padding: 12px;">
                        Update Password &rarr;
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
