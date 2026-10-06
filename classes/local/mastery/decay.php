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
 * decay.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\local\mastery;

/**
 * Class decay.
 */
final class decay {
    /**
     * Method estimate.
     *
     * @param float $value Parameter value.
     * @param int $lastreview Parameter lastreview.
     * @param int $now Parameter now.
     * @param int $halflifedays Parameter halflifedays.
     * @return float Return value.
     */
    public static function estimate(float $value, int $lastreview, int $now, int $halflifedays): float {
        $value = max(0.0, min(100.0, $value));
        if ($value <= 0.0 || $lastreview <= 0 || $now <= $lastreview || $halflifedays <= 0) {
            return $value;
        }
        $elapsed = ($now - $lastreview) / DAYSECS;
        return max(0.0, min(100.0, $value * pow(0.5, $elapsed / $halflifedays)));
    }

    /**
     * Method estimate_confidence.
     *
     * @param float $confidence Parameter confidence.
     * @param int $lastreview Parameter lastreview.
     * @param int $now Parameter now.
     * @param int $halflifedays Parameter halflifedays.
     * @return float Return value.
     */
    public static function estimate_confidence(float $confidence, int $lastreview, int $now, int $halflifedays): float {
        return self::estimate($confidence, $lastreview, $now, max(1, (int) round($halflifedays * 0.75)));
    }
}
