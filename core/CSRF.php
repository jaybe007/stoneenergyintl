<?php
/**
 * STONE ENERGY INT'L LTD
 * Cross-Site Request Forgery (CSRF) Protection
 */

declare(strict_types=1);

class CSRF {
    private const SESSION_KEY = 'sei_csrf_token';

    /**
     * Retrieve or generate CSRF token
     */
    public static function token(): string {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Generate HTML hidden input field for forms
     */
    public static function field(): string {
        $token = self::token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify submitted CSRF token
     */
    public static function verify(?string $token = null): bool {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }

        if (empty($token) || empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    /**
     * Guard function: rejects request if invalid CSRF token
     */
    public static function validateOrAbort(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!self::verify()) {
                http_response_code(403);
                if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'Security token expired. Please refresh and try again.']);
                    exit;
                }
                die("Security Validation Failed: CSRF Token Invalid or Expired. Please go back, refresh the page, and resubmit.");
            }
        }
    }
}

// Global procedural shortcuts
function csrf_token(): string {
    return CSRF::token();
}

function csrf_field(): string {
    return CSRF::field();
}

function verify_csrf_token(?string $token = null): bool {
    return CSRF::verify($token);
}
