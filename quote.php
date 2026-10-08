<?php
/**
 * STONE ENERGY INT'L LTD - Request for Quote (RFQ) System
 */
require_once __DIR__ . '/config/config.php';

$pageTitle = "Request a Quote (RFQ) | STONE ENERGY INT'L LTD";
$pageDescription = "Submit an official Request for Quotation (RFQ) for oil & gas supplies, civil construction, hospital equipment, or agro-allied procurement.";

$successRfq = null;
$errorMessage = '';

// Pre-fill parameters if redirected from product or service page
$prefillItem = trim($_GET['item'] ?? $_GET['service'] ?? '');
$prefillIndustry = trim($_GET['industry'] ?? '');

// Handle RFQ Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();

    $customerName   = trim($_POST['customer_name'] ?? '');
    $companyName    = trim($_POST['company_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $industry       = trim($_POST['industry'] ?? '');
    $itemOrService  = trim($_POST['service_or_product'] ?? '');
    $quantity       = trim($_POST['quantity'] ?? '');
    $location       = trim($_POST['project_location'] ?? '');
    $deliveryDate   = !empty($_POST['required_delivery_date']) ? $_POST['required_delivery_date'] : null;
    $description    = trim($_POST['project_description'] ?? '');
    $notes          = trim($_POST['additional_notes'] ?? '');

    // Validation
    if (empty($customerName) || empty($email) || empty($phone) || empty($industry) || empty($itemOrService) || empty($description)) {
        $errorMessage = "Please complete all mandatory fields marked with an asterisk (*).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please enter a valid corporate or personal email address.";
    } else {
        // Handle Attachment File Upload if present
        $attachmentPath = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = Uploader::upload($_FILES['attachment'], 'RFQ Attachment from ' . $customerName);
            if ($uploadResult['success']) {
                $attachmentPath = $uploadResult['file_path'];
            } else {
                $errorMessage = "Attachment error: " . $uploadResult['error'];
            }
        }

        if (empty($errorMessage)) {
            try {
                // Generate Unique Sequential RFQ Number (e.g. RFQ-2026-000001)
                $year = date('Y');
                $countThisYear = (int)Database::fetchColumn(
                    "SELECT COUNT(*) FROM `rfqs` WHERE `rfq_number` LIKE :prefix",
                    [':prefix' => "RFQ-{$year}-%"]
                );
                $nextSeq = str_pad((string)($countThisYear + 1), 6, '0', STR_PAD_LEFT);
                $rfqNumber = "RFQ-{$year}-{$nextSeq}";

                $rfqId = Database::insert('rfqs', [
                    'rfq_number'            => $rfqNumber,
                    'customer_name'         => $customerName,
                    'company_name'          => $companyName ?: null,
                    'email'                 => $email,
                    'phone'                 => $phone,
                    'industry'              => $industry,
                    'service_or_product'    => $itemOrService,
                    'quantity'              => $quantity ?: null,
                    'project_location'      => $location ?: null,
                    'required_delivery_date'=> $deliveryDate,
                    'project_description'   => $description,
                    'attachment_path'       => $attachmentPath,
                    'additional_notes'      => $notes ?: null,
                    'status'                => 'NEW',
                    'ip_address'            => $_SERVER['REMOTE_ADDR'] ?? null,
                    'created_at'            => date('Y-m-d H:i:s')
                ]);

                // Insert into rfq_items table for normalized line-item tracking
                try {
                    Database::insert('rfq_items', [
                        'rfq_id'         => $rfqId,
                        'item_name'      => $itemOrService,
                        'specifications' => truncate($description, 200),
                        'quantity'       => $quantity ?: '1 Lot',
                        'unit'           => 'Specified Scope'
                    ]);
                } catch (Exception $e) {
                    error_log("rfq_items notice: " . $e->getMessage());
                }

                // Record Audit Log
                Audit::log('submit_rfq', 'rfqs', (string)$rfqId, "New RFQ {$rfqNumber} submitted by {$customerName} ({$email})");

                // Prepare array for emails
                $rfqData = [
                    'id'                    => $rfqId,
                    'rfq_number'            => $rfqNumber,
                    'customer_name'         => $customerName,
                    'company_name'          => $companyName,
                    'email'                 => $email,
                    'phone'                 => $phone,
                    'industry'              => $industry,
                    'service_or_product'    => $itemOrService,
                    'quantity'              => $quantity,
                    'project_location'      => $location,
                    'required_delivery_date'=> $deliveryDate,
                    'project_description'   => $description
                ];

                // Send email notifications safely (never fail RFQ submission if mail dispatch has network delay)
                try {
                    Mailer::sendRfqCustomerNotice($rfqData);
                    Mailer::sendRfqAdminAlert($rfqData);
                } catch (Exception $e) {
                    error_log("RFQ email notification notice: " . $e->getMessage());
                }

                $successRfq = $rfqData;
            } catch (Exception $e) {
                error_log("RFQ Submission Error: " . $e->getMessage());
                $errorMessage = "An error occurred while transmitting your RFQ. Please retry or contact our hotline directly.";
            }
        }
    }
}

