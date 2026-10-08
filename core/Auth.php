<?php
/**
 * STONE ENERGY INT'L LTD
 * Enterprise Authentication & Role-Based Access Control (RBAC)
 */

declare(strict_types=1);

class Auth {
    private const SESSION_USER = 'sei_auth_user';
    private const SESSION_ROLES = 'sei_auth_roles';
    private const SESSION_PERMS = 'sei_auth_perms';

    /**
     * Authenticate user credentials with brute-force throttling
     */
    public static function attempt(string $usernameOrEmail, string $password): array {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $maxAttempts = (int)env('LOGIN_MAX_ATTEMPTS', 5);
        $lockoutMins = (int)env('LOGIN_LOCKOUT_MINUTES', 15);

        // Check recent failed attempts in lockout window
        $cutoff = date('Y-m-d H:i:s', time() - ($lockoutMins * 60));
        $recentAttempts = (int)Database::fetchColumn(
            "SELECT COUNT(*) FROM `login_attempts` WHERE `ip_address` = :ip AND `attempted_at` > :cutoff",
            [':ip' => $ip, ':cutoff' => $cutoff]
        );

        if ($recentAttempts >= $maxAttempts) {
            Audit::log('login_lockout', 'auth', null, "Throttled login attempt for IP {$ip} (User: {$usernameOrEmail})");
            return [
                'success' => false,
                'error' => "Too many failed attempts. For security, access is temporarily locked for {$lockoutMins} minutes."
            ];
        }

        // Fetch user record
        $user = Database::fetchOne(
            "SELECT u.*, r.name as role_name, r.slug as role_slug 
             FROM `users` u 
             JOIN `roles` r ON u.role_id = r.id 
             WHERE (u.username = :u OR u.email = :e) LIMIT 1",
            [':u' => $usernameOrEmail, ':e' => $usernameOrEmail]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            // Record failure
            Database::insert('login_attempts', [
                'ip_address' => $ip,
                'username' => $usernameOrEmail,
                'attempted_at' => date('Y-m-d H:i:s')
            ]);
            $remaining = $maxAttempts - ($recentAttempts + 1);
            return [
                'success' => false,
                'error' => "Invalid login credentials." . ($remaining > 0 ? " ({$remaining} attempts remaining before temporary lockout)" : "")
            ];
        }

        // Check if account status is active
        if ($user['status'] !== 'active') {
            return [
                'success' => false,
                'error' => "Account is currently " . htmlspecialchars($user['status']) . ". Please contact Super Admin."
            ];
        }

        // Clear login attempts on success
        Database::delete('login_attempts', '`ip_address` = :ip OR `username` = :ue', [
            ':ip' => $ip,
            ':ue' => $usernameOrEmail
        ]);

        // Secure session regeneration
        session_regenerate_id(true);

        // Load permissions for this user's role
        $perms = Database::fetchAll(
            "SELECT p.slug FROM `permissions` p 
             JOIN `role_permissions` rp ON p.id = rp.permission_id 
             WHERE rp.role_id = :rid",
            [':rid' => $user['role_id']]
        );
        $permSlugs = array_column($perms, 'slug');

        // Store user in session
        $_SESSION[self::SESSION_USER] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role_id' => (int)$user['role_id'],
            'role_slug' => $user['role_slug'],
            'role_name' => $user['role_name'],
            'avatar' => $user['avatar'] ?? null,
            'logged_in_at' => time()
        ];
        $_SESSION[self::SESSION_PERMS] = $permSlugs;

        // Update last login
        Database::update('users', [
            'last_login' => date('Y-m-d H:i:s')
        ], '`id` = :id', [':id' => $user['id']]);

        // Audit Log
        Audit::log('login_success', 'auth', (string)$user['id'], "User {$user['username']} logged in successfully");

