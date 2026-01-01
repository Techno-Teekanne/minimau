<?php
require_once __DIR__ . '/../includes/init.php';

$scene = (int)($_GET['scene'] ?? 0);

$q = $db->prepare("
    SELECT mood, mood_updated_at
    FROM rpg_scenes
    WHERE scene_id = :id
    LIMIT 1
");
$q->execute(['id' => $scene]);

echo json_encode($q->fetch(PDO::FETCH_ASSOC));
