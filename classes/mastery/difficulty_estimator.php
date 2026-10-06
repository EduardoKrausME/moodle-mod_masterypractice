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
 * difficulty_estimator.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\mastery;

/**
 * Class difficulty_estimator.
 */
final class difficulty_estimator {
    /**
     * Method estimate.
     *
     * @param int $attempts Parameter attempts.
     * @param float $totalfraction Parameter totalfraction.
     * @param int $minimumsample Parameter minimumsample.
     * @return float Return value.
     */
    public static function estimate(int $attempts, float $totalfraction, int $minimumsample): float {
        $minimumsample = max(1, $minimumsample);
        if ($attempts < $minimumsample) {
            return 0.5;
        }
        $priorstrength = (float) $minimumsample;
        $posterioraccuracy = ($totalfraction + 0.5 * $priorstrength) / ($attempts + $priorstrength);
        return max(0.05, min(0.95, 1.0 - $posterioraccuracy));
    }
}
