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

// Handle Actions: Update Status, Send Reply, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();
    $action = $_POST['action'] ?? 'update_status';

    // 1. Delete RFQ
    if ($action === 'delete') {
        Database::delete('rfqs', '`id` = :id', [':id' => $rfqId]);
        Audit::log('delete_rfq', 'rfqs', (string)$rfqId, "Deleted RFQ {$rfq['rfq_number']} permanently");
        set_flash('success', "RFQ {$rfq['rfq_number']} has been permanently deleted.");
        header('Location: ' . admin_url('rfqs.php'));
        exit;
    }

    // 2. Send Custom Email Reply & Proposal
    if ($action === 'send_reply') {
        $replySubject = trim($_POST['reply_subject'] ?? "Commercial Proposal: RFQ {$rfq['rfq_number']} - Stone Energy Int'l Ltd");
        $replyMessage = trim($_POST['reply_message'] ?? '');
        $newStatus    = trim($_POST['reply_status'] ?? 'SENT');

        if (empty($replyMessage)) {
            set_flash('error', "Please enter a message to send to the client.");
        } else {
            $company = setting('company_name', "STONE ENERGY INT'L LTD");
            $trackingLink = url('track.php?rfq=' . urlencode($rfq['rfq_number']));
            
            $htmlBody = "
            <h2>Commercial Quotation & Response</h2>
            <p>Dear <strong>" . e($rfq['customer_name']) . "</strong> (" . e($rfq['company_name'] ?: 'Valued Client') . "),</p>
            <p>Thank you for your Request for Quotation with <strong>{$company}</strong>.</p>
            <div class='info-box' style='background: #f8fafc; border-left: 4px solid #d97706; padding: 14px 18px; margin: 18px 0;'>
                <p style='margin: 4px 0;'><strong>Tracking ID:</strong> " . e($rfq['rfq_number']) . "</p>
                <p style='margin: 4px 0;'><strong>Requested Scope:</strong> " . e($rfq['service_or_product']) . "</p>
                <p style='margin: 4px 0;'><strong>Industry Sector:</strong> " . e($rfq['industry']) . "</p>
            </div>
            <div style='background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; margin: 20px 0;'>
                <h4 style='margin-top: 0; color: #0a192f;'>Commercial Response & Terms:</h4>
                <div style='line-height: 1.7; color: #334155;'>" . nl2br(e($replyMessage)) . "</div>
            </div>
            <p>You can track ongoing execution progress and milestones online anytime at:</p>
            <p><a href='{$trackingLink}' class='btn' style='background: #d97706; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: 600;'>Track RFQ #{$rfq['rfq_number']} Online &rarr;</a></p>
            <p>Should you require any immediate commercial alignment, feel free to reply directly to this email or call our procurement desk.</p>
            ";

            $sent = Mailer::send($rfq['email'], $rfq['customer_name'], $replySubject, $htmlBody);

            $notePrefix = "[" . date('Y-m-d H:i') . " Email Sent by " . ($currentUser['username'] ?? 'Admin') . "]: ";
            $updatedNotes = trim(($rfq['admin_notes'] ? $rfq['admin_notes'] . "\n\n" : "") . $notePrefix . substr($replyMessage, 0, 160) . "...");

            Database::update('rfqs', [
                'status' => $newStatus,
                'admin_notes' => $updatedNotes
            ], '`id` = :id', [':id' => $rfqId]);

            Audit::log('reply_rfq', 'rfqs', (string)$rfqId, "Dispatched commercial email proposal for RFQ {$rfq['rfq_number']} to {$rfq['email']}");
            set_flash('success', "Commercial reply successfully emailed to {$rfq['email']} and status set to {$newStatus}.");
            header('Location: ' . admin_url('rfq-view.php?id=' . $rfqId));
            exit;
        }
    }

    // 3. Status & Notes Update
    if ($action === 'update_status') {
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
    <div style="display: flex; gap: 10px; align-items: center;">
        <button type="button" onclick="window.print()" class="btn-admin btn-admin-outline">
            Print RFQ Sheet
        </button>
        <form action="<?= admin_url('rfq-view.php?id=' . $rfqId) ?>" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to permanently delete RFQ <?= e($rfq['rfq_number']) ?>? This action cannot be undone.');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn-admin" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                Delete RFQ
            </button>
        </form>
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

        <!-- Reply & Proposal Dispatcher Card -->
        <div class="admin-card" style="margin-top: 24px; border-top: 3px solid var(--admin-accent);">
            <div class="admin-card-header">
                <h3>Reply / Send Official Quotation Proposal</h3>
                <span style="font-size: 0.8rem; color: var(--admin-text-muted);">Dispatches via <?= e(setting('smtp_from_email', 'info@stoneenergyintl.com')) ?></span>
            </div>
            <div class="admin-card-body">
                <form action="<?= admin_url('rfq-view.php?id=' . $rfqId) ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="send_reply">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-row" style="margin-bottom: 0;">
                            <label class="form-label-admin">Recipient Email</label>
                            <input type="email" class="form-control-admin" value="<?= e($rfq['email']) ?>" readonly style="background: #f1f5f9; cursor: not-allowed;">
                        </div>
                        <div class="form-row" style="margin-bottom: 0;">
                            <label class="form-label-admin">Set New Pipeline Status</label>
                            <select name="reply_status" class="form-control-admin">
                                <option value="QUOTATION PREPARED">QUOTATION PREPARED</option>
                                <option value="SENT" selected>SENT (Proposal Dispatched)</option>
                                <option value="NEGOTIATION">NEGOTIATION</option>
                                <option value="APPROVED">APPROVED</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Email Subject</label>
                        <input type="text" name="reply_subject" class="form-control-admin" required value="Commercial Quotation: RFQ <?= e($rfq['rfq_number']) ?> - STONE ENERGY INT'L LTD">
                    </div>

                    <div class="form-row">
                        <label class="form-label-admin">Proposal Body / Message to Client</label>
                        <textarea name="reply_message" class="form-control-admin" style="min-height: 140px;" required placeholder="Dear <?= e($rfq['customer_name']) ?>,&#10;&#10;Regarding your quotation request for <?= e($rfq['service_or_product']) ?>, we are pleased to submit our commercial proposal as follows:&#10;&#10;1. Material/Supply Specifications: ...&#10;2. Unit Price / Total Commercial Sum: ...&#10;3. Delivery Timeline & Logistics: ...&#10;4. Payment Terms & Validity: ...&#10;&#10;Best regards,&#10;Procurement & Contracting Team&#10;STONE ENERGY INT'L LTD"></textarea>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; flex-wrap: wrap; gap: 12px;">
                        <span style="font-size: 0.8rem; color: var(--admin-text-muted);">Client will also receive their online tracking link automatically.</span>
                        <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 22px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            Dispatch Email Proposal &rarr;
                        </button>
                    </div>
                </form>
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
                <div style="margin-bottom: 12px;">
                    <span style="color: var(--admin-text-muted);">Client IP:</span><br>
                    <code><?= e($rfq['ip_address'] ?? 'N/A') ?></code>
                </div>

                <!-- Quick Direct Outreach -->
                <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--admin-border); display: flex; flex-direction: column; gap: 8px;">
                    <?php
                    $cleanPhone = preg_replace('/[^0-9]/', '', $rfq['phone']);
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '234' . substr($cleanPhone, 1);
                    }
                    ?>
                    <a href="https://wa.me/<?= e($cleanPhone) ?>?text=<?= urlencode("Hello " . $rfq['customer_name'] . ", this is Stone Energy Int'l Ltd reaching out regarding your RFQ: " . $rfq['rfq_number']) ?>" 
                       target="_blank" rel="noopener" 
                       class="btn-admin" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; justify-content: center; font-size: 0.82rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 6px;"><path d="M12.031 2C6.495 2 2 6.496 2 12.033c0 1.98.577 3.827 1.575 5.385L2.348 22l4.743-1.229a10.007 10.007 0 0 0 4.94 1.265h.004c5.536 0 10.031-4.496 10.031-10.033C22.066 6.496 17.571 2 12.031 2zm5.836 14.199c-.244.688-1.42 1.309-1.97 1.393-.51.077-1.168.109-3.791-.983-3.354-1.397-5.512-4.832-5.68-5.056-.168-.224-1.36-1.81-1.36-3.453 0-1.642.862-2.451 1.168-2.787.306-.336.669-.42.892-.42.224 0 .448.002.645.012.208.01.488-.078.763.582.285.688.97 2.37.1054 2.542.084.172.14.374.028.598-.112.224-.168.364-.336.56-.168.196-.353.438-.504.588-.168.168-.344.351-.148.688.196.336.872 1.436 1.87 2.325 1.284 1.144 2.365 1.498 2.701 1.666.336.168.532.14.73-.084.196-.224.84-0.98.1064-1.316.224-.336.448-.28.756-.168.308.112 1.956.923 2.292 1.091.336.168.56.252.644.392.084.14.084.812-.16 1.5z"/></svg>
                        Chat on WhatsApp
                    </a>
                    <a href="mailto:<?= e($rfq['email']) ?>?subject=<?= urlencode("Re: RFQ " . $rfq['rfq_number'] . " - Stone Energy Int'l Ltd") ?>" 
                       class="btn-admin btn-admin-outline" style="justify-content: center; font-size: 0.82rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        Open in Email App
                    </a>
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
