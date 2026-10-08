<?php
/**
 * STONE ENERGY INT'L LTD - Admin Entry Point
 */
require_once __DIR__ . '/../config/config.php';

if (Auth::check()) {
    header('Location: ' . admin_url('dashboard.php'));
} else {
    header('Location: ' . admin_url('login.php'));
}
exit;
