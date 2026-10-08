<?php
/**
 * STONE ENERGY INT'L LTD
 * View & Template Security Helpers
 */

declare(strict_types=1);

/**
 * Escapes output for safe HTML injection (XSS Prevention)
 */
function e(?string $string): string {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Clean URL builder
 */
function url(string $path = ''): string {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Admin URL builder
 */
function admin_url(string $path = ''): string {
    return ADMIN_URL . ltrim($path, '/');
}

/**
 * Asset URL helper
 */
function asset(string $path): string {
    $cleanPath = preg_replace('#^assets/#', '', ltrim($path, '/'));
    return ASSETS_URL . $cleanPath;
}

/**
 * Upload URL helper with fallback placeholder
 */
function upload_url(?string $path, string $fallback = 'images/placeholder.svg'): string {
    if (empty($path)) {
        return asset($fallback);
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return BASE_URL . ltrim($path, '/');
}

/**
 * Slug generator
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'item-' . time() : $text;
}

/**
 * Format datetime nicely
 */
function format_date(?string $datetime, string $format = 'M j, Y'): string {
    if (!$datetime) {
        return 'N/A';
    }
    try {
        $dt = new DateTime($datetime);
        return $dt->format($format);
    } catch (Exception) {
        return $datetime;
    }
}

/**
 * Format relative time
 */
function time_ago(?string $datetime): string {
    if (!$datetime) {
        return 'never';
    }
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = round($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = round($diff / 3600);
        return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = round($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}

/**
 * Flash notification helper
 */
function set_flash(string $type, string $message): void {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][$type][] = $message;
}

/**
 * Retrieve and clear flash notifications
 */
function get_flashes(): array {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/**
 * Display flash alerts in HTML
 */
function render_flash(): string {
    $flashes = get_flashes();
    if (empty($flashes)) {
        return '';
    }
    $html = '<div class="flash-messages-container">';
    foreach ($flashes as $type => $messages) {
        $cssClass = match ($type) {
            'success' => 'alert-success',
            'error', 'danger' => 'alert-danger',
            'warning' => 'alert-warning',
            default => 'alert-info'
        };
        foreach ($messages as $msg) {
            $html .= '<div class="alert ' . $cssClass . ' alert-dismissible" role="alert">';
            $html .= '<span>' . e($msg) . '</span>';
            $html .= '<button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>';
            $html .= '</div>';
        }
    }
    $html .= '</div>';
    return $html;
}

/**
 * Truncate text cleanly
 */
function truncate(string $text, int $limit = 150): string {
    $clean = strip_tags($text);
    if (mb_strlen($clean) <= $limit) {
        return $clean;
    }
    $sub = mb_substr($clean, 0, $limit);
    $lastSpace = mb_strrpos($sub, ' ');
    if ($lastSpace !== false) {
        $sub = mb_substr($sub, 0, $lastSpace);
    }
    return $sub . '...';
}
