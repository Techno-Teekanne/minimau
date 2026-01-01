<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Registrieren · MAU</title>
    <link rel="stylesheet" href="/css/mau-ui.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="panel">
        <h1>Account erstellen</h1>
        <div class="subtitle">Neuer MAU-Zugang</div>

        <form method="post" action="/register.php">
            <div class="form-group">
                <label>E-Mail</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Passwort</label>
                <input type="password" name="password" required>
            </div>

            <button class="button" type="submit">Registrieren</button>
        </form>

        <div class="meta-links">
            <a href="/login.php">Zurück zum Login</a>
        </div>
    </div>
</div>

</body>
</html>
