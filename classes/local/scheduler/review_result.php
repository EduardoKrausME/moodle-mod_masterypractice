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
 * review_result.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\local\scheduler;

defined('MOODLE_INTERNAL') || die();

/**
 * Class review_result.
 */
final class review_result {
    /**
     * Method __construct.
     *
     * @param int $nextreview Parameter nextreview.
     * @param int $interval Parameter interval.
     * @param float $masterydelta Parameter masterydelta.
     * @param float $confidencedelta Parameter confidencedelta.
     * @param array $itemfields Parameter itemfields.
     */
    public function __construct(
        public readonly int $nextreview,
        public readonly int $interval,
        public readonly float $masterydelta,
        public readonly float $confidencedelta,
        public readonly array $itemfields = [],
    ) {
    }
}
