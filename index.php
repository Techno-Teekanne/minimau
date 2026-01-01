<?php
require_once __DIR__ . '/includes/init.php';

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
