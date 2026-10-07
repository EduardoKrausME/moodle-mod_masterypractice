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
 * provider.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use mod_masterypractice\data_manager;

/**
 * Privacy API provider for Mastery Practice.
 */
final class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Describes stored learner data.
     *
     * @param collection $items Collection.
     * @return collection
     */
    public static function get_metadata(collection $items): collection {
        $items->add_database_table('masterypractice_cstate', [
            'userid' => 'privacy:metadata:userid',
            'mastery' => 'privacy:metadata:mastery',
            'confidence' => 'privacy:metadata:confidence',
            'lastreview' => 'privacy:metadata:lastreview',
            'nextreview' => 'privacy:metadata:nextreview',
            'avgresponsetime' => 'privacy:metadata:responsetime',
        ], 'privacy:metadata:masterypractice_cstate');

        $items->add_database_table('masterypractice_qstate', [
            'userid' => 'privacy:metadata:userid',
            'lastreview' => 'privacy:metadata:lastreview',
            'nextreview' => 'privacy:metadata:nextreview',
        ], 'privacy:metadata:masterypractice_qstate');

        $items->add_database_table('masterypractice_sessions', [
            'userid' => 'privacy:metadata:userid',
            'state' => 'privacy:metadata:session',
            'score' => 'privacy:metadata:session',
            'timestarted' => 'privacy:metadata:session',
            'timecompleted' => 'privacy:metadata:session',
        ], 'privacy:metadata:masterypractice_sessions');

        $items->add_database_table('masterypractice_squestions', [
            'questionid' => 'privacy:metadata:question',
            'entryid' => 'privacy:metadata:question',
            'fraction' => 'privacy:metadata:question',
            'responsetime' => 'privacy:metadata:responsetime',
        ], 'privacy:metadata:masterypractice_squestions');

        $items->add_database_table('masterypractice_history', [
            'userid' => 'privacy:metadata:userid',
            'mastery' => 'privacy:metadata:mastery',
            'confidence' => 'privacy:metadata:confidence',
        ], 'privacy:metadata:masterypractice_history');

        $items->add_database_table('masterypractice_usummary', [
            'userid' => 'privacy:metadata:userid',
            'overallmastery' => 'privacy:metadata:mastery',
            'overallconfidence' => 'privacy:metadata:confidence',
            'nextreview' => 'privacy:metadata:nextreview',
        ], 'privacy:metadata:masterypractice_usummary');

        $items->link_subsystem('core_question', 'privacy:metadata:core_question');
        return $items;
    }

    /**
     * Finds module contexts containing data for a learner.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $params = [
            'modname' => 'masterypractice',
            'contextlevel' => CONTEXT_MODULE,
            'userid1' => $userid,
            'userid2' => $userid,
            'userid3' => $userid,
            'userid4' => $userid,
        ];
        $sql = "SELECT DISTINCT ctx.id
                  FROM {masterypractice} mp
                  JOIN {modules} m ON m.name = :modname
                  JOIN {course_modules} cm ON cm.module = m.id AND cm.instance = mp.id
                  JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextlevel
             LEFT JOIN {masterypractice_cstate} cs
                    ON cs.masterypracticeid = mp.id AND cs.userid = :userid1
             LEFT JOIN {masterypractice_qstate} qs
                    ON qs.masterypracticeid = mp.id AND qs.userid = :userid2
             LEFT JOIN {masterypractice_sessions} s
                    ON s.masterypracticeid = mp.id AND s.userid = :userid3
             LEFT JOIN {masterypractice_usummary} us
                    ON us.masterypracticeid = mp.id AND us.userid = :userid4
                 WHERE cs.id IS NOT NULL
                    OR qs.id IS NOT NULL
                    OR s.id IS NOT NULL
                    OR us.id IS NOT NULL";
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Adds users who have data in a module context.
     *
     * @param userlist $userlist User list.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }

        $activityid = self::activity_id_from_context($context);
        if (!$activityid) {
            return;
        }

        $params = ['activityid' => $activityid];
        $tables = [
            'masterypractice_cstate',
            'masterypractice_qstate',
            'masterypractice_sessions',
            'masterypractice_usummary',
        ];
        foreach ($tables as $table) {
            $sql = "SELECT userid FROM {{$table}} WHERE masterypracticeid = :activityid";
            $userlist->add_from_sql('userid', $sql, $params);
        }
    }

    /**
     * Exports all plugin and owned Question Engine data for approved contexts.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/question/engine/lib.php');

        $userid = (int) $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $activityid = self::activity_id_from_context($context);
            if (!$activityid) {
                continue;
            }

            $activity = $DB->get_record('masterypractice', ['id' => $activityid], '*', MUST_EXIST);
            $contextdata = helper::get_context_data($context, $contextlist->get_user());
            writer::with_context($context)->export_data([], $contextdata);

            $conceptstates = array_values($DB->get_records(
                'masterypractice_cstate',
                ['masterypracticeid' => $activityid, 'userid' => $userid],
                'conceptid ASC'
            ));
            $questionstates = array_values($DB->get_records(
                'masterypractice_qstate',
                ['masterypracticeid' => $activityid, 'userid' => $userid],
                'entryid ASC'
            ));
            $history = array_values($DB->get_records(
                'masterypractice_history',
                ['masterypracticeid' => $activityid, 'userid' => $userid],
                'timecreated ASC'
            ));
            $summary = $DB->get_record('masterypractice_usummary', [
                'masterypracticeid' => $activityid,
                'userid' => $userid,
            ]);

            writer::with_context($context)->export_related_data([], 'mastery', (object) [
                'concept_states' => self::clean_records($conceptstates),
                'question_schedule' => self::clean_records($questionstates),
                'history' => self::clean_records($history),
                'summary' => $summary ? self::clean_record($summary) : null,
            ]);

            $sessions = $DB->get_records(
                'masterypractice_sessions',
                ['masterypracticeid' => $activityid, 'userid' => $userid],
                'timestarted ASC'
            );
            foreach ($sessions as $session) {
                $sessionpath = [get_string('sessionheader', 'masterypractice'), (string) $session->id];
                $questions = array_values($DB->get_records(
                    'masterypractice_squestions',
                    ['sessionid' => $session->id],
                    'slot ASC'
                ));
                writer::with_context($context)->export_data($sessionpath, (object) [
                    'session' => self::clean_record($session),
                    'questions' => self::clean_records($questions),
                ]);

                if (!empty($session->qubaid)) {
                    $options = new \question_display_options();
                    $options->marks = \question_display_options::MARK_AND_MAX;
                    $options->correctness = \question_display_options::VISIBLE;
                    $options->feedback = \question_display_options::VISIBLE;
                    $options->generalfeedback = \question_display_options::VISIBLE;
                    $options->rightanswer = \question_display_options::VISIBLE;
                    $options->history = \question_display_options::VISIBLE;
                    $options->flags = \question_display_options::VISIBLE;
                    $options->manualcomment = \question_display_options::VISIBLE;
                    \core_question\privacy\provider::export_question_usage(
                        $userid,
                        $context,
                        $sessionpath,
                        (int) $session->qubaid,
                        $options,
                        true
                    );
                }
            }
        }
    }

    /**
     * Deletes all learner data in a module context.
     *
     * @param \context $context Context.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        if (!$context instanceof \context_module) {
            return;
        }
        $activityid = self::activity_id_from_context($context);
        if ($activityid) {
            data_manager::delete_activity_data($activityid);
        }
    }

    /**
     * Deletes one learner's data in approved contexts.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        $userid = (int) $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $activityid = self::activity_id_from_context($context);
            if ($activityid) {
                data_manager::delete_user_data($activityid, $userid);
            }
        }
    }

    /**
     * Deletes multiple approved users in one context.
     *
     * @param approved_userlist $userlist Approved list.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }
        $activityid = self::activity_id_from_context($context);
        if (!$activityid) {
            return;
        }

        foreach ($userlist->get_userids() as $userid) {
            data_manager::delete_user_data($activityid, (int) $userid);
        }
    }

    /**
     * Maps a module context to an activity id.
     *
     * @param \context_module $context Module context.
     * @return int
     */
    private static function activity_id_from_context(\context_module $context): int {
        $cm = get_coursemodule_from_id('masterypractice', $context->instanceid, 0, false, IGNORE_MISSING);
        return $cm ? (int) $cm->instance : 0;
    }

    /**
     * Removes database-only identity fields from export records.
     *
     * @param \stdClass $record Record.
     * @return \stdClass
     */
    private static function clean_record(\stdClass $record): \stdClass {
        $copy = clone $record;
        unset($copy->id, $copy->masterypracticeid, $copy->userid);
        return $copy;
    }

    /**
     * Cleans a list of records.
     *
     * @param array $records Records.
     * @return array
     */
    private static function clean_records(array $records): array {
        return array_map([self::class, 'clean_record'], $records);
    }
}
