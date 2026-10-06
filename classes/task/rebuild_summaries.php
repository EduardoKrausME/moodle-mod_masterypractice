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
 * rebuild_summaries.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\task;

use mod_masterypractice\local\class_summary_manager;

/**
 * Class rebuild_summaries.
 */
final class rebuild_summaries extends \core\task\scheduled_task {
    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('taskrebuildsummaries', 'masterypractice');
    }

    /**
     * Method execute.
     *
     * @return void Return value.
     */
    public function execute(): void {
        global $DB;
        $now = time();
        foreach ($DB->get_records('masterypractice', null, '', 'id') as $activity) {
            class_summary_manager::rebuild_activity((int) $activity->id, $now);
        }
    }
}
