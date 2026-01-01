<?php
require __DIR__ . '/includes/init.php';
require __DIR__ . '/includes/header.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    die('Nicht eingeloggt');
}

/* ---------------------------------------
   About-Box holen oder anlegen
--------------------------------------- */
$stmt = $pdo->prepare("
    SELECT id
    FROM profile_boxes
    WHERE user_id = :uid AND box_key = 'about'
    LIMIT 1
");
$stmt->execute(['uid' => $userId]);
$boxId = $stmt->fetchColumn();

if (!$boxId) {
    $stmt = $pdo->prepare("
        INSERT INTO profile_boxes (user_id, box_key, title, sort_order)
        VALUES (:uid, 'about', 'About', 0)
    ");
    $stmt->execute(['uid' => $userId]);
    $boxId = $pdo->lastInsertId();
}

/* ---------------------------------------
   Formular absenden → Feld speichern
--------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $value = trim($_POST['value'] ?? '');

    if ($title !== '' && $value !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO profile_fields
                (box_id, title, field_type, value, sort_order)
            VALUES
                (:box, :title, 'text', :value, 0)
        ");
        $stmt->execute([
            'box'   => $boxId,
            'title' => $title,
            'value' => $value
        ]);
    }

    // Nach dem Speichern zurück ins Profil
    header('Location: /profile.php');
    exit;
}
?>

<!-- NUR FORMULAR. KEINE ANZEIGE. -->

<div class="panel">
    <div class="panel-header">
        <h2>Steckbrief bearbeiten</h2>
        <p class="panel-subtitle">
            Titel frei wählen, Inhalt frei schreiben
        </p>
    </div>

    <div class="panel-body">

        <form method="post" class="mau-stack">

            <label class="mau-label">Titel</label>
            <input
                class="mau-input"
                name="title"
                placeholder="z. B. Name, Pronouns, Herkunft, irgendwas"
                required
            >

            <label class="mau-label">Inhalt</label>
            <textarea
                class="mau-input mau-textarea"
                name="value"
                rows="4"
                placeholder="Was soll im Profil stehen?"
                required
            ></textarea>

            <button class="mau-btn is-primary">
                Speichern
            </button>

        </form>

    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
