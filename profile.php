<?php
require __DIR__ . '/includes/init.php';
require __DIR__ . '/includes/header.php';

/* --------------------------------------------------
   Ziel-User bestimmen
-------------------------------------------------- */
$userId = $auth->getUserId();
if (!$userId) {
    header('Location: /index.php');
    exit;
}

$profileImageStmt = $db->prepare(
    "SELECT file_path
     FROM profile_images
     WHERE user_id = :uid
     ORDER BY uploaded_at DESC
     LIMIT 1"
);
$profileImageStmt->execute(['uid' => $userId]);
$profileImage = $profileImageStmt->fetchColumn();

/* --------------------------------------------------
   Profil-Boxen laden
-------------------------------------------------- */
$stmt = $db->prepare("
    SELECT *
    FROM profile_boxes
    WHERE user_id = :uid
    ORDER BY sort_order ASC, id ASC
");
$stmt->execute(['uid' => $userId]);
$boxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="/rpg/rpg.css">

<div class="rpg-shell">
    <div class="rpg-topbar">
        <div></div>
        <div class="rpg-title">Profil</div>
        <div></div>
    </div>

    <div class="rpg-layout">
        <aside class="rpg-nav">
            <div class="rpg-nav-scroll">
                <div class="rpg-chapter-title">Bereiche</div>
                <?php foreach ($boxes as $box): ?>
                    <a class="rpg-scene" href="#<?= htmlspecialchars($box['box_key']) ?>">
                        <?= htmlspecialchars($box['title']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="rpg-scene-state">
                <a class="state-toggle is-active" href="/profile-settings.php">Steckbrief bearbeiten</a>
            </div>
        </aside>

        <main class="rpg-stage">
            <div class="panel">
                <div class="panel-header">
                    <strong>Profilbild</strong>
                </div>
                <div class="panel-body">
                    <?php if ($profileImage): ?>
                        <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profilbild" style="max-width:180px;border-radius:12px;">
                    <?php else: ?>
                        <div class="mau-dim">Kein Profilbild hinterlegt.</div>
                    <?php endif; ?>
                </div>
            </div>

            <?php foreach ($boxes as $box): ?>
                <div class="panel" id="<?= htmlspecialchars($box['box_key']) ?>">
                    <div class="panel-header">
                        <strong><?= htmlspecialchars($box['title']) ?></strong>
                    </div>
                    <div class="panel-body">
                        <?php
                        $stmtFields = $db->prepare("
                            SELECT *
                            FROM profile_fields
                            WHERE box_id = :box
                            ORDER BY sort_order ASC, id ASC
                        ");
                        $stmtFields->execute(['box' => $box['id']]);
                        $fields = $stmtFields->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <?php foreach ($fields as $f): ?>
                            <div class="profile-field" style="margin-bottom:10px;">
                                <?php if (trim($f['title']) !== ''): ?>
                                    <div class="mau-dim" style="font-size:0.75rem;">
                                        <?= htmlspecialchars($f['title']) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="profile-value">
                                    <?= nl2br(htmlspecialchars($f['value'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </main>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
