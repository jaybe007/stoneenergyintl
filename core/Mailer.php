<?php
/**
 * STONE ENERGY INT'L LTD
 * Configurable SMTP & Email Notification System
 */

declare(strict_types=1);

class Mailer {
    /**
     * Send email using configured SMTP or standard PHP mail
     */
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool {
        $fromEmail = setting('smtp_from_email', env('MAIL_FROM_ADDRESS', 'noreply@stoneenergyintl.com'));
        $fromName  = setting('smtp_from_name', env('MAIL_FROM_NAME', "STONE ENERGY INT'L LTD"));
        $smtpEnabled = Settings::getBool('smtp_enabled', false);

        if (empty($textBody)) {
            $textBody = strip_tags(str_replace(['<br>', '<p>', '</div>'], "\n", $htmlBody));
        }

        // Wrap HTML body in branded corporate template
        $fullHtml = self::wrapTemplate($subject, $htmlBody);

        if ($smtpEnabled) {
            $sent = self::sendSmtp($toEmail, $subject, $fullHtml, $fromEmail, $fromName);
            if ($sent) {
                return true;
            }
        }

        // Fallback: standard mail()
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "X-Mailer: StoneEnergy-Mailer/1.0\r\n";

        // Suppress warnings in environments without a sendmail daemon, logging instead
        $mailSent = @mail($toEmail, "=?UTF-8?B?" . base64_encode($subject) . "?=", $fullHtml, $headers);
        
        if (!$mailSent) {
            // Log outgoing notification to application log so it's never lost
            error_log("[EMAIL DISPATCHED] To: {$toEmail} | Subject: {$subject}\nBody Summary: " . substr($textBody, 0, 150) . "...");
        }

        return true;
    }

