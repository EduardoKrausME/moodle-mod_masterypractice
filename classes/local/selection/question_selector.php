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
 * question_selector.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\local\selection;

use mod_masterypractice\local\concept_repository;
use mod_masterypractice\local\limits;
use mod_masterypractice\local\question_repository;

/**
 * Adaptive question selector.
 */
final class question_selector {
    /**
     * Selects questions for a new practice session.
     *
     * @param \stdClass $activity Activity.
     * @param int $userid User id.
     * @param int $now Timestamp.
     * @return \stdClass[]
     */
    public static function select(\stdClass $activity, int $userid, int $now): array {
        global $DB;

        $concepts = concept_repository::get_all((int) $activity->id);
        if (!$concepts) {
            return [];
        }
        $conceptmap = [];
        foreach ($concepts as $concept) {
            $conceptmap[$concept->id] = $concept;
        }

        $candidates = question_repository::candidates($activity, $userid, $now);
        if (!$candidates) {
            return [];
        }

        $entryids = array_keys($candidates);
        [$entrysql, $entryparams] = $DB->get_in_or_equal($entryids, SQL_PARAMS_NAMED, 'entry');
        $entryparams['activityid'] = $activity->id;
        $entryparams['userid'] = $userid;
        $qstates = $DB->get_records_select(
            'masterypractice_qstate',
            "masterypracticeid = :activityid AND userid = :userid AND entryid {$entrysql}",
            $entryparams,
            '',
            '*',
            0,
            0
        );
        $qstatebyentry = [];
        foreach ($qstates as $state) {
            $qstatebyentry[$state->entryid] = $state;
        }

        $cstates = $DB->get_records(
            'masterypractice_cstate',
            ['masterypracticeid' => $activity->id, 'userid' => $userid]
        );
        $cstatebyconcept = [];
        foreach ($cstates as $state) {
            $cstatebyconcept[$state->conceptid] = $state;
        }

        $stats = $DB->get_records_select(
            'masterypractice_qstats',
            "masterypracticeid = :activityid AND entryid {$entrysql}",
            ['activityid' => $activity->id] + array_filter(
                $entryparams,
                static fn($key) => str_starts_with($key, 'entry'),
                ARRAY_FILTER_USE_KEY
            )
        );
        $difficulty = [];
        foreach ($stats as $stat) {
            $difficulty[$stat->entryid] = (float) $stat->difficulty;
        }

        [$effectivemininterval] = limits::intervals($activity);

        $scored = [];
        foreach ($candidates as $candidate) {
            $bestconceptid = 0;
            $bestpriority = -INF;
            foreach ($candidate->conceptids as $conceptid) {
                if (!isset($conceptmap[$conceptid])) {
                    continue;
                }
                $priority = self::concept_priority(
                    $conceptmap[$conceptid],
                    $cstatebyconcept[$conceptid] ?? null,
                    $now
                );
                if ($priority > $bestpriority) {
                    $bestpriority = $priority;
                    $bestconceptid = (int) $conceptid;
                }
            }
            if (!$bestconceptid) {
                continue;
            }

            $state = $qstatebyentry[$candidate->entryid] ?? null;
            $due = $state && self::is_due((int) $state->nextreview, $now);
            $unseen = !$state || (int) $state->reviewcount === 0;

            $score = $bestpriority;
            $score += $due ? 4.0 : 0.0;
            $score += $unseen ? 2.5 : 0.0;
            if ($state && !$due && (int) $state->lastreview > $now - $effectivemininterval) {
                $score -= 100.0;
            } else if ($state && (int) $state->lastreview > $now - DAYSECS) {
                $score -= 2.0;
            }

            $candidate->primaryconceptid = $bestconceptid;
            $candidate->difficulty = $difficulty[$candidate->entryid] ?? 0.5;
            $candidate->basescore = $score;
            $candidate->tiebreak = self::tie_break($userid, (int) $candidate->entryid, $now);
            $scored[] = $candidate;
        }

        if (!$scored) {
            return [];
        }

        $remaining = self::remaining_daily_reviews($activity, $userid, $now);
        if ($remaining < (int) $activity->minquestions) {
            return [];
        }

        $target = min(
            (int) $activity->questionspersession,
            (int) $activity->maxquestions,
            $remaining,
            count($scored)
        );
        if ($target < (int) $activity->minquestions) {
            return [];
        }

        if (empty($activity->mixconcepts)) {
            usort($scored, [self::class, 'compare_candidates']);
            $focus = (int) $scored[0]->primaryconceptid;
            $focused = array_values(array_filter(
                $scored,
                static fn($candidate) => (int) $candidate->primaryconceptid === $focus
            ));
            if (count($focused) >= min((int) $activity->minquestions, $target)) {
                $scored = $focused;
                $target = min($target, count($scored));
            }
        }

        $selected = [];
        $conceptcounts = [];
        while (count($selected) < $target && $scored) {
            foreach ($scored as $candidate) {
                $count = $conceptcounts[$candidate->primaryconceptid] ?? 0;
                $candidate->score = $candidate->basescore - 1.25 * $count;
            }
            usort($scored, [self::class, 'compare_candidates']);
            $pick = array_shift($scored);
            $selected[] = $pick;
            $conceptcounts[$pick->primaryconceptid] = ($conceptcounts[$pick->primaryconceptid] ?? 0) + 1;
        }

        return $selected;
    }

