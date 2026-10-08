<?php
/**
 * STONE ENERGY INT'L LTD - RFQ Detail & Commercial Processor
 */
$requiredPermission = 'rfqs.manage';
require_once __DIR__ . '/includes/auth-check.php';

$rfqId = (int)($_GET['id'] ?? 0);
$rfq = Database::fetchOne("SELECT * FROM `rfqs` WHERE `id` = :id", [':id' => $rfqId]);

if (!$rfq) {
    set_flash('error', 'The requested RFQ could not be located.');
    header('Location: ' . admin_url('rfqs.php'));
    exit;
}

$rfqItems = Database::fetchAll("SELECT * FROM `rfq_items` WHERE `rfq_id` = :id ORDER BY `id` ASC", [':id' => $rfqId]);

$adminPageTitle = "RFQ {$rfq['rfq_number']} Detail | STONE ENERGY INT'L LTD CMS";
$adminSection = "RFQ Details";

// Handle Status & Admin Notes Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();

    $newStatus    = trim($_POST['status'] ?? $rfq['status']);
    $adminNotes   = trim($_POST['admin_notes'] ?? '');
    $notifyClient = !empty($_POST['notify_client']);

    $validStatuses = ['NEW', 'UNDER REVIEW', 'QUOTATION PREPARED', 'SENT', 'NEGOTIATION', 'APPROVED', 'COMPLETED', 'CANCELLED'];

    if (in_array($newStatus, $validStatuses, true)) {
        Database::update('rfqs', [
            'status'      => $newStatus,
            'admin_notes' => $adminNotes
        ], '`id` = :id', [':id' => $rfqId]);

        Audit::log('update_rfq', 'rfqs', (string)$rfqId, "Updated RFQ {$rfq['rfq_number']} to {$newStatus}");

        if ($notifyClient && !empty($rfq['email'])) {
            Mailer::sendRfqStatusUpdate($rfq, $newStatus, $adminNotes);
        }

        set_flash('success', "RFQ status successfully updated to {$newStatus}.");
        header('Location: ' . admin_url('rfq-view.php?id=' . $rfqId));
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <a href="<?= admin_url('rfqs.php') ?>" style="font-size: 0.85rem; color: var(--admin-accent); font-weight: 600;">
            &larr; Back to RFQ Pipeline
        </a>
        <h2 style="font-size: 1.6rem; color: var(--admin-text); margin-top: 4px;">
            Commercial RFQ: <span style="color: var(--admin-accent);"><?= e($rfq['rfq_number']) ?></span>
        </h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" onclick="window.print()" class="btn-admin btn-admin-outline">
            Print RFQ Sheet
        </button>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 28px;">
    <!-- Main RFQ Payload -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Technical &amp; Scope Requirements</h3>
                <span class="badge-status badge-new" style="font-size: 0.8rem;"><?= e($rfq['status']) ?></span>
            </div>
            <div class="admin-card-body">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.92rem; margin-bottom: 24px;">
                    <tbody>
                        <tr style="border-bottom: 1px solid var(--admin-border);">
                            <td style="padding: 10px 0; color: var(--admin-text-muted); width: 35%; font-weight: 600;">Product / Service Required</td>
                            <td style="padding: 10px 0; font-weight: 700; color: var(--admin-text);"><?= e($rfq['service_or_product']) ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--admin-border);">
                            <td style="padding: 10px 0; color: var(--admin-text-muted); font-weight: 600;">Industry Sector</td>
                            <td style="padding: 10px 0;"><?= e($rfq['industry']) ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--admin-border);">
                            <td style="padding: 10px 0; color: var(--admin-text-muted); font-weight: 600;">Estimated Quantity / Volume</td>
                            <td style="padding: 10px 0;"><?= e($rfq['quantity'] ?: 'Not specified') ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--admin-border);">
                            <td style="padding: 10px 0; color: var(--admin-text-muted); font-weight: 600;">Site / Delivery Location</td>
                            <td style="padding: 10px 0;"><?= e($rfq['project_location'] ?: 'Not specified') ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--admin-border);">
                            <td style="padding: 10px 0; color: var(--admin-text-muted); font-weight: 600;">Required Delivery Date</td>
                            <td style="padding: 10px 0;"><?= format_date($rfq['required_delivery_date']) ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 0; color: var(--admin-text-muted); font-weight: 600;">Submission Timestamp</td>
                            <td style="padding: 10px 0;"><?= format_date($rfq['created_at'], 'M j, Y - H:i:s') ?></td>
                        </tr>
                    </tbody>
                </table>

                <?php if (!empty($rfqItems)): ?>
                <h4 style="font-size: 1rem; margin-bottom: 10px;">Itemized Procurement Breakdown (<?= count($rfqItems) ?> items):</h4>
                <div style="overflow-x: auto; margin-bottom: 24px;">
                    <table class="admin-table" style="font-size: 0.88rem;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Specification</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Target Budget / Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rfqItems as $idx => $it): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <strong><?= e($it['item_name']) ?></strong>
                                    <?php if (!empty($it['description'])): ?>
                                        <div style="font-size: 0.8rem; color: var(--admin-text-muted);"><?= e($it['description']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($it['quantity']) ?></td>
                                <td><?= e($it['unit'] ?: 'Units') ?></td>
                                <td><?= e($it['target_price'] ?: 'N/A') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <h4 style="font-size: 1rem; margin-bottom: 8px;">Detailed Project Specifications:</h4>
                <div style="background: var(--admin-body-bg); border: 1px solid var(--admin-border); border-radius: 6px; padding: 18px; font-size: 0.9rem; line-height: 1.6; margin-bottom: 24px; white-space: pre-wrap;"><?= e($rfq['project_description']) ?></div>

                <?php if (!empty($rfq['additional_notes'])): ?>
                <h4 style="font-size: 1rem; margin-bottom: 8px;">Additional Commercial Instructions:</h4>
                <div style="background: var(--admin-body-bg); border: 1px solid var(--admin-border); border-radius: 6px; padding: 18px; font-size: 0.9rem; line-height: 1.6; margin-bottom: 24px; white-space: pre-wrap;"><?= e($rfq['additional_notes']) ?></div>
                <?php endif; ?>

                <?php if (!empty($rfq['attachment_path'])): ?>
                <h4 style="font-size: 1rem; margin-bottom: 8px;">Attached Tender File / Specification:</h4>
                <div style="display: flex; align-items: center; gap: 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 14px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                    <div style="flex-grow: 1;">
                        <div style="font-weight: 600; font-size: 0.88rem; color: #1e3a8a;"><?= e(basename($rfq['attachment_path'])) ?></div>
                        <div style="font-size: 0.75rem; color: #64748b;">Uploaded with RFQ submission</div>
                    </div>
                    <a href="<?= upload_url($rfq['attachment_path']) ?>" target="_blank" class="btn-admin btn-admin-primary" style="font-size: 0.8rem;">
                        Download / View Document &rarr;
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Client Entity & Status Update Form -->
    <div>
        <!-- Client Profile -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Customer Entity</h3>
            </div>
            <div class="admin-card-body" style="font-size: 0.88rem;">
                <div style="margin-bottom: 12px;">
                    <span style="color: var(--admin-text-muted);">Name:</span><br>
                    <strong style="font-size: 1.05rem;"><?= e($rfq['customer_name']) ?></strong>
                </div>
                <div style="margin-bottom: 12px;">
                    <span style="color: var(--admin-text-muted);">Organization:</span><br>
                    <strong><?= e($rfq['company_name'] ?: 'Private Client') ?></strong>
                </div>
                <div style="margin-bottom: 12px;">
                    <span style="color: var(--admin-text-muted);">Corporate Email:</span><br>
                    <a href="mailto:<?= e($rfq['email']) ?>" style="color: var(--admin-accent); font-weight: 600;"><?= e($rfq['email']) ?></a>
                </div>
                <div style="margin-bottom: 12px;">
                    <span style="color: var(--admin-text-muted);">Phone Number:</span><br>
                    <a href="tel:<?= e($rfq['phone']) ?>" style="font-weight: 600;"><?= e($rfq['phone']) ?></a>
                </div>
                <div>
                    <span style="color: var(--admin-text-muted);">Client IP:</span><br>
                    <code><?= e($rfq['ip_address'] ?? 'N/A') ?></code>
                </div>
            </div>
        </div>

        <!-- Status & Internal Notes Processor -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Update Pipeline Status</h3>
            </div>
            <div class="admin-card-body">
                <form action="<?= admin_url('rfq-view.php?id=' . $rfqId) ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="form-row">
                        <label class="form-label-admin">Status Stage</label>
                        <select name="status" class="form-control-admin" required>
                            <?php
                            $statuses = ['NEW', 'UNDER REVIEW', 'QUOTATION PREPARED', 'SENT', 'NEGOTIATION', 'APPROVED', 'COMPLETED', 'CANCELLED'];
                            foreach ($statuses as $st):
                            ?>
                            <option value="<?= $st ?>" <?= ($rfq['status'] === $st) ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Procurement / Admin Notes</label>
                        <textarea name="admin_notes" class="form-control-admin" style="min-height: 100px;" placeholder="Internal comments or quotation breakdown details..."><?= e($rfq['admin_notes'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; cursor: pointer;">
                            <input type="checkbox" name="notify_client" value="1" checked>
                            <span>Send email notification to client</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; justify-content: center;">
                        Save Pipeline Update &rarr;
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
