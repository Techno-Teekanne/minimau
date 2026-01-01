<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$db = db_connect();
$auth = new AuthService($db);

/* Cleanup */
$auth->cleanupUnverifiedAccounts();

/* Auth Session */
$auth->bootstrapSession();

/* UI State */
$authMode = 'login';
$authError = null;
$usernameError = null;

/* POST */
require_once __DIR__ . '/auth-handler.php';

/* Public Auth Routes */
$currentPage = basename($_SERVER['PHP_SELF']);
$isPublicAuthRoute = in_array($currentPage, ['verify-email.php'], true);

/* Status */
$isLoggedIn = $auth->isLoggedIn();
$showAuth = !$isLoggedIn && !$isPublicAuthRoute;
$forceUsername = $isLoggedIn && $auth->needsUsernameSetup();
