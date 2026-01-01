<?php
require_once __DIR__ . '/../includes/init.php';
$pageTitle = 'RPG Titel';
require_once __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="rpg.css">
<!-- ───────────────── RPG SCENE SHELL ───────────────── -->
<div class="rpg-shell">

    <!-- ───── RPG Header (innerhalb Content) ───── -->
    <div class="rpg-topbar">
        <button class="rpg-back-btn">←</button>
        <div class="rpg-title">RPG Titel</div>
        <div class="rpg-clock">00:45</div>
    </div>

    <div class="rpg-layout">

        <!-- ───── LEFT: Kapitel / Szenen ───── -->
        <aside class="rpg-nav">
            <div class="rpg-nav-scroll">

                <div class="rpg-chapter">
                    <div class="rpg-chapter-title">Kapitel 1</div>
                    <div class="rpg-scene is-active">Szene</div>
                    <div class="rpg-scene">Szene</div>
                    <div class="rpg-scene">Szene</div>
                </div>

                <div class="rpg-chapter">
                    <div class="rpg-chapter-title">Kapitel 2</div>
                    <div class="rpg-scene">Szene</div>
                    <div class="rpg-scene">Szene</div>
                </div>

            </div>

            <!-- ───── Scene State Toggles ───── -->
            <div class="rpg-scene-state">
                <button class="state-toggle is-active">Regen</button>
                <button class="state-toggle is-active">Nebel</button>
                <button class="state-toggle">Sonnenschein</button>

                <hr>

                <button class="state-toggle is-active">Nacht</button>
                <button class="state-toggle">Tag</button>
                <button class="state-toggle">Dämmerung</button>
                <button class="state-toggle">Gewitter</button>
            </div>
        </aside>

        <!-- ───── CENTER: Bühne / Posts ───── -->
        <main class="rpg-stage">

            <div class="rpg-post left">
                <div class="rpg-post-avatar">
                    <img src="/assets/placeholder/johann.png" alt="">
                    <div class="rpg-post-name">Johann</div>
                </div>
                <div class="rpg-post-content">
                    Dies ist ein Testtext. Dies ist ein Testtext.  
                    Dies ist ein Testtext. Dies ist ein Testtext.
                </div>
            </div>

            <div class="rpg-post right">
                <div class="rpg-post-content">
                    Dies ist ein Testtext. Dies ist ein Testtext.  
                    Dies ist ein Testtext. Dies ist ein Testtext.
                </div>
                <div class="rpg-post-avatar">
                    <img src="/assets/placeholder/mickjell.png" alt="">
                    <div class="rpg-post-name">Mickjell</div>
                </div>
            </div>

        </main>

    </div>

    <!-- ───── RPG Bottom Bar ───── -->
    <div class="rpg-bottombar">
        <button class="rpg-action-btn">Chat</button>
        <button class="rpg-action-btn is-primary">Post reply</button>
        <button class="rpg-action-btn">FF</button>
        <button class="rpg-action-btn">Notepad</button>
    </div>

</div>
<!-- ───────────────── POST REPLY OVERLAY ───────────────── -->
<div id="postReplyOverlay" class="post-reply-overlay hidden">

    <!-- Backdrop -->
    <div class="post-reply-backdrop"></div>

    <!-- Window -->
    <div class="post-reply-window">

        <!-- ───── HEADER ───── -->
        <header class="post-reply-header">

            <div class="post-reply-user">
                <span class="post-reply-avatar">
                    <img src="/assets/placeholder/johann.png" alt="">
                </span>
                <span class="post-reply-charname">[CHARANAME]</span>
            </div>

            <button class="post-reply-close" aria-label="Close">✕</button>

        </header>

        <!-- ───── BODY ───── -->
        <div class="post-reply-body">

            <!-- EDITOR -->
            <section class="post-editor">

                <textarea
                    class="post-editor-text"
                    placeholder="Something dramatic..."></textarea>

                <div class="post-editor-toolbar">
                    <button type="button">B</button>
                    <button type="button">I</button>
                    <button type="button">U</button>
                    <button type="button">⋯</button>
                </div>

            </section>

            <!-- SIDE PANEL -->
            <aside class="post-editor-side">

                <!-- META -->
                <section class="side-section">
                    <h4 class="side-title">Scene Meta</h4>

                    <div class="post-meta-row">
                        <span class="meta-label">Location</span>
                        <button class="meta-btn">[ set ]</button>
                    </div>

                    <div class="post-meta-row">
                        <span class="meta-label">Time</span>
                        <button class="meta-btn">[ set ]</button>
                    </div>
                </section>

                <!-- TAGS -->
                <section class="side-section">
                    <h4 class="side-title">Mood Tags</h4>

                    <div class="post-tags">
                        <span class="tag-pill">Rainy</span>
                        <span class="tag-pill">Ominous</span>
                    </div>
                </section>

                <!-- ACTIONS -->
                <section class="side-section">
                    <h4 class="side-title">Actions</h4>

                    <div class="mau-action-panel">
                        <button class="action-item" data-action="character">
                            <span class="icon">◯</span>
                            <span class="label">Charakter ändern</span>
                        </button>

                        <button class="action-item" data-action="mood">
                            <span class="icon">◑</span>
                            <span class="label">Mood ändern</span>
                        </button>

                        <button class="action-item" data-action="saved">
                            <span class="icon">≡</span>
                            <span class="label">Gespeicherte Posts</span>
                        </button>

                        <button class="action-item" data-action="energy">
                            <span class="icon">⚡</span>
                            <span class="label">Realtime Energy</span>
                        </button>
                    </div>
                </section>

            </aside>

        </div>

        <!-- ───── FOOTER ───── -->
        <footer class="post-reply-footer">
            <button class="btn-cancel">Cancel</button>
            <button class="btn-save">Save</button>
            <button class="btn-post">Post</button>
        </footer>

    </div>

