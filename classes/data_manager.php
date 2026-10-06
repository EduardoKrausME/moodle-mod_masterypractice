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
 * data_manager.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice;

use mod_masterypractice\mastery\difficulty_estimator;

/**
 * Centralised deletion of learner-derived data and Question Engine usages.
 */
final class data_manager {
    /**
     * Deletes all learner data for an activity, retaining configuration.
     *
     * @param int $activityid Activity id.
     * @return void
     */
    public static function delete_activity_data(int $activityid): void {
        global $DB;

        self::delete_sessions($DB->get_records(
            'masterypractice_sessions',
            ['masterypracticeid' => $activityid],
            '',
            'id,qubaid'
        ));

        $DB->delete_records('masterypractice_history', ['masterypracticeid' => $activityid]);
        $DB->delete_records('masterypractice_cstate', ['masterypracticeid' => $activityid]);
        $DB->delete_records('masterypractice_qstate', ['masterypracticeid' => $activityid]);
        $DB->delete_records('masterypractice_usummary', ['masterypracticeid' => $activityid]);
        $DB->delete_records('masterypractice_csummary', ['masterypracticeid' => $activityid]);
        $DB->delete_records('masterypractice_qstats', ['masterypracticeid' => $activityid]);
    }

    /**
     * Deletes one learner's activity data.
     *
     * @param int $activityid Activity id.
     * @param int $userid User id.
     * @return void
     */
    public static function delete_user_data(int $activityid, int $userid): void {
        global $DB;

        self::delete_sessions($DB->get_records(
            'masterypractice_sessions',
            ['masterypracticeid' => $activityid, 'userid' => $userid],
            '',
            'id,qubaid'
        ));

        $DB->delete_records('masterypractice_history', [
            'masterypracticeid' => $activityid,
            'userid' => $userid,
        ]);
        $DB->delete_records('masterypractice_cstate', [
            'masterypracticeid' => $activityid,
            'userid' => $userid,
        ]);
        $DB->delete_records('masterypractice_qstate', [
            'masterypracticeid' => $activityid,
            'userid' => $userid,
        ]);
        $DB->delete_records('masterypractice_usummary', [
            'masterypracticeid' => $activityid,
            'userid' => $userid,
        ]);

        // Force the teacher dashboard to rebuild without the deleted user,
        // then recompute item difficulty from the remaining completed sessions.
        $DB->delete_records('masterypractice_csummary', ['masterypracticeid' => $activityid]);
        self::rebuild_question_statistics($activityid);
    }

    /**
     * Deletes all derived state for one concept.
     *
     * @param int $conceptid Concept id.
     * @return void
     */
    public static function delete_concept_data(int $conceptid): void {
        global $DB;

        $DB->delete_records('masterypractice_history', ['conceptid' => $conceptid]);
        $DB->delete_records('masterypractice_cstate', ['conceptid' => $conceptid]);
        $DB->delete_records('masterypractice_csummary', ['conceptid' => $conceptid]);
    }

    /**
     * Rebuilds aggregate item difficulty from the remaining learner evidence.
     *
     * This is intentionally used only after privacy deletion. Normal session
     * completion updates the same aggregate incrementally.
     *
     * @param int $activityid Activity id.
     * @return void
     */
    private static function rebuild_question_statistics(int $activityid): void {
        global $DB;

        $DB->delete_records('masterypractice_qstats', ['masterypracticeid' => $activityid]);

        $sql = "SELECT firstseen.entryid,
                       COUNT(firstseen.userid) AS attempts,
                       SUM(sq.fraction) AS totalfraction
                  FROM (
                        SELECT sq0.entryid, s0.userid, MIN(sq0.id) AS firstquestionid
                          FROM {masterypractice_squestions} sq0
                          JOIN {masterypractice_sessions} s0 ON s0.id = sq0.sessionid
                         WHERE s0.masterypracticeid = :activityid
                           AND s0.state = :state
                           AND sq0.fraction IS NOT NULL
                      GROUP BY sq0.entryid, s0.userid
                  ) firstseen
                  JOIN {masterypractice_squestions} sq ON sq.id = firstseen.firstquestionid
              GROUP BY firstseen.entryid";
        $records = $DB->get_records_sql($sql, [
            'activityid' => $activityid,
            'state' => 'completed',
        ]);
        $minimum = max(1, (int) get_config('masterypractice', 'difficultysamples'));
        $now = time();

        foreach ($records as $record) {
            $DB->insert_record('masterypractice_qstats', (object) [
                'masterypracticeid' => $activityid,
                'entryid' => (int) $record->entryid,
                'attempts' => (int) $record->attempts,
                'totalfraction' => (float) $record->totalfraction,
                'difficulty' => difficulty_estimator::estimate(
                    (int) $record->attempts,
                    (float) $record->totalfraction,
                    $minimum
                ),
                'timemodified' => $now,
            ]);
        }
    }

    /**
     * Deletes session rows and their Question Engine usages.
     *
     * @param \stdClass[] $sessions Sessions.
     * @return void
     */
    private static function delete_sessions(array $sessions): void {
        global $CFG, $DB;

        if (!$sessions) {
            return;
        }

        require_once($CFG->dirroot . '/question/engine/lib.php');
        $sessionids = [];
        foreach ($sessions as $session) {
            $sessionids[] = (int) $session->id;
            if (!empty($session->qubaid)) {
                \question_engine::delete_questions_usage_by_activity((int) $session->qubaid);
            }
        }

        [$insql, $params] = $DB->get_in_or_equal($sessionids, SQL_PARAMS_NAMED, 'session');
        $DB->delete_records_select('masterypractice_squestions', "sessionid {$insql}", $params);
        $DB->delete_records_select('masterypractice_sessions', "id {$insql}", $params);
    }
}
