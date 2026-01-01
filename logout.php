<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
| - Session sauber zerstören
| - Danach zurück zur Startseite
| - Login-Overlay erscheint automatisch wieder
*/

$auth->logout();

// Zurück zur Startseite
header('Location: /index.php');
exit;
