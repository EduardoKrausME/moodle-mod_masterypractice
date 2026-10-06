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
 * review_input.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\local\scheduler;

defined('MOODLE_INTERNAL') || die();

/**
 * Class review_input.
 */
final class review_input {
    /**
     * Method __construct.
     *
     * @param float $fraction Parameter fraction.
     * @param float $difficulty Parameter difficulty.
     * @param int $now Parameter now.
     * @param int $lastreview Parameter lastreview.
     * @param int $mininterval Parameter mininterval.
     * @param int $maxinterval Parameter maxinterval.
     * @param float $mastery Parameter mastery.
     * @param float $confidence Parameter confidence.
     * @param int $successstreak Parameter successstreak.
     * @param int $failurestreak Parameter failurestreak.
     * @param ?\stdClass $itemstate Parameter itemstate.
     */
    public function __construct(
        public readonly float $fraction,
        public readonly float $difficulty,
        public readonly int $now,
        public readonly int $lastreview,
        public readonly int $mininterval,
        public readonly int $maxinterval,
        public readonly float $mastery,
        public readonly float $confidence,
        public readonly int $successstreak,
        public readonly int $failurestreak,
        public readonly ?\stdClass $itemstate = null,
    ) {
    }

    /**
     * Method is_success.
     *
     * @return bool Return value.
     */
    public function is_success(): bool {
        return $this->fraction >= 0.8;
    }

    /**
     * Method days_since_review.
     *
     * @return float Return value.
     */
    public function days_since_review(): float {
        if ($this->lastreview <= 0 || $this->now <= $this->lastreview) {
            return 0.0;
        }
        return ($this->now - $this->lastreview) / DAYSECS;
    }
}
