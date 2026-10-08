<?php
/**
 * STONE ENERGY INT'L LTD - Main Admin Operations Dashboard
 */
require_once __DIR__ . '/includes/auth-check.php';

$adminPageTitle = "Executive Dashboard | STONE ENERGY INT'L LTD CMS";
$adminSection = "Dashboard Overview";

// Gather statistics
$totalProducts   = (int)Database::fetchColumn("SELECT COUNT(*) FROM `products`");
$totalProjects   = (int)Database::fetchColumn("SELECT COUNT(*) FROM `projects`");
$totalServices   = (int)Database::fetchColumn("SELECT COUNT(*) FROM `services` WHERE `status` = 'active'");
$newRfqs         = (int)Database::fetchColumn("SELECT COUNT(*) FROM `rfqs` WHERE `status` = 'NEW'");
$totalRfqs       = (int)Database::fetchColumn("SELECT COUNT(*) FROM `rfqs`");
$unreadMessages  = (int)Database::fetchColumn("SELECT COUNT(*) FROM `contact_messages` WHERE `is_read` = 0 AND `is_archived` = 0");
$publishedPosts  = (int)Database::fetchColumn("SELECT COUNT(*) FROM `blog_posts` WHERE `status` = 'Published'");

// Recent RFQs
$recentRfqs = Database::fetchAll("SELECT * FROM `rfqs` ORDER BY `id` DESC LIMIT 6");

// Recent Contact Messages
$recentMessages = Database::fetchAll("SELECT * FROM `contact_messages` WHERE `is_archived` = 0 ORDER BY `id` DESC LIMIT 5");

// Recent Audit Log actions
$recentLogs = Database::fetchAll("SELECT a.*, u.username FROM `audit_logs` a LEFT JOIN `users` u ON a.user_id = u.id ORDER BY a.id DESC LIMIT 6");

include __DIR__ . '/includes/header.php';
?>

<!-- Welcome Banner -->
<div style="background: linear-gradient(135deg, #0a192f 0%, #1e3a8a 100%); border-radius: 10px; padding: 28px 32px; color: #fff; margin-bottom: 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
    <div>
        <h2 style="font-size: 1.6rem; color: #fff; margin-bottom: 6px;">
            Welcome back, <?= e($currentUser['full_name']) ?>!
        </h2>
        <p style="color: #cbd5e1; margin-bottom: 0; font-size: 0.95rem;">
            Role: <strong style="color: #fbbf24;"><?= e($currentUser['role_name']) ?></strong> &bull; Headquarters: <strong>Ibadan, Oyo State, Nigeria</strong>
        </p>
    </div>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="https://stoneenergyintl.com/webmail" target="_blank" rel="noopener" class="btn-admin btn-admin-outline" style="color: #fff; background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.3); display: flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <span>Open Webmail (info@)</span>
        </a>
        <?php if (Auth::hasPermission('rfqs.manage')): ?>
        <a href="<?= admin_url('rfqs.php') ?>" class="btn-admin btn-admin-primary">
            Review RFQs (<?= $newRfqs ?> New)
        </a>
        <?php endif; ?>
        <?php if (Auth::hasPermission('products.manage')): ?>
        <a href="<?= admin_url('product-edit.php') ?>" class="btn-admin btn-admin-outline" style="color: #fff; background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2);">
            + Add Product
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Key Metric Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h4>New RFQ Requests</h4>
            <div class="stat-number"><?= $newRfqs ?></div>
            <div style="font-size: 0.76rem; color: var(--admin-text-muted); margin-top: 4px;">Total RFQs: <?= $totalRfqs ?></div>
        </div>
        <div class="stat-icon amber">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h4>Unread Messages</h4>
            <div class="stat-number"><?= $unreadMessages ?></div>
            <div style="font-size: 0.76rem; color: var(--admin-text-muted); margin-top: 4px;">Contact Inquiries</div>
        </div>
        <div class="stat-icon blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h4>Catalog Products</h4>
            <div class="stat-number"><?= $totalProducts ?></div>
            <div style="font-size: 0.76rem; color: var(--admin-text-muted); margin-top: 4px;">Items in Database</div>
        </div>
        <div class="stat-icon green">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h4>Project Portfolio</h4>
            <div class="stat-number"><?= $totalProjects ?></div>
            <div style="font-size: 0.76rem; color: var(--admin-text-muted); margin-top: 4px;">Civil &amp; Supply Works</div>
        </div>
        <div class="stat-icon purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
        </div>
    </div>
</div>

