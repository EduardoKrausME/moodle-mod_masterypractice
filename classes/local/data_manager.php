<?php
// This file is part of Moodle - http://moodle.org/

namespace mod_masterypractice\local;

defined('MOODLE_INTERNAL') || die();

use mod_masterypractice\local\mastery\difficulty_estimator;

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

        $sql = "SELECT sq.entryid,
                       COUNT(sq.id) AS attempts,
                       SUM(sq.fraction) AS totalfraction
                  FROM {masterypractice_squestions} sq
                  JOIN {masterypractice_sessions} s ON s.id = sq.sessionid
                 WHERE s.masterypracticeid = :activityid
                   AND s.state = :state
                   AND sq.fraction IS NOT NULL
              GROUP BY sq.entryid";
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
