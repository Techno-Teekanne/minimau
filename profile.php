<?php
require __DIR__ . '/includes/init.php';
require __DIR__ . '/includes/header.php';

/* --------------------------------------------------
   Ziel-User bestimmen
-------------------------------------------------- */
$userId = $_GET['user'] ?? $_SESSION['user_id'] ?? null;
if (!$userId) {
    die('Kein User angegeben');
}

/* --------------------------------------------------
   Profil-Boxen laden
-------------------------------------------------- */
$stmt = $pdo->prepare("
    SELECT *
    FROM profile_boxes
    WHERE user_id = :uid
    ORDER BY sort_order ASC, id ASC
");
$stmt->execute(['uid' => $userId]);
$boxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- PROFIL-INHALT -->
<div class="panel-grid">

<?php foreach ($boxes as $box): ?>

    <div class="panel">

        <div class="panel-header">
            <h2><?= htmlspecialchars($box['title']) ?></h2>
        </div>

        <div class="panel-body">

            <?php
            /* ------------------------------------------
               Felder für diese Box laden
            ------------------------------------------ */
            $stmtFields = $pdo->prepare("
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
                        <?php
                        if ($f['field_type'] === 'list') {
                            foreach (explode("\n", $f['value']) as $line) {
                                echo htmlspecialchars($line) . '<br>';
                            }
                        } else {
                            echo nl2br(htmlspecialchars($f['value']));
                        }
                        ?>
                    </div>

                </div>
            <?php endforeach; ?>

        </div>
    </div>

<?php endforeach; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
