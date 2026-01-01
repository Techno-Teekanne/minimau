let RPG = {
    scene: null,
    mood: null,
    poll: null
};

document.querySelectorAll('.rpg-scene-item').forEach(el => {
    el.addEventListener('click', () => {
        RPG.scene = el.dataset.scene;

        const bg = document.querySelector('.rpg-bg');
        bg.style.opacity = 0;

        setTimeout(() => {
            bg.style.backgroundImage = `url(${el.dataset.bg})`;
            bg.style.opacity = 1;
        }, 300);

        startMoodPolling();
    });
});

document.querySelectorAll('#rpg-moodbox button').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!RPG.scene) return;

        fetch('/rpg/set_mood.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `scene=${RPG.scene}&mood=${btn.dataset.mood}`
        });
    });
});

function startMoodPolling() {
    if (RPG.poll) clearInterval(RPG.poll);

    RPG.poll = setInterval(() => {
        fetch(`/rpg/get_mood.php?scene=${RPG.scene}`)
            .then(r => r.json())
            .then(d => {
                if (!d || d.mood === RPG.mood) return;

                RPG.mood = d.mood;
                const layer = document.querySelector('.rpg-mood-layer');
                layer.className = 'rpg-mood-layer ' + d.mood;
            });
    }, 2000);
}
