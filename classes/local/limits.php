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
 * limits.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Resolves activity limits against the site's current administrative caps.
 */
final class limits {
    /**
     * Returns effective minimum and maximum review intervals.
     *
     * @param \stdClass $activity Activity.
     * @return array{0:int,1:int}
     */
    public static function intervals(\stdClass $activity): array {
        $adminmin = max(0, (int) get_config('masterypractice', 'adminmininterval'));
        $adminmax = max(0, (int) get_config('masterypractice', 'adminmaxinterval'));

        $minimum = max((int) $activity->mininterval, $adminmin);
        $maximum = (int) $activity->maxinterval;
        if ($adminmax > 0) {
            $maximum = min($maximum, $adminmax);
        }

        // A site administrator can tighten limits after an activity was saved.
        // In that case the safe interpretation is to clamp both to the new min.
        $maximum = max($minimum, $maximum);
        return [$minimum, $maximum];
    }

    /**
     * Returns the effective daily review cap.
     *
     * @param \stdClass $activity Activity.
     * @return int
     */
    public static function max_daily_reviews(\stdClass $activity): int {
        $activitylimit = max(1, (int) $activity->maxdailyreviews);
        $adminlimit = max(0, (int) get_config('masterypractice', 'adminmaxdailyreviews'));

        return $adminlimit > 0 ? min($activitylimit, $adminlimit) : $activitylimit;
    }
}
