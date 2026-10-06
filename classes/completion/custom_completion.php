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
 * custom_completion.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace mod_masterypractice\completion;

use core_completion\activity_custom_completion;

/**
 * Custom completion rules for Mastery Practice.
 */
final class custom_completion extends activity_custom_completion {
    /**
     * Returns the state of one custom rule.
     *
     * Completion deliberately uses persisted achieved mastery, never the
     * decayed "current mastery" estimate shown by the learner dashboard.
     *
     * @param string $rule Completion rule.
     * @return int
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);
        $activity = $DB->get_record(
            'masterypractice',
            ['id' => $this->cm->instance],
            '*',
            MUST_EXIST
        );
        $summary = $DB->get_record('masterypractice_usummary', [
            'masterypracticeid' => $activity->id,
            'userid' => $this->userid,
        ]);

        $complete = match ($rule) {
            'completionsessions' => !empty($activity->completionsessions)
                && (int) ($summary->sessionscompleted ?? 0) >= (int) $activity->completionsessions,
            'completionquestions' => !empty($activity->completionquestions)
                && (int) ($summary->questionsanswered ?? 0) >= (int) $activity->completionquestions,
            'completionmastery' => !empty($activity->completionmastery)
                && (float) ($summary->overallmastery ?? 0) >= (float) $activity->completionmastery,
            'completioncritical' => !empty($activity->completioncritical)
                && self::critical_concepts_complete($activity, $this->userid),
            default => false,
        };

        return $complete ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Rules defined by this module.
     *
     * @return string[]
     */
    public static function get_defined_custom_rules(): array {
        return [
            'completionsessions',
            'completionquestions',
            'completionmastery',
            'completioncritical',
        ];
    }

    /**
     * Human-readable rule descriptions.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        $rules = $this->cm->customdata['customcompletionrules'] ?? [];

        return [
            'completionsessions' => get_string(
                'completiondetail:sessions',
                'masterypractice',
                (int) ($rules['completionsessions'] ?? 0)
            ),
            'completionquestions' => get_string(
                'completiondetail:questions',
                'masterypractice',
                (int) ($rules['completionquestions'] ?? 0)
            ),
            'completionmastery' => get_string(
                'completiondetail:mastery',
                'masterypractice',
                (int) ($rules['completionmastery'] ?? 0)
            ),
            'completioncritical' => get_string('completiondetail:critical', 'masterypractice'),
        ];
    }

    /**
     * Sort order in Moodle's completion UI.
     *
     * @return string[]
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionsessions',
            'completionquestions',
            'completionmastery',
            'completioncritical',
            'completionusegrade',
            'completionpassgrade',
        ];
    }

    /**
     * Tests all critical concept thresholds.
     *
     * @param \stdClass $activity Activity.
     * @param int $userid User id.
     * @return bool
     */
    private static function critical_concepts_complete(\stdClass $activity, int $userid): bool {
        global $DB;

        $critical = $DB->get_records('masterypractice_concepts', [
            'masterypracticeid' => $activity->id,
            'critical' => 1,
        ]);
        if (!$critical) {
            return true;
        }

        foreach ($critical as $concept) {
            $mastery = $DB->get_field('masterypractice_cstate', 'mastery', [
                'masterypracticeid' => $activity->id,
                'userid' => $userid,
                'conceptid' => $concept->id,
            ]);
            if ($mastery === false || (float) $mastery < (float) $concept->criticalthreshold) {
                return false;
            }
        }

        return true;
    }
}
