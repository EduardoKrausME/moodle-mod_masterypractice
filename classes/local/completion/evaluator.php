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
 * evaluator.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\local\completion;

/**
 * Class evaluator.
 */
final class evaluator {
    /**
     * Method evaluate_user.
     *
     * @param \stdClass $activity Parameter activity.
     * @param int $userid Parameter userid.
     * @param bool $type Parameter type.
     * @return bool Return value.
     */
    public static function evaluate_user(\stdClass $activity, int $userid, bool $type): bool {
        global $DB;
        $summary = $DB->get_record('masterypractice_usummary', [
            'masterypracticeid' => $activity->id,
            'userid' => $userid,
        ]);
        $criticalok = true;
        if (!empty($activity->completioncritical)) {
            foreach ($DB->get_records('masterypractice_concepts', [
                'masterypracticeid' => $activity->id,
                'critical' => 1,
            ]) as $concept) {
                $mastery = $DB->get_field('masterypractice_cstate', 'mastery', [
                    'masterypracticeid' => $activity->id,
                    'userid' => $userid,
                    'conceptid' => $concept->id,
                ]);
                if ($mastery === false || (float) $mastery < (float) $concept->criticalthreshold) {
                    $criticalok = false;
                    break;
                }
            }
        }
        return self::evaluate_values([
            'sessions' => $summary ? (int) $summary->sessionscompleted : 0,
            'questions' => $summary ? (int) $summary->questionsanswered : 0,
            'mastery' => $summary ? (float) $summary->overallmastery : 0.0,
            'criticalok' => $criticalok,
        ], [
            'sessions' => (int) $activity->completionsessions,
            'questions' => (int) $activity->completionquestions,
            'mastery' => (int) $activity->completionmastery,
            'critical' => !empty($activity->completioncritical),
        ], $type);
    }

    /**
     * Method evaluate_values.
     *
     * @param array $values Parameter values.
     * @param array $rules Parameter rules.
     * @param bool $type Parameter type.
     * @return bool Return value.
     */
    public static function evaluate_values(array $values, array $rules, bool $type): bool {
        $checks = [];
        if (!empty($rules['sessions'])) {
            $checks[] = (int) $values['sessions'] >= (int) $rules['sessions'];
        }
        if (!empty($rules['questions'])) {
            $checks[] = (int) $values['questions'] >= (int) $rules['questions'];
        }
        if (!empty($rules['mastery'])) {
            $checks[] = (float) $values['mastery'] >= (float) $rules['mastery'];
        }
        if (!empty($rules['critical'])) {
            $checks[] = !empty($values['criticalok']);
        }
        if (!$checks) {
            return $type;
        }
        return $type ? !in_array(false, $checks, true) : in_array(true, $checks, true);
    }
}
