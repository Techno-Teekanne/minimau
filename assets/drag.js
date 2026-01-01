const box = document.getElementById('rpg-moodbox');
const min = document.getElementById('rpg-moodbox-minimized');

let drag = false, ox = 0, oy = 0;

box.addEventListener('mousedown', e => {
    drag = true;
    ox = e.offsetX;
    oy = e.offsetY;
});

document.addEventListener('mouseup', () => drag = false);

document.addEventListener('mousemove', e => {
    if (!drag) return;
    box.style.left = (e.pageX - ox) + 'px';
    box.style.top  = (e.pageY - oy) + 'px';
});

min.addEventListener('click', () => {
    box.style.display = 'block';
    min.style.display = 'none';
});
