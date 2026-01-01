<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$db = db_connect();
$auth = new AuthService($db);

/* Auto-Login */
$auth->autoLoginFromCookie();

/* POST */
require_once __DIR__ . '/auth-handler.php';

/* Status */
$isLoggedIn = $auth->isLoggedIn();
$showAuth = !$isLoggedIn;
$forceUsername = $isLoggedIn && $auth->needsUsernameSetup();

/* Session Ablauf */
$sessionExpiringSoon = false;
if ($isLoggedIn) {
    if ($_SESSION['session_expires'] < time()) {
        $auth->logout();
        header('Location: /index.php');
        exit;
    }
    if ($_SESSION['session_expires'] < time() + 3600) {
        $sessionExpiringSoon = true;
    }
}

/* UI Status */
$authMode = $_SESSION['auth_mode'] ?? 'login';
$authError = $_SESSION['auth_error'] ?? null;
$usernameError = $_SESSION['username_error'] ?? null;

unset($_SESSION['auth_mode'], $_SESSION['auth_error'], $_SESSION['username_error']);