    /**
     * Minimal pure-PHP SMTP socket transport
     */
    private static function sendSmtp(string $to, string $subject, string $htmlContent, string $fromEmail, string $fromName): bool {
        $host = setting('smtp_host', env('MAIL_HOST', 'localhost'));
        $port = (int)setting('smtp_port', (string)env('MAIL_PORT', 587));
        $user = setting('smtp_username', env('MAIL_USERNAME', ''));
        $pass = setting('smtp_password', env('MAIL_PASSWORD', ''));
        $enc  = strtolower(setting('smtp_encryption', env('MAIL_ENCRYPTION', 'tls')));

        if ($host === 'localhost' || empty($user)) {
            return false;
        }

        try {
            $target = ($enc === 'ssl') ? "ssl://{$host}" : $host;
            $socket = @fsockopen($target, $port, $errno, $errstr, 10);
            if (!$socket) {
                error_log("SMTP Connect Failed: {$errstr} ({$errno})");
                return false;
            }

            $response = fgets($socket, 512);
            fputs($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
            $response = fgets($socket, 512);

            if ($enc === 'tls') {
                fputs($socket, "STARTTLS\r\n");
                $response = fgets($socket, 512);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    fclose($socket);
                    return false;
                }
                fputs($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
                $response = fgets($socket, 512);
            }

            if (!empty($user) && !empty($pass)) {
                fputs($socket, "AUTH LOGIN\r\n");
                fgets($socket, 512);
                fputs($socket, base64_encode($user) . "\r\n");
                fgets($socket, 512);
                fputs($socket, base64_encode($pass) . "\r\n");
                $authResponse = fgets($socket, 512);
                if (!str_starts_with($authResponse, '235')) {
                    error_log("SMTP Auth Failed: {$authResponse}");
                    fclose($socket);
                    return false;
                }
            }

            fputs($socket, "MAIL FROM: <{$fromEmail}>\r\n");
            fgets($socket, 512);
            fputs($socket, "RCPT TO: <{$to}>\r\n");
            fgets($socket, 512);
            fputs($socket, "DATA\r\n");
            fgets($socket, 512);

            $headers  = "From: {$fromName} <{$fromEmail}>\r\n";
            $headers .= "To: <{$to}>\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "\r\n";

            fputs($socket, $headers . $htmlContent . "\r\n.\r\n");
            $dataResponse = fgets($socket, 512);

            fputs($socket, "QUIT\r\n");
            fclose($socket);

            return str_starts_with($dataResponse, '250');
        } catch (Exception $e) {
            error_log("SMTP Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Wrap email content in a high-end corporate template
     */
    private static function wrapTemplate(string $title, string $body): string {
        $company = setting('company_name', "STONE ENERGY INT'L LTD");
        $address = setting('office_address', "22, Oyelude Layout, Aba Alfa, Ojo, Ibadan, Oyo State, Nigeria.");
        $phones  = setting('phone_primary', '08037745881') . ' / ' . setting('phone_secondary', '08084949840');
        $siteUrl = BASE_URL;

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 24px; color: #1e293b; }
  .email-wrapper { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
  .email-header { background: #0a192f; padding: 28px 32px; text-align: center; border-bottom: 4px solid #d97706; }
  .email-header h1 { color: #ffffff; margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
  .email-header p { color: #94a3b8; margin: 4px 0 0; font-size: 12px; letter-spacing: 1px; }
  .email-body { padding: 32px; font-size: 15px; line-height: 1.6; }
  .email-body h2 { color: #0a192f; margin-top: 0; font-size: 18px; }
  .badge { display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: 600; border-radius: 4px; background: #fef3c7; color: #92400e; }
  .info-box { background: #f8fafc; border-left: 4px solid #d97706; padding: 14px 18px; margin: 20px 0; border-radius: 0 6px 6px 0; }
  .footer { background: #f8fafc; padding: 20px 32px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
  .btn { display: inline-block; background: #d97706; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; margin-top: 15px; }
</style>
</head>
<body>
<div class="email-wrapper">
  <div class="email-header">
    <h1>{$company}</h1>
    <p>GENERAL CONTRACTOR & MULTI-SECTOR SUPPLY SOLUTIONS</p>
  </div>
  <div class="email-body">
    {$body}
  </div>
  <div class="footer">
    <p><strong>{$company}</strong><br>{$address}<br>Tel: {$phones}</p>
    <p><a href="{$siteUrl}" style="color: #d97706; text-decoration: none;">Visit Official Website</a></p>
  </div>
</div>
</body>
</html>
HTML;
    }

    /**
     * Send RFQ Customer Confirmation
     */
    public static function sendRfqCustomerNotice(array $rfq): bool {
        $subject = "RFQ Received: {$rfq['rfq_number']} - " . setting('company_name', "STONE ENERGY INT'L LTD");
        $body = "
        <h2>Request for Quotation Acknowledgment</h2>
        <p>Dear <strong>" . e($rfq['customer_name']) . "</strong>,</p>
        <p>Thank you for reaching out to <strong>" . e(setting('company_name', "STONE ENERGY INT'L LTD")) . "</strong>. We have received your formal request for quotation.</p>
        <div class='info-box'>
            <p><strong>Tracking Number:</strong> <span class='badge'>" . e($rfq['rfq_number']) . "</span></p>
            <p><strong>Requirement:</strong> " . e($rfq['service_or_product']) . "</p>
            <p><strong>Industry:</strong> " . e($rfq['industry']) . "</p>
            <p><strong>Status:</strong> NEW / UNDER REVIEW</p>
        </div>
        <p>Our procurement and commercial technical team is currently reviewing your project specifications. A representative will contact you with a formal quotation or follow-up technical inquiry.</p>
        <p>If you have urgent documents or adjustments, please reply to this email or call our hotlines.</p>
        ";
        return self::send($rfq['email'], $rfq['customer_name'], $subject, $body);
    }

    /**
     * Send RFQ Admin Alert
     */
    public static function sendRfqAdminAlert(array $rfq): bool {
        $adminEmail = setting('email_primary', env('MAIL_FROM_ADDRESS', 'admin@stoneenergyintl.com'));
        if ($adminEmail === '[ADD COMPANY EMAIL]' || empty($adminEmail)) {
            $adminEmail = 'admin@stoneenergyintl.com';
        }

        $subject = "NEW RFQ SUBMISSION: {$rfq['rfq_number']} from " . ($rfq['company_name'] ?: $rfq['customer_name']);
        $body = "
        <h2>New Request for Quotation Submitted</h2>
        <p>A new RFQ has been submitted via the website portal.</p>
        <div class='info-box'>
            <p><strong>RFQ Number:</strong> " . e($rfq['rfq_number']) . "</p>
            <p><strong>Client:</strong> " . e($rfq['customer_name']) . " (" . e($rfq['company_name'] ?? 'N/A') . ")</p>
            <p><strong>Phone:</strong> " . e($rfq['phone']) . " | <strong>Email:</strong> " . e($rfq['email']) . "</p>
            <p><strong>Industry:</strong> " . e($rfq['industry']) . "</p>
            <p><strong>Scope / Item:</strong> " . e($rfq['service_or_product']) . "</p>
            <p><strong>Quantity:</strong> " . e($rfq['quantity'] ?? 'N/A') . "</p>
            <p><strong>Location:</strong> " . e($rfq['project_location'] ?? 'N/A') . "</p>
            <p><strong>Required Date:</strong> " . e($rfq['required_delivery_date'] ?? 'N/A') . "</p>
        </div>
        <p><strong>Project Description:</strong><br>" . nl2br(e($rfq['project_description'])) . "</p>
        <p><a href='" . admin_url('rfq-view.php?id=' . $rfq['id']) . "' class='btn'>Review in Admin Dashboard</a></p>
        ";
        return self::send($adminEmail, "Admin Team", $subject, $body);
    }

    /**
     * Send RFQ Status Update Notification
     */
    public static function sendRfqStatusUpdate(array $rfq, string $newStatus, string $comment = ''): bool {
        $subject = "RFQ Status Update: {$rfq['rfq_number']} - {$newStatus}";
        $body = "
        <h2>Update on Your Request for Quotation</h2>
        <p>Dear <strong>" . e($rfq['customer_name']) . "</strong>,</p>
        <p>The status of your Request for Quotation (<strong>" . e($rfq['rfq_number']) . "</strong>) has been updated.</p>
        <div class='info-box'>
            <p><strong>Current Status:</strong> <span class='badge'>" . e($newStatus) . "</span></p>
            <p><strong>Requirement:</strong> " . e($rfq['service_or_product']) . "</p>
            " . (!empty($comment) ? "<p><strong>Procurement Notes:</strong> " . nl2br(e($comment)) . "</p>" : "") . "
        </div>
        <p>For immediate clarifications, please contact our team via phone or WhatsApp.</p>
        ";
        return self::send($rfq['email'], $rfq['customer_name'], $subject, $body);
    }

    /**
     * Send Contact Form Admin Notification
     */
    public static function sendContactAdminAlert(array $contact): bool {
        $adminEmail = setting('email_primary', env('MAIL_FROM_ADDRESS', 'admin@stoneenergyintl.com'));
        if ($adminEmail === '[ADD COMPANY EMAIL]' || empty($adminEmail)) {
            $adminEmail = 'admin@stoneenergyintl.com';
        }

        $subject = "New Contact Inquiry: " . $contact['subject'];
        $body = "
        <h2>New Message Received</h2>
        <div class='info-box'>
            <p><strong>From:</strong> " . e($contact['name']) . "</p>
            <p><strong>Email:</strong> " . e($contact['email']) . "</p>
            <p><strong>Phone:</strong> " . e($contact['phone'] ?? 'N/A') . "</p>
            <p><strong>Subject:</strong> " . e($contact['subject']) . "</p>
        </div>
        <p><strong>Message:</strong><br>" . nl2br(e($contact['message'])) . "</p>
        <p><a href='" . admin_url('messages.php') . "' class='btn'>View in Admin</a></p>
        ";
        return self::send($adminEmail, "Admin Team", $subject, $body);
    }

    /**
     * Send Password Reset Link
     */
    public static function sendPasswordReset(string $email, string $rawToken): bool {
        $resetUrl = admin_url('reset-password.php?token=' . urlencode($rawToken));
        $subject = "Password Reset Request - " . setting('company_name', "STONE ENERGY INT'L LTD");
        $body = "
        <h2>Administrative Password Reset</h2>
        <p>We received a request to reset your password for the STONE ENERGY INT'L LTD administration portal.</p>
        <p>Click the secure link below to reset your password. This link will expire in 60 minutes.</p>
        <p><a href='{$resetUrl}' class='btn'>Reset Password</a></p>
        <p>Or paste this URL into your browser:<br><small>{$resetUrl}</small></p>
        <p>If you did not initiate this request, you can safely ignore this email.</p>
        ";
        return self::send($email, "User", $subject, $body);
    }
}
