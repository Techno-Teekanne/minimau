<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($auth->login($_POST['email'], $_POST['password'])) {
        header('Location: /dashboard.php');
        exit;
    }
    $error = 'Login fehlgeschlagen';
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="panel">
        <h1>Login</h1>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="email" name="email" placeholder="E-Mail" required>
            <input type="password" name="password" placeholder="Passwort" required>
            <button class="button">Login</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
