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
 * state_manager.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\local\mastery;

use mod_masterypractice\local\concept_repository;
use mod_masterypractice\local\limits;
use mod_masterypractice\local\scheduler\factory;
use mod_masterypractice\local\scheduler\review_input;

/**
 * Applies Question Engine evidence to persisted question and concept state.
 */
final class state_manager {
    /** Mastery threshold used for the mastery-level-reached event. */
    private const MASTERED_THRESHOLD = 85.0;

    /**
     * Applies one completed question.
     *
     * @param \stdClass $activity Activity.
     * @param int $userid User id.
     * @param \stdClass $sessionquestion Session-question row.
     * @param float $fraction Moodle question fraction.
     * @param int $responsetime Auxiliary response duration.
     * @param int $now Timestamp.
     * @param \context_module $context Module context.
     * @return array<int, array> Deltas keyed by concept id.
     */
    public static function apply_question(
        \stdClass $activity,
        int $userid,
        \stdClass $sessionquestion,
        float $fraction,
        int $responsetime,
        int $now,
        \context_module $context
    ): array {
        global $DB;

        $fraction = max(0.0, min(1.0, $fraction));
        [$mininterval, $maxinterval] = limits::intervals($activity);
        $scheduler = factory::create((string) $activity->scheduler);
        $concepts = concept_repository::matching_concepts((int) $activity->id, (int) $sessionquestion->questionid);
        if (!$concepts && !empty($sessionquestion->primaryconceptid)) {
            $fallback = $DB->get_record('masterypractice_concepts', [
                'id' => $sessionquestion->primaryconceptid,
                'masterypracticeid' => $activity->id,
            ]);
            if ($fallback) {
                $concepts = [$fallback];
            }
        }

        $qstate = $DB->get_record('masterypractice_qstate', [
            'masterypracticeid' => $activity->id,
            'userid' => $userid,
            'entryid' => $sessionquestion->entryid,
        ]);
        if (!$qstate) {
            $qstate = self::new_question_state($activity, $userid, $sessionquestion, $now);
        }
        $firstdifficultyobservation = (int) $qstate->reviewcount === 0;

        $states = [];
        foreach ($concepts as $concept) {
            $state = $DB->get_record('masterypractice_cstate', [
                'masterypracticeid' => $activity->id,
                'userid' => $userid,
                'conceptid' => $concept->id,
            ]);
            $states[$concept->id] = $state ?: self::new_concept_state($activity, $userid, $concept, $now);
        }

        // Keep the pre-review item state immutable while concept evidence is
        // calculated. SM-2 repetitions must advance exactly once per presented
        // question, even when that question contributes to several concepts.
        $originalqstate = clone $qstate;

        $primaryid = (int) ($sessionquestion->primaryconceptid ?: ($concepts[0]->id ?? 0));
        $primarystate = $states[$primaryid] ?? reset($states) ?: null;
        if ($primarystate) {
            $iteminput = new review_input(
                $fraction,
                (float) $sessionquestion->difficulty,
                $now,
                (int) $qstate->lastreview,
                $mininterval,
                $maxinterval,
                (float) $primarystate->mastery,
                (float) $primarystate->confidence,
                (int) $qstate->successstreak,
                (int) $qstate->failurestreak,
                $originalqstate
            );
            $itemresult = $scheduler->review($iteminput);
            self::save_question_state($qstate, $sessionquestion, $fraction, $itemresult, $now);
        }

        $deltas = [];
        foreach ($concepts as $concept) {
            $state = $states[$concept->id];
            $oldmastery = (float) $state->mastery;
            $oldconfidence = (float) $state->confidence;

            $input = new review_input(
                $fraction,
                (float) $sessionquestion->difficulty,
                $now,
                (int) $state->lastreview,
                $mininterval,
                $maxinterval,
                $oldmastery,
                $oldconfidence,
                (int) $state->successstreak,
                (int) $state->failurestreak,
                $originalqstate
            );
            $result = $scheduler->review($input);

            $state->mastery = self::bound_percent($oldmastery + $result->masterydelta);
            $state->confidence = self::bound_percent($oldconfidence + $result->confidencedelta);
            $state->attempts = (int) $state->attempts + 1;
            if ($input->is_success()) {
                $state->correctattempts = (int) $state->correctattempts + 1;
                $state->successstreak = (int) $state->successstreak + 1;
                $state->failurestreak = 0;
            } else {
                $state->incorrectattempts = (int) $state->incorrectattempts + 1;
                $state->successstreak = 0;
                $state->failurestreak = (int) $state->failurestreak + 1;
            }
            $oldcount = max(0, (int) $state->attempts - 1);
            $state->avgresponsetime = $oldcount === 0
                ? $responsetime
                : (((float) $state->avgresponsetime * $oldcount) + $responsetime) / ($oldcount + 1);
            $state->lastreview = $now;
            $state->nextreview = $result->nextreview;
            $state->peakmastery = max((float) $state->peakmastery, (float) $state->mastery);
            $state->timemodified = $now;

            if (empty($state->id)) {
                $state->id = $DB->insert_record('masterypractice_cstate', $state);
            } else {
                $DB->update_record('masterypractice_cstate', $state);
            }

            $actualmasterydelta = (float) $state->mastery - $oldmastery;
            $actualconfidencedelta = (float) $state->confidence - $oldconfidence;
            $deltas[$concept->id] = [
                'masterydelta' => $actualmasterydelta,
                'confidencedelta' => $actualconfidencedelta,
                'mastery' => (float) $state->mastery,
                'confidence' => (float) $state->confidence,
                'nextreview' => (int) $state->nextreview,
            ];

            if ($oldmastery < self::MASTERED_THRESHOLD
                    && (float) $state->mastery >= self::MASTERED_THRESHOLD) {
                $event = \mod_masterypractice\event\mastery_level_reached::create([
                    'objectid' => $concept->id,
                    'context' => $context,
                    'relateduserid' => $userid,
                    'other' => [
                        'masterypracticeid' => (int) $activity->id,
                        'mastery' => (float) $state->mastery,
                    ],
                ]);
                $event->trigger();
            }
        }

        if ($firstdifficultyobservation) {
            self::update_question_statistics(
                (int) $activity->id,
                (int) $sessionquestion->entryid,
                $fraction,
                $now
            );
        }

        return $deltas;
    }

