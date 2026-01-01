<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Passwort vergessen · MAU</title>
    <link rel="stylesheet" href="/css/mau-ui.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="panel">
        <h1>Passwort zurücksetzen</h1>
        <div class="subtitle">Wir schicken dir einen Reset-Link</div>

        <form method="post" action="/forgot.php">
            <div class="form-group">
                <label>E-Mail</label>
                <input type="email" name="email" required>
            </div>

            <button class="button" type="submit">Link senden</button>
        </form>

        <div class="meta-links">
            <a href="/login.php">Zurück</a>
        </div>
    </div>
</div>

</body>
</html>