    /**
     * Pure concept priority calculation, exposed for deterministic tests.
     *
     * @param \stdClass $concept Configured concept.
     * @param \stdClass|null $state Learner concept state.
     * @param int $now Timestamp.
     * @return float
     */
    public static function concept_priority(\stdClass $concept, ?\stdClass $state, int $now): float {
        $mastery = $state ? (float) $state->mastery : 0.0;
        $confidence = $state ? (float) $state->confidence : 0.0;
        $attempts = $state ? (int) $state->attempts : 0;
        $due = $state && self::is_due((int) $state->nextreview, $now);

        $priority = max(0.01, (float) $concept->weight);
        $priority *= 1.0
            + 2.2 * (1.0 - $mastery / 100.0)
            + 1.2 * (1.0 - $confidence / 100.0);
        if ($due) {
            $priority += 2.0;
        }
        if (!empty($concept->critical)) {
            $priority += 1.25;
        }
        if ($attempts === 0) {
            $priority += 1.5;
        }

        return $priority;
    }

    /**
     * Whether a scheduled question/concept is due.
     *
     * @param int $nextreview Next-review timestamp.
     * @param int $now Current timestamp.
     * @return bool
     */
    public static function is_due(int $nextreview, int $now): bool {
        return $nextreview > 0 && $nextreview <= $now;
    }

    /**
     * Remaining daily question reviews.
     *
     * @param \stdClass $activity Activity.
     * @param int $userid User id.
     * @param int $now Timestamp.
     * @return int
     */
    private static function remaining_daily_reviews(\stdClass $activity, int $userid, int $now): int {
        global $DB;

        $daystart = usergetmidnight($now);
        $sql = "SELECT COUNT(sq.id)
                  FROM {masterypractice_squestions} sq
                  JOIN {masterypractice_sessions} s ON s.id = sq.sessionid
                 WHERE s.masterypracticeid = :activityid
                   AND s.userid = :userid
                   AND s.state = :state
                   AND s.timecompleted >= :daystart";
        $used = (int) $DB->count_records_sql($sql, [
            'activityid' => $activity->id,
            'userid' => $userid,
            'state' => 'completed',
            'daystart' => $daystart,
        ]);

        return max(0, limits::max_daily_reviews($activity) - $used);
    }

    /**
     * Stable non-random tie break.
     *
     * @param int $userid User id.
     * @param int $entryid Entry id.
     * @param int $now Timestamp.
     * @return float
     */
    private static function tie_break(int $userid, int $entryid, int $now): float {
        $seed = $userid . ':' . $entryid . ':' . (int) floor($now / HOURSECS);
        return ((int) sprintf('%u', crc32($seed))) / 4294967295;
    }

    /**
     * Sorts candidates descending by effective score and deterministic tie break.
     *
     * @param \stdClass $a Candidate.
     * @param \stdClass $b Candidate.
     * @return int
     */
    private static function compare_candidates(\stdClass $a, \stdClass $b): int {
        $ascore = (float) ($a->score ?? $a->basescore);
        $bscore = (float) ($b->score ?? $b->basescore);
        if (abs($ascore - $bscore) > 0.000001) {
            return $ascore < $bscore ? 1 : -1;
        }
        return $a->tiebreak < $b->tiebreak ? 1 : -1;
    }
}
