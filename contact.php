<?php
/**
 * STONE ENERGY INT'L LTD - Contact Headquarters
 */
require_once __DIR__ . '/config/config.php';

$pageTitle = "Contact STONE ENERGY INT'L LTD | Ibadan, Oyo State, Nigeria";
$pageDescription = "Get in touch with STONE ENERGY INT'L LTD headquarters in Ibadan, Oyo State. Reach our procurement officers, general contracting team, and project directors.";

$companyName    = setting('company_name', "STONE ENERGY INT'L LTD");
$officeAddress  = setting('office_address', '22, Oyelude Layout, Aba Alfa, Ojo, Ibadan, Oyo State, Nigeria.');
$phonePrimary   = setting('phone_primary', '08037745881');
$phoneSecondary = setting('phone_secondary', '08084949840');
$emailPrimary   = setting('email_primary', '[ADD COMPANY EMAIL]');
$businessHours  = setting('business_hours', 'Monday - Friday: 8:00 AM - 5:00 PM');
$googleMaps     = setting('google_maps_embed', '');

$success = false;
$errorMessage = '';

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrAbort();

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $errorMessage = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please provide a valid email address.";
    } else {
        try {
            $msgId = Database::insert('contact_messages', [
                'name'       => $name,
                'email'      => $email,
                'phone'      => $phone ?: null,
                'subject'    => $subject,
                'message'    => $message,
                'is_read'    => 0,
                'is_archived'=> 0,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Notify admin
            Mailer::sendContactAdminAlert([
                'name'    => $name,
                'email'   => $email,
                'phone'   => $phone,
                'subject' => $subject,
                'message' => $message
            ]);

            $success = true;
        } catch (Exception $e) {
            error_log("Contact message error: " . $e->getMessage());
            $errorMessage = "Failed to transmit message. Please contact our phone lines directly.";
        }
    }
}

include INCLUDES_PATH . 'header.php';
?>

<div class="section-dark" style="padding: 60px 0; border-bottom: 3px solid var(--color-accent-500);">
    <div class="container">
        <span class="section-tag section-tag-light">GET IN TOUCH</span>
        <h1 style="font-size: 2.8rem; color: #fff; margin-bottom: 12px;">Contact Our Corporate Office</h1>
        <p style="color: #cbd5e1; max-width: 720px; font-size: 1.1rem; margin-bottom: 0;">
            Connect with our general contracting, multi-sector procurement, and logistics teams in Ibadan, Oyo State.
        </p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1.2fr 1.8fr; gap: 48px; align-items: start;">
            <!-- Corporate Contact Cards -->
            <div>
                <h3 style="font-size: 1.45rem; margin-bottom: 24px;">Headquarters Information</h3>

                <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); padding: 26px; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; gap: 14px; align-items: flex-start;">
                        <div style="width: 44px; height: 44px; background: rgba(217, 119, 6, 0.1); color: var(--color-accent-500); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </div>
                        <div>
                            <h4 style="font-size: 1rem; margin-bottom: 4px;">Physical Address</h4>
                            <p style="font-size: 0.92rem; color: var(--color-dark-600); margin-bottom: 0; line-height: 1.6;">
                                <?= e($officeAddress) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); padding: 26px; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; gap: 14px; align-items: flex-start;">
                        <div style="width: 44px; height: 44px; background: rgba(217, 119, 6, 0.1); color: var(--color-accent-500); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        </div>
                        <div>
                            <h4 style="font-size: 1rem; margin-bottom: 4px;">Telephone Hotlines</h4>
                            <div style="font-size: 0.92rem; color: var(--color-dark-600);">
                                <a href="tel:<?= e($phonePrimary) ?>" style="color: var(--color-primary-900); font-weight: 600;"><?= e($phonePrimary) ?></a><br>
                                <a href="tel:<?= e($phoneSecondary) ?>" style="color: var(--color-primary-900); font-weight: 600;"><?= e($phoneSecondary) ?></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); padding: 26px; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; gap: 14px; align-items: flex-start;">
                        <div style="width: 44px; height: 44px; background: rgba(217, 119, 6, 0.1); color: var(--color-accent-500); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        </div>
                        <div>
                            <h4 style="font-size: 1rem; margin-bottom: 4px;">Official Email</h4>
                            <div style="font-size: 0.92rem; color: var(--color-dark-600);">
                                <span><?= e($emailPrimary) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); padding: 26px; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; gap: 14px; align-items: flex-start;">
                        <div style="width: 44px; height: 44px; background: rgba(217, 119, 6, 0.1); color: var(--color-accent-500); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div>
                            <h4 style="font-size: 1rem; margin-bottom: 4px;">Office Hours</h4>
                            <div style="font-size: 0.92rem; color: var(--color-dark-600);">
                                <span><?= e($businessHours) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Submission Form -->
            <div class="form-box">
                <h3 style="font-size: 1.45rem; margin-bottom: 12px; color: var(--color-primary-900);">Send a Direct Message</h3>
                <p style="font-size: 0.92rem; color: var(--color-dark-600); margin-bottom: 24px;">
                    For tenders, procurement partnerships, or general corporate inquiries, leave a message below.
                </p>

                <?php if ($success): ?>
                    <div class="alert alert-success" style="margin-bottom: 24px;">
                        <span>Thank you. Your message has been received by our office. An officer will respond shortly.</span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger" style="margin-bottom: 24px;">
                        <span><?= e($errorMessage) ?></span>
                    </div>
                <?php endif; ?>

                <form action="<?= url('contact.php') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Your Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Full Name" value="<?= e($_POST['name'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" required placeholder="your@email.com" value="<?= e($_POST['email'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="080XXXXXXXX" value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Subject <span class="required">*</span></label>
                            <input type="text" name="subject" class="form-control" required placeholder="Nature of inquiry" value="<?= e($_POST['subject'] ?? '') ?>">
                        </div>

                        <div class="form-group-full form-group">
                            <label class="form-label">Your Message <span class="required">*</span></label>
                            <textarea name="message" class="form-control" required style="min-height: 140px;" placeholder="Write your message here..."><?= e($_POST['message'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 10px;">
                        SEND MESSAGE &rarr;
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Map & Geographic Headquarters Location -->
<section style="background: var(--color-gray-100); padding: 0 0 60px 0;">
    <div class="container">
        <div style="background: var(--color-white); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); border: 1px solid var(--color-gray-200);">
            <div style="padding: 24px 30px; border-bottom: 1px solid var(--color-gray-200); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 4px; color: var(--color-primary-900);">Physical Office Location</h3>
                    <p style="font-size: 0.9rem; color: var(--color-dark-600); margin: 0;">22, Oyelude Layout, Aba Alfa, Ojo, Ibadan, Oyo State, Nigeria</p>
                </div>
                <a href="https://maps.google.com/?q=Ojo+Ibadan+Oyo+State+Nigeria" target="_blank" rel="noopener noreferrer" class="btn btn-outline-gold btn-sm">
                    Open in Google Maps &rarr;
                </a>
            </div>
            <div style="width: 100%; height: 380px; position: relative; background: #e2e8f0;">
                <?php 
                $mapEmbed = setting('google_maps_embed', '');
                if (!empty($mapEmbed)): 
                    if (str_contains($mapEmbed, '<iframe')): ?>
                        <div class="responsive-map" style="width: 100%; height: 100%;">
                            <?= $mapEmbed ?>
                        </div>
                    <?php else: ?>
                        <iframe src="<?= e($mapEmbed) ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    <?php endif; 
                else: ?>
                    <iframe 
                        src="https://maps.google.com/maps?q=Ojo,+Ibadan,+Oyo+State,+Nigeria&t=&z=14&ie=UTF8&iwloc=&output=embed" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Stone Energy International Office Location">
                    </iframe>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include INCLUDES_PATH . 'footer.php'; ?>
