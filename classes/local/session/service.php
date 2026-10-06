<?php
// This file is part of Moodle - http://moodle.org/

namespace mod_masterypractice\local\session;

defined('MOODLE_INTERNAL') || die();

use mod_masterypractice\local\limits;
use mod_masterypractice\local\mastery\state_manager;
use mod_masterypractice\local\selection\question_selector;
use mod_masterypractice\local\summary_manager;

/**
 * Owns Practice Session lifecycle while delegating question execution to core.
 */
final class service {
    /**
     * Starts or resumes a learner's session.
     *
     * @param \stdClass $activity Activity.
     * @param \stdClass $cm Course module.
     * @param int $userid User id.
     * @param array $responsetimes Client-observed active seconds keyed by slot.
     * @param int|null $now Timestamp.
     * @return \stdClass Session record.
     */
    public static function start(
        \stdClass $activity,
        \stdClass $cm,
        int $userid,
        ?int $now = null
    ): \stdClass {
        global $CFG, $DB;

        $now = $now ?? time();
        $existing = $DB->get_record('masterypractice_sessions', [
            'masterypracticeid' => $activity->id,
            'userid' => $userid,
            'state' => 'inprogress',
        ]);
        if ($existing) {
            return $existing;
        }

        $last = $DB->get_record_sql(
            "SELECT *
               FROM {masterypractice_sessions}
              WHERE masterypracticeid = :activityid
                AND userid = :userid
                AND state = :state
           ORDER BY timecompleted DESC",
            ['activityid' => $activity->id, 'userid' => $userid, 'state' => 'completed'],
            IGNORE_MULTIPLE
        );
        if ($last && (int) $activity->minsessioninterval > 0
                && (int) $last->timecompleted + (int) $activity->minsessioninterval > $now) {
            $available = (int) $last->timecompleted + (int) $activity->minsessioninterval;
            throw new \moodle_exception(
                'sessiontoosoon',
                'masterypractice',
                '',
                userdate($available, get_string('strftimedatetimeshort', 'langconfig'))
            );
        }

        if ($last && empty($activity->allowextra)) {
            $dueexists = $DB->record_exists_select(
                'masterypractice_qstate',
                'masterypracticeid = :activityid AND userid = :userid AND nextreview > 0 AND nextreview <= :now',
                ['activityid' => $activity->id, 'userid' => $userid, 'now' => $now]
            );
            if (!$dueexists) {
                $next = (int) $DB->get_field('masterypractice_usummary', 'nextreview', [
                    'masterypracticeid' => $activity->id,
                    'userid' => $userid,
                ]);
                throw new \moodle_exception(
                    'nothingdue',
                    'masterypractice',
                    '',
                    $next ? userdate($next, get_string('strftimedatetimeshort', 'langconfig')) : '-'
                );
            }
        }

        $selected = question_selector::select($activity, $userid, $now);
        if (!$selected) {
            $daystart = usergetmidnight($now);
            $used = (int) $DB->count_records_sql(
                "SELECT COUNT(sq.id)
                   FROM {masterypractice_squestions} sq
                   JOIN {masterypractice_sessions} s ON s.id = sq.sessionid
                  WHERE s.masterypracticeid = :activityid
                    AND s.userid = :userid
                    AND s.state = :state
                    AND s.timecompleted >= :daystart",
                [
                    'activityid' => $activity->id,
                    'userid' => $userid,
                    'state' => 'completed',
                    'daystart' => $daystart,
                ]
            );
            if ($used >= limits::max_daily_reviews($activity)) {
                throw new \moodle_exception('dailylimitreached', 'masterypractice');
            }
            throw new \moodle_exception('noquestionsavailable', 'masterypractice');
        }

        require_once($CFG->dirroot . '/question/engine/lib.php');
        require_once($CFG->dirroot . '/question/type/questionbase.php');

        $context = \context_module::instance($cm->id);
        $quba = \question_engine::make_questions_usage_by_activity('mod_masterypractice', $context);
        $quba->set_preferred_behaviour('deferredfeedback');

        $slots = [];
        foreach ($selected as $candidate) {
            $question = \question_bank::load_question((int) $candidate->questionid);
            if (!$question instanceof \question_automatically_gradable) {
                continue;
            }
            $slot = $quba->add_question($question, 1.0);
            $slots[$slot] = $candidate;
        }

        if (count($slots) < (int) $activity->minquestions) {
            throw new \moodle_exception('noquestionsavailable', 'masterypractice');
        }

        $quba->start_all_questions(null, $now, $userid);
        \question_engine::save_questions_usage_by_activity($quba);

        $session = (object) [
            'masterypracticeid' => $activity->id,
            'userid' => $userid,
            'qubaid' => $quba->get_id(),
            'state' => 'inprogress',
            'questioncount' => count($slots),
            'answeredcount' => 0,
            'correctcount' => 0,
            'score' => 0.0,
            'masteryafter' => 0.0,
            'timestarted' => $now,
            'timecompleted' => 0,
            'recommendednext' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $session->id = $DB->insert_record('masterypractice_sessions', $session);

        foreach ($slots as $slot => $candidate) {
            $DB->insert_record('masterypractice_squestions', (object) [
                'sessionid' => $session->id,
                'slot' => $slot,
                'questionid' => $candidate->questionid,
                'entryid' => $candidate->entryid,
                'primaryconceptid' => $candidate->primaryconceptid,
                'fraction' => null,
                'difficulty' => $candidate->difficulty,
                'responsetime' => 0,
                'nextreview' => 0,
                'timecreated' => $now,
                'timemodified' => $now,
            ]);
        }

        $event = \mod_masterypractice\event\practice_session_started::create([
            'objectid' => $session->id,
            'context' => $context,
            'relateduserid' => $userid,
            'other' => ['masterypracticeid' => (int) $activity->id],
        ]);
        $event->add_record_snapshot('masterypractice_sessions', $session);
        $event->trigger();

        return $session;
    }

    /**
     * Finishes a session using native Question Engine response processing.
     *
     * @param \stdClass $activity Activity.
     * @param \stdClass $cm Course module.
     * @param int $sessionid Session id.
     * @param int $userid User id.
     * @param int|null $now Timestamp.
     * @return \stdClass Completed session.
     */
    public static function finish(
        \stdClass $activity,
        \stdClass $cm,
        int $sessionid,
        int $userid,
        array $responsetimes = [],
        ?int $now = null
    ): \stdClass {
        global $CFG, $DB;

        $now = $now ?? time();
        $transaction = $DB->start_delegated_transaction();

        $session = $DB->get_record('masterypractice_sessions', [
            'id' => $sessionid,
            'masterypracticeid' => $activity->id,
            'userid' => $userid,
        ], '*', MUST_EXIST);

        if ($session->state === 'completed') {
            $transaction->allow_commit();
            return $session;
        }

        require_once($CFG->dirroot . '/question/engine/lib.php');
        $quba = \question_engine::load_questions_usage_by_activity((int) $session->qubaid);
        $quba->process_all_actions($now);
        $quba->finish_all_questions($now);
        \question_engine::save_questions_usage_by_activity($quba);

        $context = \context_module::instance($cm->id);
        $rows = $DB->get_records('masterypractice_squestions', ['sessionid' => $session->id], 'slot ASC');
        $scoretotal = 0.0;
        $answered = 0;
        $correct = 0;
        $aggregateddeltas = [];

        foreach ($rows as $row) {
            $qa = $quba->get_question_attempt((int) $row->slot);
            $fraction = $qa->get_fraction();
            $fraction = $fraction === null ? 0.0 : max(0.0, min(1.0, (float) $fraction));
            $lastaction = (int) $qa->get_last_action_time();
            $fallbacktime = max(0, $lastaction - (int) $session->timestarted);
            $clienttime = max(0, (int) ($responsetimes[$row->slot] ?? 0));
            $responsetime = $clienttime > 0 ? $clienttime : $fallbacktime;

            $row->fraction = $fraction;
            $row->responsetime = $responsetime;
            $row->timemodified = $now;

            $deltas = state_manager::apply_question(
                $activity,
                $userid,
                $row,
                $fraction,
                $responsetime,
                $now,
                $context
            );
            foreach ($deltas as $conceptid => $delta) {
                if (!isset($aggregateddeltas[$conceptid])) {
                    $aggregateddeltas[$conceptid] = ['masterydelta' => 0.0, 'confidencedelta' => 0.0];
                }
                $aggregateddeltas[$conceptid]['masterydelta'] += $delta['masterydelta'];
                $aggregateddeltas[$conceptid]['confidencedelta'] += $delta['confidencedelta'];
            }

            $qstate = $DB->get_record('masterypractice_qstate', [
                'masterypracticeid' => $activity->id,
                'userid' => $userid,
                'entryid' => $row->entryid,
            ]);
            $row->nextreview = $qstate ? (int) $qstate->nextreview : 0;
            $DB->update_record('masterypractice_squestions', $row);

            $scoretotal += $fraction;
            $answered++;
            if ($fraction >= 0.8) {
                $correct++;
            }
        }

        $session->state = 'completed';
        $session->answeredcount = $answered;
        $session->correctcount = $correct;
        $session->score = $answered ? round(100.0 * $scoretotal / $answered, 2) : 0.0;
        $session->timecompleted = $now;
        $session->recommendednext = (int) $DB->get_field_sql(
            "SELECT COALESCE(MIN(nextreview), 0)
               FROM {masterypractice_qstate}
              WHERE masterypracticeid = :activityid
                AND userid = :userid
                AND nextreview > 0",
            ['activityid' => $activity->id, 'userid' => $userid]
        );
        $session->timemodified = $now;
        $DB->update_record('masterypractice_sessions', $session);

        summary_manager::snapshot($activity, $userid, $session->id, $aggregateddeltas, $now);
        $summary = summary_manager::refresh_user($activity, $userid, $now);
        $session->masteryafter = (float) $summary->overallmastery;
        $DB->set_field(
            'masterypractice_sessions',
            'masteryafter',
            $session->masteryafter,
            ['id' => $session->id]
        );

        $event = \mod_masterypractice\event\practice_session_completed::create([
            'objectid' => $session->id,
            'context' => $context,
            'relateduserid' => $userid,
            'other' => [
                'masterypracticeid' => (int) $activity->id,
                'score' => (float) $session->score,
                'questioncount' => $answered,
            ],
        ]);
        $event->add_record_snapshot('masterypractice_sessions', $session);
        $event->trigger();

        $transaction->allow_commit();
        return $session;
    }

    /**
     * Loads a learner-owned session.
     *
     * @param int $sessionid Session id.
     * @param int $activityid Activity id.
     * @param int $userid User id.
     * @return \stdClass
     */
    public static function get(int $sessionid, int $activityid, int $userid): \stdClass {
        global $DB;

        return $DB->get_record('masterypractice_sessions', [
            'id' => $sessionid,
            'masterypracticeid' => $activityid,
            'userid' => $userid,
        ], '*', MUST_EXIST);
    }
}
