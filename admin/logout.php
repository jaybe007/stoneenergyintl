<?php
/**
 * STONE ENERGY INT'L LTD - Secure Admin Logout
 */
require_once __DIR__ . '/../config/config.php';

Auth::logout();
set_flash('success', 'You have been logged out securely.');
header('Location: ' . admin_url('login.php'));
exit;
