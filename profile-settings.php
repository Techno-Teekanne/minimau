<?php
require __DIR__ . '/includes/init.php';
require __DIR__ . '/includes/header.php';

$userId = $auth->getUserId();
if (!$userId) {
    header('Location: /index.php');
    exit;
}

$templatePath = __DIR__ . '/vorlagen/steckbrief-vorlage.md';
if (!file_exists($templatePath)) {
    die('Vorlage fehlt: /vorlagen/steckbrief-vorlage.md');
}

$templateLines = file($templatePath, FILE_IGNORE_NEW_LINES);
$template = [];
$currentBox = null;
$fieldIndex = 0;

foreach ($templateLines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '# ')) {
        continue;
    }
    if (str_starts_with($line, '## ')) {
        $title = trim(substr($line, 3));
        $currentBox = [
            'title' => $title,
            'key' => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title)),
            'fields' => []
        ];
        $template[] = $currentBox;
        $fieldIndex = 0;
        continue;
    }
    if (str_starts_with($line, '- ') && $currentBox !== null) {
        $fieldTitle = trim(substr($line, 2));
        $template[count($template) - 1]['fields'][] = [
            'title' => $fieldTitle,
            'sort' => $fieldIndex++
        ];
    }
}

foreach ($template as $index => $boxData) {
    $stmt = $db->prepare("
        SELECT id
        FROM profile_boxes
        WHERE user_id = :uid AND box_key = :box_key
        LIMIT 1
    ");
    $stmt->execute([
        'uid' => $userId,
        'box_key' => $boxData['key']
    ]);
    $boxId = $stmt->fetchColumn();

    if (!$boxId) {
        $stmt = $db->prepare("
            INSERT INTO profile_boxes (user_id, box_key, title, sort_order)
            VALUES (:uid, :box_key, :title, :sort)
        ");
        $stmt->execute([
            'uid' => $userId,
            'box_key' => $boxData['key'],
            'title' => $boxData['title'],
            'sort' => $index
        ]);
        $boxId = $db->lastInsertId();
    }

    $template[$index]['box_id'] = (int)$boxId;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['profile_image']['tmp_name'])) {
        $uploadDir = __DIR__ . '/uploads/profile-images/' . $userId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $originalName = basename($_FILES['profile_image']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $safeName = bin2hex(random_bytes(8)) . ($extension ? '.' . $extension : '');
        $targetPath = $uploadDir . '/' . $safeName;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
            $relativePath = '/uploads/profile-images/' . $userId . '/' . $safeName;
            $db->prepare("
                INSERT INTO profile_images (user_id, file_path, uploaded_at)
                VALUES (:uid, :path, NOW())
            ")->execute([
                'uid' => $userId,
                'path' => $relativePath
            ]);
        }
    }

    foreach ($template as $boxData) {
        foreach ($boxData['fields'] as $field) {
            $inputKey = 'field_' . md5($boxData['key'] . '_' . $field['title']);
            $value = trim($_POST[$inputKey] ?? '');

            $stmt = $db->prepare("
                SELECT id
                FROM profile_fields
                WHERE box_id = :box AND title = :title
                LIMIT 1
            ");
            $stmt->execute([
                'box' => $boxData['box_id'],
                'title' => $field['title']
            ]);
            $fieldId = $stmt->fetchColumn();

            if ($fieldId) {
                $db->prepare("
                    UPDATE profile_fields
                    SET value = :value, sort_order = :sort
                    WHERE id = :id
                ")->execute([
                    'value' => $value,
                    'sort' => $field['sort'],
                    'id' => $fieldId
                ]);
            } else {
                $db->prepare("
                    INSERT INTO profile_fields (box_id, title, field_type, value, sort_order)
                    VALUES (:box, :title, 'text', :value, :sort)
                ")->execute([
                    'box' => $boxData['box_id'],
                    'title' => $field['title'],
                    'value' => $value,
                    'sort' => $field['sort']
                ]);
            }
        }
    }

    header('Location: /profile.php');
    exit;
}

$values = [];
foreach ($template as $boxData) {
    $stmt = $db->prepare("
        SELECT title, value
        FROM profile_fields
        WHERE box_id = :box
    ");
    $stmt->execute(['box' => $boxData['box_id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $values[$boxData['key']][$row['title']] = $row['value'];
    }
}
?>

<!-- NUR FORMULAR. KEINE ANZEIGE. -->

<link rel="stylesheet" href="/rpg/rpg.css">

<div class="rpg-shell">
    <div class="rpg-topbar">
        <a class="rpg-back-btn" href="/profile.php">←</a>
        <div class="rpg-title">Steckbrief bearbeiten</div>
        <div></div>
    </div>

    <div class="rpg-layout">
        <aside class="rpg-nav">
            <div class="rpg-nav-scroll">
                <div class="rpg-chapter-title">Vorlage</div>
                <?php foreach ($template as $boxData): ?>
                    <a class="rpg-scene" href="#edit-<?= htmlspecialchars($boxData['key']) ?>">
                        <?= htmlspecialchars($boxData['title']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>

        <main class="rpg-stage">
            <form method="post" enctype="multipart/form-data" class="mau-stack">
                <div class="panel" id="edit-profile-image">
                    <div class="panel-header">
                        <strong>Profilbild</strong>
                    </div>
                    <div class="panel-body">
                        <label class="mau-label">Bild hochladen</label>
                        <input class="mau-input" type="file" name="profile_image" accept="image/*">
                    </div>
                </div>

                <?php foreach ($template as $boxData): ?>
                    <div class="panel" id="edit-<?= htmlspecialchars($boxData['key']) ?>">
                        <div class="panel-header">
                            <strong><?= htmlspecialchars($boxData['title']) ?></strong>
                        </div>
                        <div class="panel-body">
                            <?php foreach ($boxData['fields'] as $field): ?>
                                <?php
                                $inputKey = 'field_' . md5($boxData['key'] . '_' . $field['title']);
                                $value = $values[$boxData['key']][$field['title']] ?? '';
                                ?>
                                <label class="mau-label"><?= htmlspecialchars($field['title']) ?></label>
                                <textarea
                                    class="mau-input mau-textarea"
                                    name="<?= htmlspecialchars($inputKey) ?>"
                                    rows="3"
                                ><?= htmlspecialchars($value) ?></textarea>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <button class="mau-btn is-primary">Speichern</button>
            </form>
        </main>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
