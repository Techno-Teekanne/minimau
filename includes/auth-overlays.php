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
<input type="text" name="register_code" placeholder="Register-Code" required>
<?php endif; ?>
<button class="button">OK</button>
</form>
<div class="auth-switch">
<?php if ($authMode === 'login'): ?>
    <form method="post">
        <input type="hidden" name="mode" value="register">
        <button class="link-button">Account erstellen</button>
    </form>
<?php else: ?>
    <form method="post">
        <input type="hidden" name="mode" value="login">
        <button class="link-button">Zurück zum Login</button>
    </form>
<?php endif; ?>
</div>
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
