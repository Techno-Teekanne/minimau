<?php
$pageTitle = 'RPG · Szenenübersicht';
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/header.php';

$rpg_id = (int)($_GET['rpg_id'] ?? 0);
if ($rpg_id <= 0) {
    echo '<div class="panel">Ungültige RPG-ID</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
function stripBrTags(string $text): string
{
    // Entfernt alle <br>, <br/> und <br /> Varianten
    return preg_replace('~<br\s*/?>~i', '', $text);
}

/* ===============================
   Marker laden (Titel ebenfalls korrekt lesen)
================================ */
$markerStmt = $db->prepare("
    SELECT
        marker_id,
        CONVERT(BINARY CONVERT(titel USING latin1) USING utf8mb4) AS titel,
        post_id
    FROM rpg_kap_marker
    WHERE rpg_id = :rpg
    ORDER BY post_id ASC
");
$markerStmt->execute(['rpg' => $rpg_id]);
$markers = $markerStmt->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   Postings laden (korrekt interpretiert!)
================================ */
$postStmt = $db->prepare("
    SELECT
        post_id,
        CONVERT(BINARY CONVERT(autor USING latin1) USING utf8mb4) AS autor,
        CONVERT(BINARY CONVERT(posting_text USING latin1) USING utf8mb4) AS posting_text,
        posting_date
    FROM rpg_basic_posting
    WHERE rpg_id = :rpg
    ORDER BY post_id ASC
");
$postStmt->execute(['rpg' => $rpg_id]);
$posts = $postStmt->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   Szenen aus Markern bauen
================================ */
$scenes = [];
$markerCount = count($markers);

foreach ($markers as $i => $m) {
    $start = (int)$m['post_id'];
    $end   = ($i + 1 < $markerCount)
        ? (int)$markers[$i + 1]['post_id'] - 1
        : PHP_INT_MAX;

    $scenes[] = [
        'marker_id' => (int)$m['marker_id'],
        'titel'     => $m['titel'] ?: 'Ohne Titel',
        'start'     => $start,
        'end'       => $end,
        'posts'     => []
    ];
}

/* Sonderfall: Posts vor dem ersten Marker */
$preScene = [
    'marker_id' => 0,
    'titel'     => 'Vor dem ersten Marker',
    'start'     => 0,
    'end'       => $markerCount ? (int)$markers[0]['post_id'] - 1 : PHP_INT_MAX,
    'posts'     => []
];

/* ===============================
   Postings linear zuordnen
================================ */
if (!empty($scenes)) {
    $sceneIndex = 0;
    $sceneMax   = count($scenes) - 1;

    foreach ($posts as $p) {
        $pid = (int)$p['post_id'];

        while ($sceneIndex < $sceneMax && $pid > $scenes[$sceneIndex]['end']) {
            $sceneIndex++;
        }

        if ($pid >= $scenes[$sceneIndex]['start'] && $pid <= $scenes[$sceneIndex]['end']) {
            $scenes[$sceneIndex]['posts'][] = $p;
        } elseif ($pid <= $preScene['end']) {
            $preScene['posts'][] = $p;
        }
    }
} else {
    $preScene['posts'] = $posts;
}

if (!empty($preScene['posts'])) {
    array_unshift($scenes, $preScene);
}
?>
<style>
.rpg-tabs {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}
.rpg-tab {
    background: #222;
    border: 1px solid #333;
    color: #ddd;
    padding: 6px 10px;
    cursor: pointer;
}
.rpg-tab.active {
    background: #444;
}
.rpg-scene {
    display: none;
}
.rpg-scene.active {
    display: block;
}
.rpg-post {
    border-bottom: 1px solid #222;
    padding: 8px 0;
}
.rpg-post-head {
    font-size: 0.8rem;
    opacity: 0.8;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.rpg-post-body {
    margin-top: 4px;
}
.rpg-scene-meta {
    font-size: 0.75rem;
    opacity: 0.6;
    margin-bottom: 6px;
}
.muted {
    opacity: 0.5;
}
</style>
<div class="panel mau-glow">
    <div class="panel-header">
        <strong>Szenenübersicht (RPG <?= (int)$rpg_id ?>)</strong>
    </div>

    <div class="rpg-tabs">
        <?php foreach ($scenes as $i => $s): ?>
            <button class="rpg-tab<?= $i === 0 ? ' active' : '' ?>"
                    data-tab="scene<?= $i ?>">
                <?= htmlspecialchars($s['titel']) ?>
                <small>(<?= count($s['posts']) ?>)</small>
            </button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($scenes as $i => $s): ?>
        <div class="rpg-scene<?= $i === 0 ? ' active' : '' ?>"
             id="scene<?= $i ?>">

            <div class="rpg-scene-meta">
                <strong>Marker-ID:</strong> <?= (int)$s['marker_id'] ?>
                · <strong>Post-ID:</strong>
                <?= (int)$s['start'] ?> – <?= $s['end'] === PHP_INT_MAX ? '∞' : (int)$s['end'] ?>
            </div>

            <?php foreach ($s['posts'] as $p): ?>
                <div class="rpg-post">
                    <div class="rpg-post-head">
                        <strong><?= htmlspecialchars($p['autor']) ?></strong>
                        <span>#<?= (int)$p['post_id'] ?></span>
                        <small><?= htmlspecialchars($p['posting_date']) ?></small>
                    </div>
                    <div class="rpg-post-body">
                        <?= nl2br(htmlspecialchars(stripBrTags($p['posting_text']))) ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($s['posts'])): ?>
                <div class="rpg-post muted">Keine Postings in dieser Szene.</div>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>
</div>

<script>
document.querySelectorAll('.rpg-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.rpg-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.rpg-scene').forEach(s => s.classList.remove('active'));

        tab.classList.add('active');
        const target = document.getElementById(tab.dataset.tab);
        if (target) target.classList.add('active');
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
