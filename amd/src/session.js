// This file is part of Moodle - http://moodle.org/
//
// Tracks active time per question as an auxiliary learning signal.
// It is not used to grade the learner.

export const init = () => {
    const form = document.querySelector('.masterypractice-session');
    if (!form) {
        return;
    }

    const totals = new Map();
    let activeSlot = null;
    let activeSince = null;

    const flush = () => {
        if (activeSlot === null || activeSince === null) {
            return;
        }
        const elapsed = Math.max(0, performance.now() - activeSince);
        totals.set(activeSlot, (totals.get(activeSlot) || 0) + elapsed);
        activeSince = performance.now();
    };

    const activate = (wrapper) => {
        const slot = wrapper?.dataset.masterySlot;
        if (!slot || slot === activeSlot) {
            return;
        }
        flush();
        activeSlot = slot;
        activeSince = performance.now();
    };

    form.addEventListener('focusin', (event) => {
        activate(event.target.closest('.masterypractice-question'));
    });

    form.addEventListener('pointerdown', (event) => {
        activate(event.target.closest('.masterypractice-question'));
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            flush();
            activeSince = null;
        } else if (activeSlot !== null) {
            activeSince = performance.now();
        }
    });

    form.addEventListener('submit', () => {
        flush();
        form.querySelectorAll('input[data-mastery-time]').forEach((element) => element.remove());

        form.querySelectorAll('.masterypractice-question[data-mastery-slot]').forEach((wrapper) => {
            const slot = wrapper.dataset.masterySlot;
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `masterytime[${slot}]`;
            input.value = Math.max(0, Math.round((totals.get(slot) || 0) / 1000));
            input.dataset.masteryTime = '1';
            form.appendChild(input);
        });

        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
        }
    });
};
