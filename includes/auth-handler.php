<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

$action = $_POST['action'] ?? null;

switch ($action) {

	case 'login':
		$remember = !empty($_POST['remember']);

		if (!$auth->login(
			$_POST['email'] ?? '',
			$_POST['password'] ?? '',
			$remember
		)) {
			$_SESSION['auth_error'] = 'Login fehlgeschlagen';
			$_SESSION['auth_mode']  = 'login';
		}
		break;


    case 'register':
        if (($_POST['password'] ?? '') !== ($_POST['password_confirm'] ?? '')) {
            $_SESSION['auth_error'] = 'Passwörter stimmen nicht überein';
            $_SESSION['auth_mode'] = 'register';
            break;
        }
        if (!$auth->register($_POST['email'] ?? '', $_POST['password'] ?? '')) {
            $_SESSION['auth_error'] = 'Registrierung fehlgeschlagen';
            $_SESSION['auth_mode'] = 'register';
            break;
        }
        $auth->login($_POST['email'], $_POST['password']);
        break;

    case 'set_username':
        if (!$auth->updateUsername($_POST['username'] ?? '')) {
            $_SESSION['username_error'] = 'Username ungültig oder vergeben';
        }
        break;

    case 'extend_session':
        $_SESSION['session_expires'] = time() + (7*24*60*60);
        break;

    case 'logout':
        $auth->logout();
        header('Location: /index.php');
        exit;
}
