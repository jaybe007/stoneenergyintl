<?php
/**
 * STONE ENERGY INT'L LTD - Reusable Quick Request for Quote (RFQ) Modal
 */
?>
<div class="modal-overlay" id="quickRfqModal" role="dialog" aria-modal="true" aria-labelledby="rfqModalTitle">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="rfqModalTitle">Quick Request for Quotation</h3>
            <button type="button" class="modal-close" data-modal-close aria-label="Close Modal">&times;</button>
        </div>
        <div class="modal-body">
            <form action="<?= url('quote.php') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="is_quick_rfq" value="1">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="customer_name" class="form-control" required placeholder="Your full name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company / Organization</label>
                        <input type="text" name="company_name" class="form-control" placeholder="Company legal name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="corporate@email.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number <span class="required">*</span></label>
                        <input type="tel" name="phone" class="form-control" required placeholder="080XXXXXXXX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Industry Sector <span class="required">*</span></label>
                        <select name="industry" class="form-control" required>
                            <option value="">Select Target Industry</option>
                            <option value="Oil & Gas">Oil &amp; Gas Supply</option>
                            <option value="Building Construction">Building Construction &amp; Civil Works</option>
                            <option value="Hospital & Healthcare">Hospital Equipment &amp; Consumables</option>
                            <option value="Agro-Allied">Agro-Allied Supplies</option>
                            <option value="Agroprocessing">Agroprocessing Plant Support</option>
                            <option value="General Contracting">General Contracting &amp; Procurement</option>
                            <option value="Industrial Supply">Industrial Supplies &amp; Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Product / Service Required <span class="required">*</span></label>
                        <input type="text" name="service_or_product" class="form-control" required placeholder="e.g. 500 Bags Fe500 Rebar, ICU Bed, etc.">
                    </div>
                    <div class="form-group-full form-group">
                        <label class="form-label">Quantity &amp; Specifications</label>
                        <input type="text" name="quantity" class="form-control" placeholder="Estimated quantity / size / metrics">
                    </div>
                    <div class="form-group-full form-group">
                        <label class="form-label">Brief Project Description <span class="required">*</span></label>
                        <textarea name="project_description" class="form-control" style="min-height: 90px;" required placeholder="Describe technical specifications, site location, or delivery timelines..."></textarea>
                    </div>
                </div>

                <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                    <a href="<?= url('quote.php') ?>" style="font-size: 0.85rem; color: var(--color-accent-500); font-weight: 600;">
                        Open Detailed Multi-Item RFQ Form &rarr;
                    </a>
                    <button type="submit" class="btn btn-primary">
                        SUBMIT RFQ NOW
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