</div>
<!-- ───────────────── CHARACTER SELECT OVERLAY ───────────────── -->
<div id="characterSelectOverlay" class="character-overlay hidden">

    <div class="character-backdrop"></div>

    <div class="character-window">

        <!-- Header -->
        <header class="character-header">
            <div>
                <h2>Select Character</h2>
                <p>Choose a character to post as.</p>
            </div>
            <button class="character-close">✕</button>
        </header>

        <!-- Search -->
        <div class="character-search">
            <input type="text" placeholder="Search">
        </div>

        <!-- Grid -->
        <div class="character-grid">

            <!-- Character Card -->
            <button class="character-card" data-char="jeremy">
                <img src="/assets/placeholder/jeremy.png" alt="">
                <span>Jeremy</span>
            </button>

            <button class="character-card" data-char="jane">
                <img src="/assets/placeholder/jane.png" alt="">
                <span>Jane</span>
            </button>

            <button class="character-card" data-char="denise">
                <img src="/assets/placeholder/denise.png" alt="">
                <span>Denise</span>
            </button>

            <button class="character-card" data-char="johann">
                <img src="/assets/placeholder/johann.png" alt="">
                <span>Johann</span>
            </button>

            <button class="character-card" data-char="louis">
                <img src="/assets/placeholder/louis.png" alt="">
                <span>Louis</span>
            </button>

            <button class="character-card is-selected" data-char="mickjell">
                <img src="/assets/placeholder/mickjell.png" alt="">
                <span>Mickjell</span>
            </button>

            <button class="character-card" data-char="misha">
                <img src="/assets/placeholder/misha.png" alt="">
                <span>Misha</span>
            </button>

        </div>

    </div>
</div>
<!-- ───────────────── MOOD SELECT OVERLAY ───────────────── -->
<div id="moodSelectOverlay" class="overlay hidden">

    <div class="overlay-backdrop"></div>

    <div class="overlay-window mood-select">

        <!-- Header -->
        <header class="overlay-header">
            <h1>SELECT MOOD</h1>
            <p>Wähle ein passendes Bild oder füge ein neues an</p>
            <button class="overlay-close">✕</button>
        </header>

        <!-- Upload -->
        <section class="overlay-upload">
            <label class="upload-label">Wähle:</label>
            <input type="file" id="moodUpload" hidden>
            <button class="btn-upload"
                    onclick="document.getElementById('moodUpload').click()">
                Durchsuchen
            </button>
            <button class="btn-upload-secondary">UPLOAD</button>
        </section>

        <!-- Grid -->
        <section class="overlay-grid mood-grid">
            <!-- Mood Cards -->
            <button class="mood-card is-selected">
                <img src="/assets/mood/mickjell_01.png">
            </button>

            <button class="mood-card">
                <img src="/assets/mood/mickjell_02.png">
            </button>

            <button class="mood-card">
                <img src="/assets/mood/mickjell_03.png">
            </button>

            <button class="mood-card">
                <img src="/assets/mood/mickjell_04.png">
            </button>

            <button class="mood-card">
                <img src="/assets/mood/mickjell_05.png">
            </button>

            <button class="mood-card">
                <img src="/assets/mood/mickjell_06.png">
            </button>
        </section>

        <!-- Footer -->
        <footer class="overlay-footer">
            <button class="btn-cancel">Cancel</button>
        </footer>

    </div>
