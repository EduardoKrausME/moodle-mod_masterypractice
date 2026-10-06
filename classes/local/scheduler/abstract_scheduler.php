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
 * abstract_scheduler.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\local\scheduler;

defined('MOODLE_INTERNAL') || die();

/**
 * Class abstract_scheduler.
 */
abstract class abstract_scheduler implements scheduler_interface {
    /**
     * Method bound_interval.
     *
     * @param int|float $seconds Parameter seconds.
     * @param review_input $input Parameter input.
     * @return int Return value.
     */
    protected function bound_interval(int|float $seconds, review_input $input): int {
        $seconds = (int) round($seconds);
        return max($input->mininterval, min($input->maxinterval, $seconds));
    }

    /**
     * Method streak_fields.
     *
     * @param review_input $input Parameter input.
     * @return array Return value.
     */
    protected function streak_fields(review_input $input): array {
        if ($input->is_success()) {
            return [
                'successstreak' => $input->successstreak + 1,
                'failurestreak' => 0,
            ];
        }

        return [
            'successstreak' => 0,
            'failurestreak' => $input->failurestreak + 1,
        ];
    }

    /**
     * Method spacing_factor.
     *
     * @param review_input $input Parameter input.
     * @return float Return value.
     */
    protected function spacing_factor(review_input $input): float {
        $days = $input->days_since_review();
        if ($days <= 0.0) {
            return 0.45;
        }

        return min(1.5, max(0.55, log(1.0 + $days, 2) / 3.0 + 0.45));
    }
}
