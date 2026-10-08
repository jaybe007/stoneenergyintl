<?php
/**
 * STONE ENERGY INT'L LTD
 * Core Configuration & Bootstrap
 */

// Strict typing and timezone
declare(strict_types=1);
date_default_timezone_set('Africa/Lagos');

// Base Paths
define('ROOT_PATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
define('CONFIG_PATH', ROOT_PATH . 'config' . DIRECTORY_SEPARATOR);
define('CORE_PATH', ROOT_PATH . 'core' . DIRECTORY_SEPARATOR);
define('INCLUDES_PATH', ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR);
define('UPLOADS_PATH', ROOT_PATH . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);
define('LOGS_PATH', ROOT_PATH . 'logs' . DIRECTORY_SEPARATOR);

// Simple .env Loader (Clean vanilla implementation)
if (!function_exists('load_env')) {
    function load_env(string $envPath): void {
        if (!file_exists($envPath)) {
            return;
        }
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                // Strip quotes if present
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }
                if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }
}

// Load .env
load_env(ROOT_PATH . '.env');

// Helper to get environment variable with fallback
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        $val = getenv($key);
        if ($val === false) {
            return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }
        return match (strtolower((string)$val)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $val,
        };
    }
}

// Dynamic Base URL calculation
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
// Normalize script directory to find application root
$parts = explode('/', trim($scriptDir, '/'));
$baseParts = [];
foreach ($parts as $part) {
    if ($part === 'admin' || $part === 'api' || $part === 'core' || $part === 'includes') {
        break;
    }
    if ($part !== '') {
        $baseParts[] = $part;
    }
}
$calculatedBase = $protocol . $host . '/' . (count($baseParts) ? implode('/', $baseParts) . '/' : '');
$configuredUrl = env('APP_URL', rtrim($calculatedBase, '/'));
define('BASE_URL', rtrim($configuredUrl, '/') . '/');
define('ADMIN_URL', BASE_URL . 'admin/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_URL', ASSETS_URL . 'uploads/');

// Error Reporting & Logging
$debug = (bool)env('APP_DEBUG', false);
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}
ini_set('log_errors', '1');
ini_set('error_log', LOGS_PATH . 'app.log');

// Secure Session Initialization
if (session_status() === PHP_SESSION_NONE) {
    $lifetime = (int)env('SESSION_LIFETIME', 7200);
    ini_set('session.gc_maxlifetime', (string)$lifetime);
    
    // Cookie parameters
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_name('SEI_CORP_SESS');
    session_start();
}

// Autoload core files
require_once CONFIG_PATH . 'database.php';
require_once CORE_PATH . 'View.php';
require_once CORE_PATH . 'CSRF.php';
require_once CORE_PATH . 'Settings.php';
require_once CORE_PATH . 'Auth.php';
require_once CORE_PATH . 'Audit.php';
require_once CORE_PATH . 'Mailer.php';
require_once CORE_PATH . 'Uploader.php';
