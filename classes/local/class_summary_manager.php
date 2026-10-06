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
 * class_summary_manager.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\local;
defined('MOODLE_INTERNAL') || die();

/**
 * Class class_summary_manager.
 */
final class class_summary_manager {
    /**
     * Method rebuild_activity.
     *
     * @param int $activityid Parameter activityid.
     * @param ?int $now Parameter now.
     * @return void Return value.
     */
    public static function rebuild_activity(int $activityid, ?int $now = null): void {
        global $DB;
        $now = $now ?? time();

        foreach ($DB->get_records('masterypractice_concepts', ['masterypracticeid' => $activityid]) as $concept) {
            $aggregate = $DB->get_record_sql(
                "SELECT COUNT(id) AS usercount,
                        COALESCE(AVG(mastery), 0) AS avgmastery,
                        COALESCE(AVG(confidence), 0) AS avgconfidence,
                        COALESCE(SUM(CASE WHEN nextreview > 0 AND nextreview <= :now THEN 1 ELSE 0 END), 0) AS overduecount,
                        COALESCE(SUM(CASE WHEN mastery < 50 THEN 1 ELSE 0 END), 0) AS lowcount
                   FROM {masterypractice_cstate}
                  WHERE masterypracticeid = :activityid
                    AND conceptid = :conceptid",
                ['now' => $now, 'activityid' => $activityid, 'conceptid' => $concept->id]
            );

            $summary = $DB->get_record('masterypractice_csummary', [
                'masterypracticeid' => $activityid,
                'conceptid' => $concept->id,
            ]) ?: (object) [
                'masterypracticeid' => $activityid,
                'conceptid' => $concept->id,
            ];

            $summary->usercount = (int) ($aggregate->usercount ?? 0);
            $summary->avgmastery = round((float) ($aggregate->avgmastery ?? 0), 2);
            $summary->avgconfidence = round((float) ($aggregate->avgconfidence ?? 0), 2);
            $summary->overduecount = (int) ($aggregate->overduecount ?? 0);
            $summary->lowcount = (int) ($aggregate->lowcount ?? 0);
            $summary->timemodified = $now;

            if (empty($summary->id)) {
                $DB->insert_record('masterypractice_csummary', $summary);
            } else {
                $DB->update_record('masterypractice_csummary', $summary);
            }
        }
    }
}
