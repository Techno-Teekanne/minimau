</div> <!-- /app-content -->
</div> <!-- /app-body -->

<footer style="
    padding: 10px 16px;
    border-top: 1px solid var(--mau-border);
    background: rgba(10,10,10,0.9);
    font-size: 0.75rem;
    color: var(--mau-text-muted);
    text-align: center;
">
    MAU OS · handcrafted · internal
</footer>

</div> <!-- /app-shell -->

<!-- =========================
     AUTH / SESSION OVERLAYS
========================= -->
<?php require_once __DIR__ . '/auth-overlays.php'; ?>

<!-- =========================
     SCRIPTS
========================= -->

<script>
/* Sidebar Toggle */
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar')?.classList.toggle('is-open');
});
</script>

<script>
/* User Menu (Topbar) */
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('userMenuToggle');
    const menu   = document.getElementById('userMenuDropdown');

    if (!toggle || !menu) return;

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('open');
    });

    document.addEventListener('click', () => {
        menu.classList.remove('open');
    });
});
</script>

<script>
/* Achievement Overlay */
function openAchievementOverlay(){
    document.getElementById('achievementOverlay')?.classList.remove('hidden');
}
function closeAchievementOverlay(){
    document.getElementById('achievementOverlay')?.classList.add('hidden');
}
</script>

</body>
</html>
