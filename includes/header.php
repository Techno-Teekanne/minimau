<?php
// Erwartet: $auth (aus init.php), optional $pageTitle
$currentPage = basename($_SERVER['PHP_SELF']);
$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? 'MAU OS') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/mau-ui.css">
</head>
<body>

<div class="app-shell">

    <!-- TOPBAR -->
    <header class="topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button id="sidebarToggle" class="mau-btn is-ghost" aria-label="Menü">☰</button>
            <span class="brand"><?= htmlspecialchars($pageTitle ?? 'MAU OS') ?></span>
        </div>

        <div class="topbar-right">
            <?php if ($auth->isLoggedIn()): ?>
                <span class="badge is-ok">online</span>

                <!-- USER MENU -->
                <div class="user-menu">
                    <button id="userMenuToggle"
                            class="mau-btn is-ghost user-name"
                            type="button">
                        <?= htmlspecialchars($username) ?>
                    </button>

                    <div id="userMenuDropdown" class="user-dropdown">
                        <a href="/profile.php">Profil</a>
                        <a href="/profile-settings.php">Settings</a>
                        <hr>
                        <a href="/logout.php">Logout</a>
                    </div>
                </div>

            <?php else: ?>
                <a href="/login.php" class="mau-btn is-primary">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="mau-rainbow-line"></div>

    <div class="app-body">

<aside id="sidebar" class="sidebar">
    <nav class="sidebar-menu">

        <div class="menu-section">System</div>
        <a href="/index.php"
           class="menu-item <?= $currentPage === 'index.php' ? 'is-active' : '' ?>">
            Start
        </a>
        <a href="/dashboard.php"
           class="menu-item <?= $currentPage === 'dashboard.php' ? 'is-active' : '' ?>">
            Dashboard
        </a>

        <div class="menu-section">Games</div>
        <a href="#" class="menu-item">TF2</a>
        <a href="#" class="menu-item">Minecraft</a>

        <div class="menu-section">MAU</div>
        <a href="#" class="menu-item">Module</a>
        <a href="#" class="menu-item">Logs</a>

    </nav>
</aside>

<div class="app-content">
