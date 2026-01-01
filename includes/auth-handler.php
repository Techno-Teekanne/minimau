<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

$action = $_POST['action'] ?? null;

switch ($action) {

	case 'login':
		if (!$auth->login(
			$_POST['email'] ?? '',
			$_POST['password'] ?? ''
		)) {
            $authError = 'Login fehlgeschlagen';
            $authMode  = 'login';
            break;
		}
        header('Location: /dashboard.php');
        exit;
		break;


    case 'register':
        if (($_POST['password'] ?? '') !== ($_POST['password_confirm'] ?? '')) {
            $authError = 'Passwörter stimmen nicht überein';
            $authMode = 'register';
            break;
        }
        if (!$auth->register($_POST['email'] ?? '', $_POST['password'] ?? '', $_POST['register_code'] ?? '')) {
            $authError = 'Registrierung fehlgeschlagen';
            $authMode = 'register';
            break;
        }
        $auth->login($_POST['email'], $_POST['password']);
        header('Location: /dashboard.php');
        exit;
        break;

    case 'set_username':
        if (!$auth->updateUsername($_POST['username'] ?? '')) {
            $usernameError = 'Username ungültig oder vergeben';
            break;
        }
        header('Location: ' . ($_SERVER['REQUEST_URI'] ?? '/index.php'));
        exit;
        break;

    case 'request_email_verification':
        if (!$auth->requestEmailVerification()) {
            $authError = 'Verifizierungs-Mail konnte nicht gesendet werden';
        }
        header('Location: ' . ($_SERVER['REQUEST_URI'] ?? '/index.php'));
        exit;
        break;

    case 'logout':
        $auth->logout();
        header('Location: /index.php');
        exit;
}

if ($action === null && isset($_POST['mode'])) {
    $authMode = $_POST['mode'] === 'register' ? 'register' : 'login';
}
