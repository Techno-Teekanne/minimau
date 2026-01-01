<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="panel-grid">
    <div class="panel mau-glow">
        <div class="panel-header"><strong>Dashboard</strong></div>
        <div class="panel-body">
            <p class="panel-subtitle">Willkommen zurück, <?= htmlspecialchars($auth->getUsername() ?? '') ?></p>
            <p class="mau-dim">Hier findest du die wichtigsten Einstiege für dein Profil und deine Projekte.</p>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header"><strong>User-Verwaltung</strong></div>
        <div class="panel-body">
            <div class="mau-stack">
                <a class="mau-btn is-primary" href="/profile.php">Profil ansehen</a>
                <a class="mau-btn" href="/profile-settings.php">Steckbrief bearbeiten</a>
                <a class="mau-btn" href="/profile-settings.php#edit-profile-image">Profilbild aktualisieren</a>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header"><strong>Einstieg</strong></div>
        <div class="panel-body">
            <div class="mau-stack">
                <a class="mau-btn" href="/profile-settings.php">Settings</a>
                <a class="mau-btn" href="/rpg/scene.php">RPG</a>
                <a class="mau-btn" href="/index.php">Start</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
