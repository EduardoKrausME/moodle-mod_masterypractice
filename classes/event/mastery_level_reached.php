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
 * mastery_level_reached.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\event;

/**
 * Class mastery_level_reached.
 */
final class mastery_level_reached extends \core\event\base {
    /**
     * Method init.
     *
     * @return void Return value.
     */
    protected function init(): void {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'masterypractice_concepts';
    }

    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public static function get_name(): string {
        return get_string('eventmasterylevelreached', 'masterypractice');
    }

    /**
     * Method get_description.
     *
     * @return string Return value.
     */
    public function get_description(): string {
        $mastery = $this->other['mastery'] ?? 0;
        return "The user with id '{$this->relateduserid}' reached mastery {$mastery}% for concept '{$this->objectid}'.";
    }
}
