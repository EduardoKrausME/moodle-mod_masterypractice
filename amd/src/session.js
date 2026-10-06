// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * session.js
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    const init = function() {
        const form = document.querySelector('.masterypractice-session');
        if (!form) {
            return;
        }

        const totals = new Map();
        let activeSlot = null;
        let activeSince = null;

        const flush = function() {
            if (activeSlot === null || activeSince === null) {
                return;
            }

            const elapsed = Math.max(0, performance.now() - activeSince);
            totals.set(activeSlot, (totals.get(activeSlot) || 0) + elapsed);
            activeSince = performance.now();
        };

        const activate = function(wrapper) {
            if (!wrapper) {
                return;
            }

            const slot = wrapper.dataset.masterySlot;
            if (!slot || slot === activeSlot) {
                return;
            }

            flush();
            activeSlot = slot;
            activeSince = performance.now();
        };

        form.addEventListener('focusin', function(event) {
            activate(event.target.closest('.masterypractice-question'));
        });

        form.addEventListener('pointerdown', function(event) {
            activate(event.target.closest('.masterypractice-question'));
        });

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                flush();
                activeSince = null;
            } else if (activeSlot !== null) {
                activeSince = performance.now();
            }
        });

        form.addEventListener('submit', function() {
            flush();

            form.querySelectorAll('input[data-mastery-time]').forEach(function(element) {
                element.remove();
            });

            form.querySelectorAll('.masterypractice-question[data-mastery-slot]').forEach(function(wrapper) {
                const slot = wrapper.dataset.masterySlot;
                const input = document.createElement('input');

                input.type = 'hidden';
                input.name = 'masterytime[' + slot + ']';
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

    return {
        init: init,
    };
});
