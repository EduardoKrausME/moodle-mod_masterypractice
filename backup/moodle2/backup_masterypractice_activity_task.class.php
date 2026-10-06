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
 * backup_masterypractice_activity_task.class.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/mod/masterypractice/backup/moodle2/backup_masterypractice_stepslib.php');

/**
 * Backup task for Mastery Practice.
 */
class backup_masterypractice_activity_task extends backup_activity_task {
    /**
     * No activity-specific backup settings.
     */
    protected function define_my_settings() {
    }

    /**
     * Adds the structure step.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_masterypractice_activity_structure_step(
            'masterypractice_structure',
            'masterypractice.xml'
        ));
    }

    /**
     * Encodes links in activity content.
     *
     * @param string $content Content.
     * @return string
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot . '/mod/masterypractice', '#');

        $content = preg_replace(
            '#(' . $base . '/index\.php\?id=)([0-9]+)#',
            '$@MASTERYPRACTICEINDEX*$2@$',
            $content
        );
        $content = preg_replace(
            '#(' . $base . '/view\.php\?id=)([0-9]+)#',
            '$@MASTERYPRACTICEVIEWBYID*$2@$',
            $content
        );

        return $content;
    }
}
