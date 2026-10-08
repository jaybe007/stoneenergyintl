<?php
/**
 * STONE ENERGY INT'L LTD - Secure Admin Login
 */
require_once __DIR__ . '/../config/config.php';

// If already authenticated, redirect to dashboard
if (Auth::check()) {
    header('Location: ' . admin_url('dashboard.php'));
    exit;
}

$errorMessage = '';
$redirect = $_GET['redirect'] ?? admin_url('dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errorMessage = "Please enter both username/email and password.";
    } else {
        $result = Auth::attempt($username, $password);
        if ($result['success']) {
            $dest = !empty($_POST['redirect']) ? urldecode($_POST['redirect']) : admin_url('dashboard.php');
            // Prevent open redirect attack
            if (!str_starts_with($dest, admin_url()) && !str_starts_with($dest, '/')) {
                $dest = admin_url('dashboard.php');
            }
            header('Location: ' . $dest);
            exit;
        } else {
            $errorMessage = $result['error'];
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
    <title>Admin Portal Login | <?= e($companyName) ?></title>
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
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-header img {
            height: 48px;
            margin: 0 auto 16px auto;
        }
        .login-header h2 {
            font-size: 1.45rem;
            color: #0a192f;
            margin-bottom: 6px;
        }
        .login-header p {
            font-size: 0.85rem;
            color: #64748b;
        }
    </style>
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-header">
            <img src="<?= asset('images/logo.svg') ?>" alt="<?= e($companyName) ?>">
            <h2>Administrative Access</h2>
            <p>Enter your authorized credentials to manage CMS</p>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px; font-size: 0.86rem;">
                <span><?= e($errorMessage) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= admin_url('login.php') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

            <div class="form-row">
                <label class="form-label-admin">Username or Corporate Email</label>
                <input type="text" name="username" class="form-control-admin" required autocomplete="username" placeholder="e.g. admin" value="<?= e($_POST['username'] ?? '') ?>" autofocus>
            </div>

            <div class="form-row">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="form-label-admin" style="margin-bottom: 0;">Account Password</label>
                    <a href="<?= admin_url('forgot-password.php') ?>" style="font-size: 0.78rem; color: var(--admin-accent); font-weight: 600;">Forgot Password?</a>
                </div>
                <input type="password" name="password" class="form-control-admin" required autocomplete="current-password" placeholder="••••••••••••">
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 0.95rem;">
                    Sign In to Dashboard &rarr;
                </button>
            </div>
        </form>

        <div style="margin-top: 28px; text-align: center; border-top: 1px solid var(--admin-border); padding-top: 16px;">
            <a href="<?= url('') ?>" style="font-size: 0.82rem; color: var(--admin-text-muted); text-decoration: none;">
                &larr; Return to Public Website
            </a>
        </div>
    </div>
</body>
</html>