        return ['success' => true];
    }

    /**
     * Check if user is currently logged in
     */
    public static function check(): bool {
        return !empty($_SESSION[self::SESSION_USER]['id']);
    }

    /**
     * Get active logged in user array
     */
    public static function user(): ?array {
        return $_SESSION[self::SESSION_USER] ?? null;
    }

    /**
     * Get active user ID
     */
    public static function id(): ?int {
        return $_SESSION[self::SESSION_USER]['id'] ?? null;
    }

    /**
     * Get active user role slug
     */
    public static function role(): ?string {
        return $_SESSION[self::SESSION_USER]['role_slug'] ?? null;
    }

    /**
     * Check if user possesses given role slug
     */
    public static function hasRole(string ...$roles): bool {
        if (!self::check()) {
            return false;
        }
        $currentRole = self::role();
        if ($currentRole === 'super_admin') {
            return true; // Super Admin bypasses all checks
        }
        return in_array($currentRole, $roles, true);
    }

    public static function isSuperAdmin(): bool {
        return self::role() === 'super_admin';
    }

    public static function isAdmin(): bool {
        return in_array(self::role(), ['super_admin', 'admin'], true);
    }

    public static function isEditor(): bool {
        return in_array(self::role(), ['super_admin', 'admin', 'editor'], true);
    }

    public static function isContentManager(): bool {
        return self::check();
    }

    /**
     * Check if user possesses specific permission
     */
    public static function hasPermission(string $permissionSlug): bool {
        if (!self::check()) {
            return false;
        }
        if (self::role() === 'super_admin') {
            return true;
        }
        $perms = $_SESSION[self::SESSION_PERMS] ?? [];
        return in_array($permissionSlug, $perms, true);
    }

    /**
     * Force authentication guard
     */
    public static function requireLogin(): void {
        if (!self::check()) {
            $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '');
            header('Location: ' . admin_url('login.php?redirect=' . $redirect));
            exit;
        }
    }

    /**
     * Require specific permission or abort with 403
     */
    public static function requirePermission(string $permissionSlug): void {
        self::requireLogin();
        if (!self::hasPermission($permissionSlug)) {
            http_response_code(403);
            require_once ROOT_PATH . '403.php';
            exit;
        }
    }

    /**
     * Require Super Admin only
     */
    public static function requireSuperAdmin(): void {
        self::requireLogin();
        if (self::role() !== 'super_admin') {
            http_response_code(403);
            require_once ROOT_PATH . '403.php';
            exit;
        }
    }

    /**
     * Logout and destroy session safely
     */
    public static function logout(): void {
        if (self::check()) {
            $u = self::user();
            Audit::log('logout', 'auth', (string)($u['id'] ?? ''), "User logged out");
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Create secure temporary password reset token
     */
    public static function createPasswordResetToken(string $email): ?string {
        $user = Database::fetchOne("SELECT id, email FROM `users` WHERE `email` = :e", [':e' => $email]);
        if (!$user) {
            return null;
        }

        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour validity

        // Invalidate previous tokens
        Database::delete('password_resets', '`email` = :e', [':e' => $email]);

        Database::insert('password_resets', [
            'email' => $email,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Audit::log('password_reset_request', 'auth', (string)$user['id'], "Password reset requested for {$email}");

        return $rawToken;
    }

    /**
     * Verify token validity
     */
    public static function verifyResetToken(string $rawToken): ?string {
        $tokenHash = hash('sha256', $rawToken);
        $row = Database::fetchOne(
            "SELECT email FROM `password_resets` WHERE `token_hash` = :th AND `expires_at` > :now LIMIT 1",
            [':th' => $tokenHash, ':now' => date('Y-m-d H:i:s')]
        );
        return $row ? $row['email'] : null;
    }

    /**
     * Complete password reset
     */
    public static function resetPasswordWithToken(string $rawToken, string $newPassword): bool {
        $email = self::verifyResetToken($rawToken);
        if (!$email) {
            return false;
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        Database::update('users', ['password_hash' => $newHash], '`email` = :e', [':e' => $email]);
        Database::delete('password_resets', '`email` = :e', [':e' => $email]);

        Audit::log('password_reset_complete', 'auth', null, "Password reset completed for {$email}");
        return true;
    }
}
