<?php
require_once __DIR__ . '/includes/init.php';

/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/
$isLoggedIn        = $auth->isLoggedIn();
$showAuth         = !$isLoggedIn;
$forceUsername    = $isLoggedIn && $auth->needsUsernameSetup();

$mode             = $_POST['mode']   ?? 'login';
$action           = $_POST['action'] ?? null;

$error            = null;
$usernameError    = null;

/*
|--------------------------------------------------------------------------
| POST Handling
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // LOGIN
    if ($action === 'login') {
        if ($auth->login($_POST['email'], $_POST['password'])) {
            header('Location: /index.php');
            exit;
        }
        $error = 'Login fehlgeschlagen';
        $mode  = 'login';
    }

    // REGISTER
    if ($action === 'register') {
        if ($_POST['password'] !== $_POST['password_confirm']) {
            $error = 'Passwörter stimmen nicht überein';
            $mode  = 'register';
        } elseif ($auth->register($_POST['email'], $_POST['password'])) {
            $auth->login($_POST['email'], $_POST['password']);
            header('Location: /index.php');
            exit;
        } else {
            $error = 'Registrierung fehlgeschlagen';
            $mode  = 'register';
        }
    }

    // USERNAME SETZEN (Zwang)
    if ($action === 'set_username') {
        if ($auth->updateUsername($_POST['username'])) {
            header('Location: /index.php');
            exit;
        }
        $usernameError = 'Username ungültig oder bereits vergeben';
    }

    // NUR Modus wechseln
    if ($action === null && isset($_POST['mode'])) {
        $mode = $_POST['mode'];
    }
}

/*
|--------------------------------------------------------------------------
| Daten laden
|--------------------------------------------------------------------------
*/
$achievements = $isLoggedIn
    ? $auth->getUserAchievements()
    : [];

$pageTitle = 'Start';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ================= MAIN CONTENT ================= -->

<div class="panel-grid">
    <div class="panel mau-glow">
        <div class="panel-header"><strong>Willkommen</strong></div>
        <div class="panel-body">
            <p class="panel-subtitle">Modulares Autarkes Universalsystem</p>
        </div>
    </div>
</div>

<!-- ================= LOGIN / REGISTER OVERLAY ================= -->

<?php if ($showAuth): ?>
<div class="auth-overlay">
    <div class="auth-modal">

        <?php if ($mode === 'login'): ?>
            <h1>Login</h1>

            <?php if ($error): ?>
                <div class="auth-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="E-Mail" required>
                <input type="password" name="password" placeholder="Passwort" required>
                <button class="button">Login</button>
            </form>

            <form method="post">
                <input type="hidden" name="mode" value="register">
                <button class="link-button">Account erstellen</button>
            </form>

        <?php else: ?>
            <h1>Registrieren</h1>

            <?php if ($error): ?>
                <div class="auth-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="action" value="register">
                <input type="email" name="email" placeholder="E-Mail" required>
                <input type="password" name="password" placeholder="Passwort" required>
                <input type="password" name="password_confirm" placeholder="Passwort wiederholen" required>
                <button class="button">Registrieren</button>
            </form>

            <form method="post">
                <input type="hidden" name="mode" value="login">
                <button class="link-button">Zurück zum Login</button>
            </form>
        <?php endif; ?>

    </div>
</div>
<?php endif; ?>

<!-- ================= USERNAME ZWANG ================= -->

<?php if ($forceUsername): ?>
<div class="auth-overlay">
    <div class="auth-modal">
        <h1>Username festlegen</h1>

        <?php if ($usernameError): ?>
            <div class="auth-error"><?= htmlspecialchars($usernameError) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="action" value="set_username">
            <input type="text" name="username" placeholder="Neuer Username" required>
            <button class="button">Speichern</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ================= ACHIEVEMENT TOAST ================= -->

<?php if (!empty($_SESSION['toast'])): ?>
<div class="achievement-toast" onclick="openAchievementOverlay()">
    🏆 Achievement freigeschaltet
</div>
<?php unset($_SESSION['toast']); endif; ?>

<!-- ================= ACHIEVEMENT OVERLAY ================= -->

<div id="achievementOverlay" class="auth-overlay hidden">
    <div class="auth-modal">
        <h2>Deine Achievements</h2>

        <ul class="achievement-list">
            <?php foreach ($achievements as $a): ?>
                <li>
                    <strong><?= htmlspecialchars($a['name']) ?></strong><br>
                    <small><?= htmlspecialchars($a['description']) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>

        <button class="button" onclick="closeAchievementOverlay()">Schließen</button>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
