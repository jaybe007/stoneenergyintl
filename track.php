<?php
/**
 * STONE ENERGY INT'L LTD - Public RFQ & Order Tracking Portal
 * Allows corporate clients and commercial partners to track procurement and quote statuses
 */
require_once __DIR__ . '/config/config.php';

$pageTitle = "Track RFQ & Tender Status | STONE ENERGY INT'L LTD";
$pageDescription = "Track your Request for Quotation (RFQ), commercial tender proposal, and supply delivery progress in real-time with STONE ENERGY INT'L LTD.";

$companyName = setting('company_name', "STONE ENERGY INT'L LTD");
$phonePrimary = setting('phone_primary', '08037745881');
$whatsappNum  = setting('whatsapp_number', '2348037745881');

$searchQuery = trim($_GET['rfq'] ?? $_POST['rfq_number'] ?? '');
$searchEmail = trim($_POST['email'] ?? '');
$rfqResult   = null;
$searched    = false;
$errorMessage= '';

if (!empty($searchQuery)) {
    $searched = true;
    
    // Normalize format
    $searchQueryClean = strtoupper($searchQuery);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($searchEmail)) {
        $errorMessage = "Please provide the corporate email address used when submitting the RFQ.";
    } else {
        $sql = "SELECT * FROM `rfqs` WHERE UPPER(`rfq_number`) = :rfq ";
        $params = [':rfq' => $searchQueryClean];

        // If email provided, verify ownership for data privacy
        if (!empty($searchEmail)) {
            $sql .= "AND LOWER(`email`) = LOWER(:email) ";
            $params[':email'] = $searchEmail;
        }

        $rfqResult = Database::fetch($sql, $params);

        if (!$rfqResult && !empty($searchEmail)) {
            $errorMessage = "No quotation found matching tracking ID '{$searchQueryClean}' with the specified email address. Please verify your details.";
        } elseif (!$rfqResult && empty($searchEmail)) {
            // Prompts user to input verification email
            $errorMessage = "To safeguard confidential commercial data, please enter your email address to view this RFQ.";
        }
    }
}

// Calculate stepper state based on status enum:
// 'NEW', 'UNDER REVIEW', 'QUOTATION PREPARED', 'SENT', 'NEGOTIATION', 'APPROVED', 'COMPLETED', 'CANCELLED'
$statusOrder = [
    'NEW' => 1,
    'UNDER REVIEW' => 2,
    'QUOTATION PREPARED' => 3,
    'SENT' => 4,
    'NEGOTIATION' => 4,
    'APPROVED' => 5,
    'COMPLETED' => 6,
    'CANCELLED' => -1
];

$currentStep = 1;
if ($rfqResult) {
    $statusKey = strtoupper($rfqResult['status'] ?? 'NEW');
    $currentStep = $statusOrder[$statusKey] ?? 1;
}

include INCLUDES_PATH . 'header.php';
?>

<div class="section-dark" style="padding: 60px 0; border-bottom: 3px solid var(--color-accent-500);">
    <div class="container text-center">
        <span class="section-tag section-tag-light">REAL-TIME STATUS PORTAL</span>
        <h1 style="font-size: 2.8rem; color: #fff; margin-bottom: 12px;">Track Your RFQ &amp; Procurement</h1>
        <p style="color: #cbd5e1; max-width: 680px; font-size: 1.1rem; margin: 0 auto;">
            Enter your official RFQ tracking number (e.g. <code>RFQ-2026-000001</code>) to monitor technical estimation, commercial bidding, and dispatch progress.
        </p>
    </div>
</div>