    /**
     * Creates an unsaved question state.
     *
     * @param \stdClass $activity Activity.
     * @param int $userid User id.
     * @param \stdClass $sessionquestion Session question.
     * @param int $now Timestamp.
     * @return \stdClass
     */
    private static function new_question_state(
        \stdClass $activity,
        int $userid,
        \stdClass $sessionquestion,
        int $now
    ): \stdClass {
        return (object) [
            'id' => 0,
            'masterypracticeid' => $activity->id,
            'userid' => $userid,
            'entryid' => $sessionquestion->entryid,
            'lastquestionid' => 0,
            'lastreview' => 0,
            'nextreview' => 0,
            'reviewcount' => 0,
            'successstreak' => 0,
            'failurestreak' => 0,
            'leitnerbox' => 1,
            'sm2repetitions' => 0,
            'sm2interval' => 0,
            'sm2easiness' => 2.5,
            'lastfraction' => null,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
    }

    /**
     * Creates an unsaved concept state.
     *
     * @param \stdClass $activity Activity.
     * @param int $userid User id.
     * @param \stdClass $concept Concept.
     * @param int $now Timestamp.
     * @return \stdClass
     */
    private static function new_concept_state(
        \stdClass $activity,
        int $userid,
        \stdClass $concept,
        int $now
    ): \stdClass {
        return (object) [
            'id' => 0,
            'masterypracticeid' => $activity->id,
            'userid' => $userid,
            'conceptid' => $concept->id,
            'mastery' => 0.0,
            'confidence' => 0.0,
            'attempts' => 0,
            'correctattempts' => 0,
            'incorrectattempts' => 0,
            'lastreview' => 0,
            'nextreview' => 0,
            'successstreak' => 0,
            'failurestreak' => 0,
            'avgresponsetime' => 0.0,
            'peakmastery' => 0.0,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
    }

    /**
     * Persists per-question scheduling state.
     *
     * @param \stdClass $state State.
     * @param \stdClass $sessionquestion Session question.
     * @param float $fraction Fraction.
     * @param \mod_masterypractice\local\scheduler\review_result $result Strategy result.
     * @param int $now Timestamp.
     * @return void
     */
    private static function save_question_state(
        \stdClass $state,
        \stdClass $sessionquestion,
        float $fraction,
        \mod_masterypractice\local\scheduler\review_result $result,
        int $now
    ): void {
        global $DB;

        $state->lastquestionid = $sessionquestion->questionid;
        $state->lastreview = $now;
        $state->nextreview = $result->nextreview;
        $state->reviewcount = (int) $state->reviewcount + 1;
        $state->lastfraction = $fraction;
        $state->timemodified = $now;

        foreach ($result->itemfields as $field => $value) {
            if (property_exists($state, $field)) {
                $state->{$field} = $value;
            }
        }

        if (empty($state->id)) {
            $state->id = $DB->insert_record('masterypractice_qstate', $state);
        } else {
            $DB->update_record('masterypractice_qstate', $state);
        }
    }

    /**
     * Incrementally updates aggregate difficulty evidence.
     *
     * @param int $activityid Activity id.
     * @param int $entryid Entry id.
     * @param float $fraction Fraction.
     * @param int $now Timestamp.
     * @return void
     */
    private static function update_question_statistics(
        int $activityid,
        int $entryid,
        float $fraction,
        int $now
    ): void {
        global $DB;

        $stats = $DB->get_record('masterypractice_qstats', [
            'masterypracticeid' => $activityid,
            'entryid' => $entryid,
        ]);
        if (!$stats) {
            $stats = (object) [
                'masterypracticeid' => $activityid,
                'entryid' => $entryid,
                'attempts' => 0,
                'totalfraction' => 0.0,
                'difficulty' => 0.5,
                'timemodified' => $now,
            ];
        }

        $stats->attempts = (int) $stats->attempts + 1;
        $stats->totalfraction = (float) $stats->totalfraction + $fraction;
        $minimum = max(1, (int) get_config('masterypractice', 'difficultysamples'));
        $stats->difficulty = difficulty_estimator::estimate(
            (int) $stats->attempts,
            (float) $stats->totalfraction,
            $minimum
        );
        $stats->timemodified = $now;

        if (empty($stats->id)) {
            $DB->insert_record('masterypractice_qstats', $stats);
        } else {
            $DB->update_record('masterypractice_qstats', $stats);
        }
    }

    /**
     * Bounds a percentage.
     *
     * @param float $value Value.
     * @return float
     */
    private static function bound_percent(float $value): float {
        return round(max(0.0, min(100.0, $value)), 2);
    }
}
