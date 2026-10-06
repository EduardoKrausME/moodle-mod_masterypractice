<?php
namespace mod_masterypractice\local\completion;
defined('MOODLE_INTERNAL') || die();

final class evaluator {
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