<section class="section" style="background: var(--color-gray-50); min-height: 60vh;">
    <div class="container">
        
        <!-- Tracking Search Form -->
        <div style="background: #fff; border: 1px solid var(--color-gray-200); border-radius: var(--radius-lg); padding: 36px; max-width: 760px; margin: -30px auto 40px auto; box-shadow: var(--shadow-lg); position: relative; z-index: 5;">
            <form action="<?= url('track.php') ?>" method="POST">
                <div style="display: grid; grid-template-columns: 1.2fr 1fr auto; gap: 16px; align-items: flex-end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">RFQ Tracking Number <span class="required">*</span></label>
                        <input type="text" name="rfq_number" class="form-control" required 
                               placeholder="e.g. RFQ-2026-000001" 
                               value="<?= e($searchQuery) ?>" style="text-transform: uppercase; font-family: monospace; font-size: 1.05rem; font-weight: 600;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Corporate Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required 
                               placeholder="email@company.com" 
                               value="<?= e($searchEmail) ?>">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary" style="padding: 13px 26px; white-space: nowrap; height: 50px;">
                            Track Status &rarr;
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div style="max-width: 760px; margin: 0 auto 30px auto; background: #fef2f2; border-left: 4px solid #ef4444; padding: 18px 24px; border-radius: var(--radius-md); color: #991b1b; box-shadow: var(--shadow-sm);">
                <div style="display: flex; gap: 12px; align-items: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <div>
                        <strong>Notice:</strong> <?= e($errorMessage) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($rfqResult): ?>
            <!-- RFQ Tracking Detail Card -->
            <div style="background: #fff; border: 1px solid var(--color-gray-200); border-radius: var(--radius-lg); padding: 40px; max-width: 900px; margin: 0 auto; box-shadow: var(--shadow-md);">
                
                <!-- Card Header -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; border-bottom: 2px solid var(--color-gray-100); padding-bottom: 24px; margin-bottom: 32px;">
                    <div>
                        <span class="section-tag" style="margin-bottom: 6px;">OFFICIAL PROCUREMENT RECORD</span>
                        <h2 style="font-size: 1.8rem; margin-bottom: 4px; color: var(--color-primary-950);">
                            <?= e($rfqResult['rfq_number']) ?>
                        </h2>
                        <div style="color: var(--color-dark-600); font-size: 0.95rem;">
                            Submitted on <strong><?= date('F j, Y \a\t g:i A', strtotime($rfqResult['created_at'])) ?></strong>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span style="display: inline-block; padding: 8px 18px; border-radius: 50px; font-size: 0.88rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
                            <?php 
                            if ($rfqResult['status'] === 'COMPLETED') echo 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;';
                            elseif ($rfqResult['status'] === 'CANCELLED') echo 'background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;';
                            elseif ($rfqResult['status'] === 'SENT' || $rfqResult['status'] === 'QUOTATION PREPARED') echo 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;';
                            else echo 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;';
                            ?>">
                            <?= e($rfqResult['status']) ?>
                        </span>
                    </div>
                </div>

                <!-- Progress Stepper (Timeline) -->
                <?php if ($rfqResult['status'] !== 'CANCELLED'): ?>
                <div style="margin-bottom: 40px;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 24px; color: var(--color-primary-900);">Tender Lifecycle Progress</h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; position: relative;">
                        
                        <!-- Step 1: Received -->
                        <div style="text-align: center;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; margin: 0 auto 10px auto; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem;
                                <?= ($currentStep >= 1) ? 'background: var(--color-accent-500); color: #fff;' : 'background: #e2e8f0; color: #64748b;' ?>">
                                1
                            </div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--color-primary-950);">Received</div>
                            <div style="font-size: 0.78rem; color: #64748b;">Logged into registry</div>
                        </div>

                        <!-- Step 2: Under Technical Review -->
                        <div style="text-align: center;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; margin: 0 auto 10px auto; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem;
                                <?= ($currentStep >= 2) ? 'background: var(--color-accent-500); color: #fff;' : 'background: #e2e8f0; color: #64748b;' ?>">
                                2
                            </div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--color-primary-950);">Technical Review</div>
                            <div style="font-size: 0.78rem; color: #64748b;">BOQ / Mill evaluation</div>
                        </div>

                        <!-- Step 3: Quotation Prepared & Dispatched -->
                        <div style="text-align: center;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; margin: 0 auto 10px auto; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem;
                                <?= ($currentStep >= 3) ? 'background: var(--color-accent-500); color: #fff;' : 'background: #e2e8f0; color: #64748b;' ?>">
                                3
                            </div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--color-primary-950);">Bid Prepared / Sent</div>
                            <div style="font-size: 0.78rem; color: #64748b;">Commercial terms ready</div>
                        </div>

                        <!-- Step 4: Execution / Completion -->
                        <div style="text-align: center;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; margin: 0 auto 10px auto; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem;
                                <?= ($currentStep >= 5) ? 'background: #059669; color: #fff;' : 'background: #e2e8f0; color: #64748b;' ?>">
                                4
                            </div>
                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--color-primary-950);">Delivery &amp; Closeout</div>
                            <div style="font-size: 0.78rem; color: #64748b;">Contract fulfillment</div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-md); padding: 18px; margin-bottom: 30px; color: #991b1b;">
                        <strong>Status: Cancelled / Closed.</strong> This procurement tender was marked as cancelled or closed by our administrative office.
                    </div>
                <?php endif; ?>

                <!-- Tender Specifications Grid -->
                <div style="background: var(--color-gray-50); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); padding: 26px; margin-bottom: 30px;">
                    <h3 style="font-size: 1.05rem; margin-bottom: 16px; color: var(--color-primary-950);">Requirement Overview</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; font-size: 0.92rem;">
                        <div>
                            <span style="color: #64748b;">Client Name:</span>
                            <div style="font-weight: 600;"><?= e($rfqResult['customer_name']) ?></div>
                        </div>
                        <div>
                            <span style="color: #64748b;">Organization / Enterprise:</span>
                            <div style="font-weight: 600;"><?= e($rfqResult['company_name'] ?: 'Private / Individual Tender') ?></div>
                        </div>
                        <div>
                            <span style="color: #64748b;">Industry Sector:</span>
                            <div style="font-weight: 600;"><?= e($rfqResult['industry']) ?></div>
                        </div>
                        <div>
                            <span style="color: #64748b;">Product / Service Requested:</span>
                            <div style="font-weight: 600;"><?= e($rfqResult['service_or_product']) ?></div>
                        </div>
                        <div>
                            <span style="color: #64748b;">Estimated Quantity:</span>
                            <div style="font-weight: 600;"><?= e($rfqResult['quantity'] ?: 'Not Specified') ?></div>
                        </div>
                        <div>
                            <span style="color: #64748b;">Site / Delivery Location:</span>
                            <div style="font-weight: 600;"><?= e($rfqResult['project_location'] ?: 'To Be Advised') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Fast Actions -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; padding-top: 10px;">
                    <div>
                        <span style="color: #64748b; font-size: 0.88rem;">Need immediate clarification or priority processing?</span>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <a href="https://wa.me/<?= e($whatsappNum) ?>?text=<?= urlencode("Hello Stone Energy Int'l, I am following up on RFQ Tracking: " . $rfqResult['rfq_number']) ?>" 
                           target="_blank" rel="noopener" 
                           class="btn btn-outline-dark" style="font-size: 0.9rem;">
                           WhatsApp Procurement &rarr;
                        </a>
                        <a href="tel:<?= e($phonePrimary) ?>" class="btn btn-primary" style="font-size: 0.9rem;">
                           Call Hotline (<?= e($phonePrimary) ?>)
                        </a>
                    </div>
                </div>

            </div>
        <?php endif; ?>

    </div>
</section>

<?php include INCLUDES_PATH . 'footer.php'; ?>
