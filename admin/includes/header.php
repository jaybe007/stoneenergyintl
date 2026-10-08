<?php
/**
 * STONE ENERGY INT'L LTD - Reusable Admin Header & Topbar
 */
$adminPageTitle = $adminPageTitle ?? "Control Panel | STONE ENERGY INT'L LTD";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminPageTitle) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin-body">

    <!-- Include Sidebar Navigation -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Working Container -->
    <div class="admin-main">
        <!-- Admin Topbar -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <button type="button" id="adminSidebarToggle" class="btn-icon" style="background:none; border:none; cursor:pointer;" aria-label="Toggle Navigation">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <div class="breadcrumbs">
                    <a href="<?= admin_url('dashboard.php') ?>">CMS Admin</a> &rarr; 
                    <span><?= e($adminSection ?? 'Dashboard') ?></span>
                </div>
            </div>

            <div class="topbar-right">
                <a href="https://stoneenergyintl.com/webmail" target="_blank" rel="noopener" class="btn-admin btn-admin-outline" style="padding: 6px 12px; font-size: 0.78rem; display: flex; align-items: center; gap: 6px; color: var(--admin-primary); border-color: var(--admin-primary);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <span>Webmail (info@)</span>
                </a>
                <a href="<?= url('') ?>" target="_blank" class="btn-view-site">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    <span>View Public Website</span>
                </a>
                <span style="font-size: 0.85rem; color: var(--admin-text-muted);">
                    Logged in as: <strong><?= e($currentUser['username']) ?></strong>
                </span>
                <a href="<?= admin_url('logout.php') ?>" class="btn-admin btn-admin-outline" style="padding: 6px 12px; font-size: 0.78rem;">
                    Sign Out
                </a>
            </div>
        </header>

        <!-- Admin Content Area -->
        <main class="admin-content">
            <?= render_flash() ?>