include INCLUDES_PATH . 'header.php';
?>

<div class="section-dark" style="padding: 60px 0; border-bottom: 3px solid var(--color-accent-500);">
    <div class="container">
        <span class="section-tag section-tag-light">COMMERCIAL TENDER PORTAL</span>
        <h1 style="font-size: 2.8rem; color: #fff; margin-bottom: 12px;">Request for Quotation (RFQ)</h1>
        <p style="color: #cbd5e1; max-width: 720px; font-size: 1.1rem; margin-bottom: 0;">
            Submit your bill of materials, civil construction tender drawings, or multi-sector supply specifications. We assign an official tracking number and provide formal commercial proposals.
        </p>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if ($successRfq): ?>
            <!-- Success Confirmation Screen -->
            <div style="background: var(--color-white); border: 2px solid #a7f3d0; border-radius: var(--radius-lg); padding: 48px; max-width: 800px; margin: 0 auto; box-shadow: var(--shadow-xl); text-align: center;">
                <div style="width: 72px; height: 72px; background: #ecfdf5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>

                <span class="section-tag" style="background: #d1fae5; color: #065f46;">SUBMISSION SUCCESSFUL</span>
                <h2 style="font-size: 2.2rem; margin: 12px 0;">Request for Quotation Received</h2>
                <p style="font-size: 1.05rem; color: var(--color-dark-600); max-width: 600px; margin: 0 auto 24px auto;">
                    Thank you, <strong><?= e($successRfq['customer_name']) ?></strong>. Your commercial procurement inquiry has been logged into our procurement pipeline.
                </p>

                <div style="background: var(--color-gray-100); border: 1.5px dashed var(--color-accent-500); border-radius: var(--radius-md); padding: 24px; max-width: 500px; margin: 0 auto 30px auto; text-align: left;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: var(--color-dark-600); font-weight: 600;">Tracking Number:</span>
                        <strong style="color: var(--color-accent-600); font-size: 1.15rem;"><?= e($successRfq['rfq_number']) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: var(--color-dark-600); font-weight: 600;">Subject / Item:</span>
                        <span><?= e($successRfq['service_or_product']) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-dark-600); font-weight: 600;">Status:</span>
                        <span class="badge-status badge-new">NEW / UNDER REVIEW</span>
                    </div>
                </div>

                <p style="font-size: 0.92rem; color: var(--color-dark-600); margin-bottom: 30px;">
                    An official email acknowledgment has been dispatched to <strong><?= e($successRfq['email']) ?></strong>. Our technical estimators will contact you within 24 to 48 business hours.
                </p>

                <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
                    <a href="<?= url('track.php?rfq=' . urlencode($successRfq['rfq_number'])) ?>" class="btn btn-primary">
                        Track RFQ Status &rarr;
                    </a>
                    <a href="<?= url('') ?>" class="btn btn-navy">Return to Home</a>
                    <a href="<?= url('quote.php') ?>" class="btn btn-outline-gold">Submit Another RFQ</a>
                </div>
            </div>
        <?php else: ?>
            <!-- RFQ Input Form -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 48px; align-items: start;">
                <div class="form-box">
                    <?php if (!empty($errorMessage)): ?>
                        <div class="alert alert-danger" style="margin-bottom: 24px;">
                            <span><?= e($errorMessage) ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="<?= url('quote.php') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <h3 style="font-size: 1.35rem; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid var(--color-gray-200); color: var(--color-primary-900);">
                            1. Contact &amp; Organization Details
                        </h3>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Full Name <span class="required">*</span></label>
                                <input type="text" name="customer_name" class="form-control" required placeholder="e.g. Engr. Babatunde Adeleke" value="<?= e($_POST['customer_name'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Company / Organization Name</label>
                                <input type="text" name="company_name" class="form-control" placeholder="e.g. Apex Industrial Solutions Ltd" value="<?= e($_POST['company_name'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Corporate Email Address <span class="required">*</span></label>
                                <input type="email" name="email" class="form-control" required placeholder="procurement@organization.com" value="<?= e($_POST['email'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone Number <span class="required">*</span></label>
                                <input type="tel" name="phone" class="form-control" required placeholder="e.g. 08031234567" value="<?= e($_POST['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <h3 style="font-size: 1.35rem; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 1px solid var(--color-gray-200); color: var(--color-primary-900);">
                            2. Requirement &amp; Project Scope
                        </h3>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Industry Sector <span class="required">*</span></label>
                                <select name="industry" class="form-control" required>
                                    <option value="">Select Target Industry</option>
                                    <option value="Oil & Gas" <?= ($prefillIndustry === 'Oil & Gas' || ($_POST['industry'] ?? '') === 'Oil & Gas') ? 'selected' : '' ?>>Oil &amp; Gas Supply</option>
                                    <option value="Building Construction" <?= ($prefillIndustry === 'Building Construction' || ($_POST['industry'] ?? '') === 'Building Construction') ? 'selected' : '' ?>>Building Construction &amp; Civil Works</option>
                                    <option value="Hospital & Healthcare" <?= ($prefillIndustry === 'Hospital & Healthcare' || ($_POST['industry'] ?? '') === 'Hospital & Healthcare') ? 'selected' : '' ?>>Hospital Equipment &amp; Consumables</option>
                                    <option value="Agro-Allied" <?= ($prefillIndustry === 'Agro-Allied' || ($_POST['industry'] ?? '') === 'Agro-Allied') ? 'selected' : '' ?>>Agro-Allied Supplies</option>
                                    <option value="Agroprocessing" <?= ($prefillIndustry === 'Agroprocessing' || ($_POST['industry'] ?? '') === 'Agroprocessing') ? 'selected' : '' ?>>Agroprocessing Plant Support</option>
                                    <option value="General Contracting" <?= ($prefillIndustry === 'General Contracting' || ($_POST['industry'] ?? '') === 'General Contracting') ? 'selected' : '' ?>>General Contracting</option>
                                    <option value="Industrial Supplies" <?= ($_POST['industry'] ?? '') === 'Industrial Supplies' ? 'selected' : '' ?>>Industrial Supplies &amp; Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Service or Product Name <span class="required">*</span></label>
                                <input type="text" name="service_or_product" class="form-control" required placeholder="e.g. 200 Bundles 16mm TMT Steel Rebars" value="<?= e($prefillItem ?: ($_POST['service_or_product'] ?? '')) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Estimated Quantity / Unit</label>
                                <input type="text" name="quantity" class="form-control" placeholder="e.g. 50 Units / 120 Metric Tons" value="<?= e($_POST['quantity'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Delivery / Site Location</label>
                                <input type="text" name="project_location" class="form-control" placeholder="e.g. Ibadan, Oyo State" value="<?= e($_POST['project_location'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Required Delivery / Start Date</label>
                                <input type="date" name="required_delivery_date" class="form-control" value="<?= e($_POST['required_delivery_date'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Specifications / Drawing Attachment</label>
                                <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                <span class="form-help">PDF, JPG, PNG or WEBP up to 10MB (BOQ, drawing, or specs)</span>
                            </div>

                            <div class="form-group-full form-group">
                                <label class="form-label">Detailed Project Specifications <span class="required">*</span></label>
                                <textarea name="project_description" class="form-control" required style="min-height: 140px;" placeholder="Outline technical specifications, brand requirements, site access details, or commercial guidelines..."><?= e($_POST['project_description'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group-full form-group">
                                <label class="form-label">Additional Instructions / Tender Notes</label>
                                <textarea name="additional_notes" class="form-control" style="min-height: 80px;" placeholder="Any specific commercial terms, milestones, or questions..."><?= e($_POST['additional_notes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div style="margin-top: 24px;">
                            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                                TRANSMIT FORMAL RFQ &rarr;
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Guidance & Hotlines Sidebar -->
                <div>
                    <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); padding: 30px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
                        <h4 style="font-size: 1.15rem; margin-bottom: 14px; color: var(--color-primary-900);">The RFQ Process</h4>
                        <ol style="list-style: decimal; padding-left: 18px; font-size: 0.9rem; color: var(--color-dark-600); line-height: 1.7;">
                            <li style="margin-bottom: 10px;"><strong>Submission:</strong> You submit your specifications, BOQ, or drawing online.</li>
                            <li style="margin-bottom: 10px;"><strong>Tracking Assignment:</strong> A unique tracking ID (e.g. <code>RFQ-2026-000001</code>) is automatically generated.</li>
                            <li style="margin-bottom: 10px;"><strong>Technical Review:</strong> Our procurement estimators evaluate mill availability and freight logistics.</li>
                            <li><strong>Formal Bid:</strong> A commercial quote with delivery timelines and terms is issued.</li>
                        </ol>
                    </div>

                    <div style="background: var(--color-primary-900); color: #fff; border-radius: var(--radius-md); padding: 30px;">
                        <h4 style="color: #fff; font-size: 1.15rem; margin-bottom: 12px;">Urgent Requirement?</h4>
                        <p style="color: #cbd5e1; font-size: 0.88rem; margin-bottom: 20px;">
                            For immediate material dispatch or emergency site works, reach our procurement hotlines directly:
                        </p>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <a href="tel:<?= e(setting('phone_primary', '08037745881')) ?>" class="btn btn-outline-gold btn-sm" style="width: 100%;">
                                <?= e(setting('phone_primary', '08037745881')) ?>
                            </a>
                            <a href="tel:<?= e(setting('phone_secondary', '08084949840')) ?>" class="btn btn-outline-light btn-sm" style="width: 100%;">
                                <?= e(setting('phone_secondary', '08084949840')) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include INCLUDES_PATH . 'footer.php'; ?>
