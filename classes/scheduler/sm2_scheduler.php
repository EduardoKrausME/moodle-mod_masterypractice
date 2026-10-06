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
 * sm2_scheduler.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\scheduler;

/**
 * SM-2 adapted to Moodle fractions.
 *
 * Keeps the original SM-2 quality/easiness/repetition mechanics. Moodle
 * fractions are deterministically mapped to quality 0..5, then activity/site
 * interval limits are applied after the SM-2 interval is calculated.
 */
final class sm2_scheduler extends abstract_scheduler {
    /**
     * Method key.
     *
     * @return string Return value.
     */
    public function key(): string {
        return 'sm2';
    }

    /**
     * Method quality_from_fraction.
     *
     * @param float $fraction Parameter fraction.
     * @return int Return value.
     */
    public static function quality_from_fraction(float $fraction): int {
        $fraction = max(0.0, min(1.0, $fraction));
        return match (true) {
            $fraction < 0.20 => 0,
            $fraction < 0.40 => 1,
            $fraction < 0.60 => 2,
            $fraction < 0.80 => 3,
            $fraction < 0.95 => 4,
            default => 5,
        };
    }

    /**
     * Method review.
     *
     * @param review_input $input Parameter input.
     * @return review_result Return value.
     */
    public function review(review_input $input): review_result {
        $quality = self::quality_from_fraction($input->fraction);
        $oldrepetitions = max(0, (int) ($input->itemstate->sm2repetitions ?? 0));
        $oldinterval = max(0, (int) ($input->itemstate->sm2interval ?? 0));
        $easiness = (float) ($input->itemstate->sm2easiness ?? 2.5);
        if ($easiness <= 0.0) {
            $easiness = 2.5;
        }

        $difference = 5 - $quality;
        $easiness += 0.1 - $difference * (0.08 + $difference * 0.02);
        $easiness = max(1.3, $easiness);

        if ($quality < 3) {
            $repetitions = 0;
            $rawinterval = DAYSECS;
        } else {
            $repetitions = $oldrepetitions + 1;
            if ($repetitions === 1) {
                $rawinterval = DAYSECS;
            } else if ($repetitions === 2) {
                $rawinterval = 6 * DAYSECS;
            } else {
                $previous = $oldinterval > 0 ? $oldinterval : 6 * DAYSECS;
                $rawinterval = (int) round($previous * $easiness);
            }
        }

        $interval = $this->bound_interval($rawinterval, $input);
        $spacing = $this->spacing_factor($input);

        if ($quality >= 3) {
            $qualityfactor = ($quality - 2.0) / 3.0;
            $gain = (5.0 + 5.0 * $input->difficulty) * $qualityfactor * $spacing;
            $masterydelta = $gain * max(0.2, 1.0 - $input->mastery / 120.0);
            $confidencedelta = 2.5 + $qualityfactor * 3.5 + min(2.0, $spacing);
        } else {
            $severity = (3 - $quality) / 3.0;
            $masterydelta = -(8.0 + 8.0 * $severity)
                * (0.7 + $input->mastery / 100.0)
                * (1.15 - 0.3 * $input->difficulty);
            $confidencedelta = -(6.0 + 6.0 * $severity + $input->mastery * 0.035);
        }

        return new review_result(
            $input->now + $interval,
            $interval,
            $masterydelta,
            $confidencedelta,
            [
                'sm2repetitions' => $repetitions,
                'sm2interval' => $interval,
                'sm2easiness' => $easiness,
            ] + $this->streak_fields($input)
        );
    }
}
