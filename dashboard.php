<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="panel dashboard-panel">
    <h1>Dashboard</h1>
    <p>Willkommen, <?= htmlspecialchars($auth->getUserEmail()) ?></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