<!-- Two-Column Operational Content -->
<div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 28px;">
    <!-- Left Column: Recent RFQs -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Recent Commercial RFQs</h3>
                <a href="<?= admin_url('rfqs.php') ?>" class="btn-admin btn-admin-outline" style="font-size: 0.8rem;">
                    View All RFQs &rarr;
                </a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>RFQ Tracking</th>
                            <th>Client / Entity</th>
                            <th>Sector / Requirement</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentRfqs)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--admin-text-muted); padding: 30px;">
                                No Request for Quotes submitted yet.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recentRfqs as $r): 
                            $badgeClass = match ($r['status']) {
                                'NEW' => 'badge-new',
                                'UNDER REVIEW' => 'badge-under-review',
                                'APPROVED', 'SENT' => 'badge-approved',
                                'COMPLETED' => 'badge-completed',
                                'CANCELLED' => 'badge-cancelled',
                                default => 'badge-under-review'
                            };
                        ?>
                        <tr>
                            <td>
                                <strong style="color: var(--admin-accent);"><?= e($r['rfq_number']) ?></strong><br>
                                <small style="color: var(--admin-text-muted);"><?= format_date($r['created_at']) ?></small>
                            </td>
                            <td>
                                <strong><?= e($r['customer_name']) ?></strong><br>
                                <small><?= e($r['company_name'] ?: 'Individual') ?></small>
                            </td>
                            <td>
                                <span><?= e(truncate($r['service_or_product'], 26)) ?></span><br>
                                <small style="color: var(--admin-text-muted);"><?= e($r['industry']) ?></small>
                            </td>
                            <td>
                                <span class="badge-status <?= $badgeClass ?>"><?= e($r['status']) ?></span>
                            </td>
                            <td>
                                <a href="<?= admin_url('rfq-view.php?id=' . $r['id']) ?>" class="btn-admin btn-admin-outline btn-icon" title="Review Detail">
                                    View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Contact Messages -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Latest Contact Inquiries</h3>
                <a href="<?= admin_url('messages.php') ?>" class="btn-admin btn-admin-outline" style="font-size: 0.8rem;">
                    All Messages &rarr;
                </a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentMessages)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--admin-text-muted); padding: 30px;">
                                No recent contact inquiries.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recentMessages as $msg): ?>
                        <tr style="<?= $msg['is_read'] ? '' : 'background-color: #f0fdf4;' ?>">
                            <td>
                                <strong><?= e($msg['name']) ?></strong><br>
                                <small style="color: var(--admin-text-muted);"><?= e($msg['email']) ?></small>
                            </td>
                            <td>
                                <span><?= e($msg['subject']) ?></span><br>
                                <small style="color: var(--admin-text-muted);"><?= truncate($msg['message'], 36) ?></small>
                            </td>
                            <td>
                                <small><?= time_ago($msg['created_at']) ?></small>
                            </td>
                            <td>
                                <a href="<?= admin_url('messages.php?view=' . $msg['id']) ?>" class="btn-admin btn-admin-outline btn-icon">
                                    Open
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Quick Shortcuts & Audit Logs -->
    <div>
        <!-- Quick System Status & Info -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Corporate Settings</h3>
            </div>
            <div class="admin-card-body" style="font-size: 0.88rem;">
                <div style="margin-bottom: 12px;">
                    <span style="color: var(--admin-text-muted);">Company:</span><br>
                    <strong><?= e(setting('company_name', "STONE ENERGY INT'L LTD")) ?></strong>
                </div>
                <div style="margin-bottom: 12px;">
                    <span style="color: var(--admin-text-muted);">Headquarters:</span><br>
                    <span><?= e(setting('office_address')) ?></span>
                </div>
                <div style="margin-bottom: 12px;">
                    <span style="color: var(--admin-text-muted);">Phones:</span><br>
                    <span><?= e(setting('phone_primary')) ?> / <?= e(setting('phone_secondary')) ?></span>
                </div>
                <div style="margin-bottom: 16px;">
                    <span style="color: var(--admin-text-muted);">WhatsApp Widget:</span><br>
                    <span class="badge-status <?= Settings::getBool('whatsapp_enabled') ? 'badge-approved' : 'badge-inactive' ?>">
                        <?= Settings::getBool('whatsapp_enabled') ? 'Active' : 'Disabled' ?>
                    </span>
                </div>
                <?php if (Auth::hasPermission('settings.manage')): ?>
                <a href="<?= admin_url('settings.php') ?>" class="btn-admin btn-admin-outline" style="width: 100%; justify-content: center;">
                    Edit Site Settings &rarr;
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Audit Trail -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Recent Activity Log</h3>
                <?php if (Auth::hasPermission('audit.view')): ?>
                <a href="<?= admin_url('audit-logs.php') ?>" style="font-size: 0.8rem; color: var(--admin-accent); font-weight: 600;">Full Trail &rarr;</a>
                <?php endif; ?>
            </div>
            <div class="admin-card-body" style="padding: 16px;">
                <?php if (empty($recentLogs)): ?>
                    <p style="color: var(--admin-text-muted); font-size: 0.85rem; text-align: center;">No activity recorded yet.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php foreach ($recentLogs as $log): ?>
                        <div style="font-size: 0.82rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                                <strong style="color: var(--admin-text);"><?= e($log['action']) ?></strong>
                                <span style="color: var(--admin-text-muted); font-size: 0.75rem;"><?= time_ago($log['created_at']) ?></span>
                            </div>
                            <div style="color: var(--admin-text-muted); margin-bottom: 2px;"><?= e($log['description']) ?></div>
                            <div style="color: var(--admin-accent); font-size: 0.72rem;"><?= e($log['username'] ?? 'System') ?> &bull; IP: <?= e($log['ip_address']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
