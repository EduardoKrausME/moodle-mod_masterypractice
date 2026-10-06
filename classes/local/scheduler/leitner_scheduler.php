<?php
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
 * leitner_scheduler.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\local\scheduler;

/**
 * Class leitner_scheduler.
 */
final class leitner_scheduler extends abstract_scheduler {
    private const BOX_INTERVALS = [
        DAYSECS,
        3 * DAYSECS,
        7 * DAYSECS,
        14 * DAYSECS,
        30 * DAYSECS,
        60 * DAYSECS,
    ];

    /**
     * Method key.
     *
     * @return string Return value.
     */
    public function key(): string {
        return 'leitner';
    }

    /**
     * Method review.
     *
     * @param review_input $input Parameter input.
     * @return review_result Return value.
     */
    public function review(review_input $input): review_result {
        $oldbox = max(1, min(6, (int) ($input->itemstate->leitnerbox ?? 1)));
        $box = $input->is_success() ? min(6, $oldbox + 1) : 1;
        $interval = $this->bound_interval(self::BOX_INTERVALS[$box - 1], $input);

        $spacing = $this->spacing_factor($input);
        if ($input->is_success()) {
            $gain = (7.0 + 4.0 * $input->difficulty) * $spacing;
            $diminishing = max(0.2, 1.0 - ($input->mastery / 120.0));
            $masterydelta = $gain * $diminishing;
            $confidencedelta = 3.0 + 2.0 * min(1.0, $spacing);
        } else {
            $penalty = (10.0 + 4.0 * (1.0 - $input->difficulty))
                * (0.65 + $input->mastery / 100.0);
            $masterydelta = -$penalty;
            $confidencedelta = -(6.0 + $input->mastery * 0.04);
        }

        return new review_result(
            $input->now + $interval,
            $interval,
            $masterydelta,
            $confidencedelta,
            ['leitnerbox' => $box] + $this->streak_fields($input)
        );
    }
}
