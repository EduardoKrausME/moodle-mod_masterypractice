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
 * Course module viewed event.
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\event;

/**
 * Records an activity view in Moodle's standard event log.
 */
final class course_module_viewed extends \core\event\course_module_viewed {
    /**
     * Define the event metadata.
     *
     * @return void
     */
    protected function init(): void {
        $this->data["objecttable"] = "masterypractice";
        $this->data["crud"] = "r";
        $this->data["edulevel"] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Map viewed activity IDs when restoring course logs.
     *
     * @return array Database and restore table mappings.
     */
    public static function get_objectid_mapping(): array {
        return ["db" => "masterypractice", "restore" => "masterypractice"];
    }
}
