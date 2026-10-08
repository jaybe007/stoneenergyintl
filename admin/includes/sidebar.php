<?php
/**
 * STONE ENERGY INT'L LTD - Reusable Admin Sidebar Navigation
 */
$adminCurrentPage = basename($_SERVER['PHP_SELF'] ?? '');

// Live notification counts
$newRfqCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM `rfqs` WHERE `status` = 'NEW'");
$unreadMsgCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM `contact_messages` WHERE `is_read` = 0 AND `is_archived` = 0");
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-brand">
        <?php $adminLogoUrl = setting('logo_light_url', setting('logo_url', 'assets/images/logo-light.svg')); ?>
        <img src="<?= upload_url($adminLogoUrl, 'assets/images/logo-light.svg') ?>" alt="<?= e(setting('company_name', "Stone Energy Int'l Ltd")) ?>" style="max-height: 42px; max-width: 190px; object-fit: contain;">
    </div>

    <div class="admin-user-profile">
        <div class="admin-avatar">
            <?= strtoupper(substr($currentUser['full_name'] ?? 'A', 0, 1)) ?>
        </div>
        <div class="admin-user-meta">
            <div class="admin-user-name"><?= e($currentUser['full_name'] ?? $currentUser['username']) ?></div>
            <div class="admin-user-role"><?= e($currentUser['role_name'] ?? $currentRole) ?></div>
        </div>
    </div>

    <nav class="admin-nav">
        <!-- Main -->
        <div class="nav-section-title">Core Operations</div>
        <a href="<?= admin_url('dashboard.php') ?>" class="admin-nav-item <?= ($adminCurrentPage === 'dashboard.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            <span>Dashboard</span>
        </a>

        <!-- Commercial / Inquiries -->
        <?php if (Auth::hasPermission('rfqs.manage') || Auth::hasPermission('messages.manage')): ?>
        <div class="nav-section-title">Commercial &amp; Inquiries</div>
        
        <?php if (Auth::hasPermission('rfqs.manage')): ?>
        <a href="<?= admin_url('rfqs.php') ?>" class="admin-nav-item <?= (str_contains($adminCurrentPage, 'rfq')) ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <span>Request for Quotes</span>
            <?php if ($newRfqCount > 0): ?>
                <span class="badge-count"><?= $newRfqCount ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>

        <?php if (Auth::hasPermission('messages.manage')): ?>
        <a href="<?= admin_url('messages.php') ?>" class="admin-nav-item <?= ($adminCurrentPage === 'messages.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <span>Contact Messages</span>
            <?php if ($unreadMsgCount > 0): ?>
                <span class="badge-count"><?= $unreadMsgCount ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>

        <!-- Corporate Webmail -->
        <a href="https://stoneenergyintl.com/webmail" target="_blank" rel="noopener" class="admin-nav-item" style="color: var(--color-accent-400);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <span>Corporate Webmail</span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: auto; opacity: 0.6;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
        </a>
        <?php endif; ?>

        <!-- Catalogue & Services -->
        <div class="nav-section-title">Catalog &amp; Projects</div>

        <?php if (Auth::hasPermission('products.manage')): ?>
        <a href="<?= admin_url('products.php') ?>" class="admin-nav-item <?= (str_contains($adminCurrentPage, 'product') && !str_contains($adminCurrentPage, 'categories')) ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
            <span>Products</span>
        </a>
        <a href="<?= admin_url('product-categories.php') ?>" class="admin-nav-item <?= ($adminCurrentPage === 'product-categories.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
            <span>Product Categories</span>
        </a>
        <?php endif; ?>

        <?php if (Auth::hasPermission('projects.manage')): ?>
        <a href="<?= admin_url('projects.php') ?>" class="admin-nav-item <?= (str_contains($adminCurrentPage, 'project')) ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            <span>Projects Portfolio</span>
        </a>
        <?php endif; ?>

        <?php if (Auth::hasPermission('services.manage')): ?>
        <a href="<?= admin_url('services.php') ?>" class="admin-nav-item <?= (str_contains($adminCurrentPage, 'service')) ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
            <span>Services</span>
        </a>
        <?php endif; ?>

        <!-- Content Management -->
        <div class="nav-section-title">Publishing &amp; Media</div>

        <?php if (Auth::hasPermission('blog.manage')): ?>
        <a href="<?= admin_url('blog.php') ?>" class="admin-nav-item <?= (str_contains($adminCurrentPage, 'blog') && !str_contains($adminCurrentPage, 'categories')) ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
            <span>Articles &amp; News</span>
        </a>
        <a href="<?= admin_url('blog-categories.php') ?>" class="admin-nav-item <?= ($adminCurrentPage === 'blog-categories.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
            <span>Blog Categories</span>
        </a>
        <?php endif; ?>

        <?php if (Auth::hasPermission('media.manage')): ?>
        <a href="<?= admin_url('media.php') ?>" class="admin-nav-item <?= ($adminCurrentPage === 'media.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
            <span>Media Library</span>
        </a>
        <?php endif; ?>

        <?php if (Auth::hasPermission('pages.manage')): ?>
        <a href="<?= admin_url('homepage-cms.php') ?>" class="admin-nav-item <?= ($adminCurrentPage === 'homepage-cms.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <span>Homepage &amp; About CMS</span>
        </a>
        <a href="<?= admin_url('pages.php') ?>" class="admin-nav-item <?= ($adminCurrentPage === 'pages.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            <span>Legal Pages</span>
        </a>
        <?php endif; ?>

        <!-- System -->
        <div class="nav-section-title">Administration</div>

        <?php if (Auth::hasPermission('settings.manage')): ?>
        <a href="<?= admin_url('settings.php') ?>" class="admin-nav-item <?= ($adminCurrentPage === 'settings.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            <span>Site Settings</span>
        </a>
        <a href="<?= admin_url('settings.php?tab=tabLogos') ?>" class="admin-nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
            <span>Logo &amp; Branding</span>
        </a>
        <?php endif; ?>

        <?php if (Auth::isSuperAdmin() || Auth::hasRole('super_admin')): ?>
        <a href="<?= admin_url('users.php') ?>" class="admin-nav-item <?= (str_contains($adminCurrentPage, 'user')) ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            <span>Admin Users</span>
        </a>
        <a href="<?= admin_url('backup.php') ?>" class="admin-nav-item <?= ($adminCurrentPage === 'backup.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <span>Database Backup</span>
        </a>
        <?php endif; ?>

        <?php if (Auth::hasPermission('audit.view')): ?>
        <a href="<?= admin_url('audit-logs.php') ?>" class="admin-nav-item <?= ($adminCurrentPage === 'audit-logs.php') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
            <span>Audit Trail</span>
        </a>
        <?php endif; ?>

        <div style="margin-top: 24px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.08);">
            <a href="<?= admin_url('logout.php') ?>" class="admin-nav-item" style="color: #f87171;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Sign Out</span>
            </a>
        </div>
    </nav>
</aside>
