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
 * factory.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\local\scheduler;

defined('MOODLE_INTERNAL') || die();

/**
 * Class factory.
 */
final class factory {
    /**
     * Method create.
     *
     * @param string $key Parameter key.
     * @return scheduler_interface Return value.
     */
    public static function create(string $key): scheduler_interface {
        return match ($key) {
            'leitner' => new leitner_scheduler(),
            'sm2' => new sm2_scheduler(),
            'adaptive' => new adaptive_mastery_scheduler(),
            default => throw new \coding_exception('Unknown Mastery Practice scheduler: ' . $key),
        };
    }
}
