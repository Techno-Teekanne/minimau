<?php
require_once __DIR__ . '/includes/init.php';

$token = $_GET['token'] ?? '';
$verified = $auth->verifyEmailToken($token);
$pageTitle = 'E-Mail bestätigen';
require_once __DIR__ . '/includes/header.php';
?>

<div class="panel">
    <div class="panel-header"><strong>E-Mail bestätigen</strong></div>
    <div class="panel-body">
        <?php if ($verified): ?>
            <p>Danke! Deine E-Mail wurde erfolgreich verifiziert.</p>
        <?php else: ?>
            <p>Der Verifizierungslink ist ungültig oder abgelaufen.</p>
        <?php endif; ?>
        <a class="mau-btn is-primary" href="/dashboard.php">Zum Dashboard</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
