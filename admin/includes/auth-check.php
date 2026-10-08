<?php
/**
 * STONE ENERGY INT'L LTD - Admin Authentication & Authorization Guard
 */
require_once __DIR__ . '/../../config/config.php';

// Ensure user is authenticated
Auth::requireLogin();

// Optional module permission check if $requiredPermission is set before inclusion
if (isset($requiredPermission) && !empty($requiredPermission)) {
    Auth::requirePermission($requiredPermission);
}

// Optional Super Admin only check
if (isset($requireSuperAdmin) && $requireSuperAdmin === true) {
    Auth::requireSuperAdmin();
}

$currentUser = Auth::user();
$currentRole = Auth::role();