</div>
<!-- ───────────────── SAVED POSTS OVERLAY ───────────────── -->
<div id="savedPostsOverlay" class="overlay hidden">

    <div class="overlay-backdrop"></div>

    <div class="overlay-window saved-posts">

        <!-- Header -->
        <header class="overlay-header">
            <h1>GESPEICHERTE POSTS</h1>
            <p>Wähle einen Entwurf aus und schreibe ihn weiter</p>
            <button class="overlay-close">✕</button>
        </header>

        <!-- List -->
        <section class="saved-posts-list">

            <!-- Saved Post -->
            <article class="saved-post-card">
                <div class="saved-post-meta">
                    <span class="saved-post-char">Johann</span>
                    <span class="saved-post-date">2025-01-12 · Szene 3</span>
                </div>

                <div class="saved-post-preview">
                    Something dramatic… pulls his coat tighter as the rain begins…
                </div>

                <div class="saved-post-actions">
                    <button class="btn-secondary">Vorschau</button>
                    <button class="btn-primary continue-post"
                            data-content="Something dramatic… pulls his coat tighter as the rain begins…">
                        Weiter schreiben
                    </button>
                </div>
            </article>

            <!-- Wiederholbar -->
            <article class="saved-post-card">
                <div class="saved-post-meta">
                    <span class="saved-post-char">Mickjell</span>
                    <span class="saved-post-date">2025-01-10 · Kapitel 1</span>
                </div>

                <div class="saved-post-preview">
                    He remains silent. The fog swallows the shoreline.
                </div>

                <div class="saved-post-actions">
                    <button class="btn-secondary">Vorschau</button>
                    <button class="btn-primary continue-post"
                            data-content="He remains silent. The fog swallows the shoreline.">
                        Weiter schreiben
                    </button>
                </div>
            </article>

        </section>

        <!-- Footer -->
        <footer class="overlay-footer">
            <button class="btn-cancel">Schließen</button>
        </footer>

    </div>
</div>

<script>
document.querySelector('.rpg-action-btn.is-primary')?.addEventListener('click', () => {
    document.getElementById('postReplyOverlay')?.classList.remove('hidden');
});

document.querySelector('.post-reply-close')?.addEventListener('click', () => {
    document.getElementById('postReplyOverlay')?.classList.add('hidden');
});
</script>
<script>
document.querySelectorAll('.mau-slice').forEach(slice => {
    slice.addEventListener('click', () => {
        alert('Radial: ' + slice.dataset.action);
    });
});
</script>
<script>
/* Open */
document.querySelector('[data-action="character"]')?.addEventListener('click', () => {
    document.getElementById('characterSelectOverlay')?.classList.remove('hidden');
});

/* Close */
document.querySelector('.character-close')?.addEventListener('click', () => {
    document.getElementById('characterSelectOverlay')?.classList.add('hidden');
});

/* Select Character */
document.querySelectorAll('.character-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.character-card')
            .forEach(c => c.classList.remove('is-selected'));
        card.classList.add('is-selected');

        // später: Übergabe an Post-Editor
        console.log('Selected character:', card.dataset.char);
    });
});
</script>

<script>
/* Öffnen */
document
  .querySelector('[data-action="mood"]')
  ?.addEventListener('click', () => {
      document.getElementById('moodSelectOverlay')
          .classList.remove('hidden');
  });

/* Schließen */
document
  .querySelector('#moodSelectOverlay .overlay-close')
  ?.addEventListener('click', () => {
      document.getElementById('moodSelectOverlay')
          .classList.add('hidden');
  });

/* Auswahl */
document.querySelectorAll('.mood-card').forEach(card => {
    card.addEventListener('click', () => {
        document
          .querySelectorAll('.mood-card')
          .forEach(c => c.classList.remove('is-selected'));
        card.classList.add('is-selected');
    });
});
</script>

<script>
/* Öffnen */
document
  .querySelector('[data-action="saved"]')
  ?.addEventListener('click', () => {
      document.getElementById('savedPostsOverlay')
          .classList.remove('hidden');
  });

/* Schließen */
document.querySelectorAll(
  '#savedPostsOverlay .overlay-close, #savedPostsOverlay .btn-cancel'
).forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('savedPostsOverlay')
            .classList.add('hidden');
    });
});

/* Weiter schreiben */
document.querySelectorAll('.continue-post').forEach(btn => {
    btn.addEventListener('click', () => {
        const content = btn.dataset.content;
        const textarea = document.querySelector('.post-editor-text');

        if (textarea && content) {
            textarea.value = content;
            document.getElementById('savedPostsOverlay')
                .classList.add('hidden');
            textarea.focus();
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
