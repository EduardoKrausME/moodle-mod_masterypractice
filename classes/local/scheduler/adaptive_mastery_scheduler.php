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
 * adaptive_mastery_scheduler.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\local\scheduler;

/**
 * Class adaptive_mastery_scheduler.
 */
final class adaptive_mastery_scheduler extends abstract_scheduler {
    /**
     * Method key.
     *
     * @return string Return value.
     */
    public function key(): string {
        return 'adaptive';
    }

    /**
     * Method review.
     *
     * @param review_input $input Parameter input.
     * @return review_result Return value.
     */
    public function review(review_input $input): review_result {
        $fraction = max(0.0, min(1.0, $input->fraction));
        $difficulty = max(0.0, min(1.0, $input->difficulty));
        $spacing = $this->spacing_factor($input);
        $signed = ($fraction - 0.5) * 2.0;

        if ($signed >= 0.0) {
            $difficultyfactor = 0.75 + 0.75 * $difficulty;
            $repeatpenalty = 1.0 / (1.0 + 0.18 * max(0, $input->successstreak));
            $diminishing = max(0.18, 1.0 - $input->mastery / 118.0);
            $masterydelta = $signed * 13.0 * $difficultyfactor * $spacing
                * $repeatpenalty * $diminishing;
            $confidencedelta = 3.0 + 4.0 * $spacing + 2.0 * $difficulty;

            $masteryafter = min(100.0, $input->mastery + $masterydelta);
            $confidenceafter = min(100.0, $input->confidence + $confidencedelta);
            $days = 1.0
                + 24.0 * pow($masteryafter / 100.0, 2)
                * (0.55 + $confidenceafter / 100.0)
                * (1.0 + min(5, $input->successstreak) * 0.16);
            $rawinterval = $days * DAYSECS;
        } else {
            $unexpected = 0.65 + $input->mastery / 100.0;
            $easyfailure = 1.2 - 0.35 * $difficulty;
            $masterydelta = $signed * 15.0 * $unexpected * $easyfailure;
            $confidencedelta = -(8.0 + $input->mastery * 0.065 + max(0, $input->failurestreak) * 1.5);

            $urgency = max(0.2, 1.0 - $input->mastery / 140.0);
            $rawinterval = max(6 * HOURSECS, 2 * DAYSECS * $urgency);
        }

        $interval = $this->bound_interval($rawinterval, $input);

        return new review_result(
            $input->now + $interval,
            $interval,
            $masterydelta,
            $confidencedelta,
            $this->streak_fields($input)
        );
    }
}
