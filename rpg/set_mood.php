<?php
require_once __DIR__ . '/../includes/init.php';

$scene = (int)($_POST['scene'] ?? 0);
$mood  = $_POST['mood'] ?? '';

$db->prepare("
    UPDATE rpg_scenes
    SET mood = :m, mood_updated_at = NOW()
    WHERE scene_id = :id
")->execute([
    'm'  => $mood,
    'id' => $scene
]);

echo json_encode(['ok' => true]);
