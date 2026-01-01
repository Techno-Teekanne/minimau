<?php if ($showAuth): ?>
<div class="auth-overlay">
<div class="auth-modal">
<h1><?= $authMode === 'login' ? 'Login' : 'Registrieren' ?></h1>
<?php if ($authError): ?><div class="auth-error"><?= $authError ?></div><?php endif; ?>
<form method="post">
<input type="hidden" name="action" value="<?= $authMode === 'login' ? 'login' : 'register' ?>">
<input type="email" name="email" required>
<input type="password" name="password" required>
<?php if ($authMode === 'register'): ?>
<input type="password" name="password_confirm" required>
<?php endif; ?>
<button class="button">OK</button>
</form>
</div>
</div>
<?php endif; ?>

<?php if ($forceUsername): ?>
<div class="auth-overlay">
<div class="auth-modal">
<h1>Username festlegen</h1>
<?php if ($usernameError): ?><div class="auth-error"><?= $usernameError ?></div><?php endif; ?>
<form method="post">
<input type="hidden" name="action" value="set_username">
<input type="text" name="username" required>
<button class="button">Speichern</button>
</form>
</div>
</div>
<?php endif; ?>

<?php if ($sessionExpiringSoon): ?>
<div class="auth-overlay">
<div class="auth-modal">
<h2>Session läuft ab</h2>
<form method="post">
<input type="hidden" name="action" value="extend_session">
<button class="button">7 Tage verlängern</button>
</form>
<form method="post">
<input type="hidden" name="action" value="logout">
<button class="link-button">Logout</button>
</form>
</div>
</div>
<?php endif; ?>
