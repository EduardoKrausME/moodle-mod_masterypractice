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
 * view.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT);
[$course, $cm] = get_course_and_cm_from_cmid($id, 'masterypractice');
$activity = $DB->get_record('masterypractice', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/masterypractice:view', $context);

$PAGE->set_url('/mod/masterypractice/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($activity->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

masterypractice_view($activity, $course, $cm);

echo $OUTPUT->header();

if (trim((string) $activity->intro) !== '') {
    echo $OUTPUT->box(format_module_intro('masterypractice', $activity, $cm->id), 'generalbox mod_introbox');
}

$actions = [];
if (has_capability('mod/masterypractice:manageconcepts', $context)) {
    $actions[] = html_writer::link(
        new moodle_url('/mod/masterypractice/concepts.php', ['id' => $cm->id]),
        get_string('manageconcepts', 'masterypractice'),
        ['class' => 'btn btn-secondary mr-2']
    );
}
if (has_capability('mod/masterypractice:viewreports', $context)) {
    $actions[] = html_writer::link(
        new moodle_url('/mod/masterypractice/teacher.php', ['id' => $cm->id]),
        get_string('teacherdashboard', 'masterypractice'),
        ['class' => 'btn btn-secondary']
    );
}
if ($actions) {
    echo html_writer::div(implode('', $actions), 'mb-4');
}

if (has_capability('mod/masterypractice:attempt', $context)) {
    $concepts = \mod_masterypractice\local\concept_repository::get_all((int) $activity->id);
    $states = $DB->get_records('masterypractice_cstate', [
        'masterypracticeid' => $activity->id,
        'userid' => $USER->id,
    ]);
    $statebyconcept = [];
    foreach ($states as $state) {
        $statebyconcept[$state->conceptid] = $state;
    }

    $summary = $DB->get_record('masterypractice_usummary', [
        'masterypracticeid' => $activity->id,
        'userid' => $USER->id,
    ]);
    if (!$summary && $concepts) {
        $summary = \mod_masterypractice\local\summary_manager::refresh_user($activity, $USER->id, time());
    }

    $now = time();
    $conceptdata = [];
    foreach ($concepts as $concept) {
        $state = $statebyconcept[$concept->id] ?? null;
        $mastery = $state ? (float) $state->mastery : 0.0;
        $confidence = $state ? (float) $state->confidence : 0.0;
        $lastreview = $state ? (int) $state->lastreview : 0;
        $currentmastery = \mod_masterypractice\local\mastery\decay::estimate(
            $mastery,
            $lastreview,
            $now,
            (int) $activity->decayhalflifedays
        );
        $currentconfidence = \mod_masterypractice\local\mastery\decay::estimate_confidence(
            $confidence,
            $lastreview,
            $now,
            (int) $activity->decayhalflifedays
        );

        if (!$state || (int) $state->attempts === 0) {
            $statekey = 'unassessed';
            $badge = 'badge-secondary';
        } else if ((int) $state->nextreview > 0 && (int) $state->nextreview <= $now) {
            $statekey = 'overdue';
            $badge = 'badge-danger';
        } else if ($currentmastery >= 85 && $currentconfidence >= 60) {
            $statekey = 'mastered';
            $badge = 'badge-success';
        } else if ($currentmastery < 50) {
            $statekey = 'practice';
            $badge = 'badge-warning';
        } else {
            $statekey = 'progress';
            $badge = 'badge-info';
        }

        $conceptdata[] = [
            'label' => \mod_masterypractice\local\concept_repository::label($concept),
            'critical' => !empty($concept->critical),
            'currentmastery' => round($currentmastery),
            'currentconfidence' => round($currentconfidence),
            'statelabel' => get_string('state_' . $statekey, 'masterypractice'),
            'badgeclass' => $badge,
            'nextreview' => $state && !empty($state->nextreview)
                ? ((int) $state->nextreview <= $now
                    ? get_string('practicenow', 'masterypractice')
                    : userdate((int) $state->nextreview, get_string('strftimedatetimeshort', 'langconfig')))
                : '',
        ];
    }

    $inprogress = $DB->get_record('masterypractice_sessions', [
        'masterypracticeid' => $activity->id,
        'userid' => $USER->id,
        'state' => 'inprogress',
    ]);

    $historyrecords = $DB->get_records(
        'masterypractice_history',
        ['masterypracticeid' => $activity->id, 'userid' => $USER->id],
        'timecreated DESC',
        '*',
        0,
        40
    );
    $labels = \mod_masterypractice\local\concept_repository::labels((int) $activity->id);
    $history = [];
    foreach (array_reverse(array_values($historyrecords)) as $record) {
        $history[] = [
            'label' => $labels[$record->conceptid] ?? '#' . $record->conceptid,
            'mastery' => round((float) $record->mastery),
            'confidence' => round((float) $record->confidence),
            'date' => userdate((int) $record->timecreated, get_string('strftimedatetimeshort', 'langconfig')),
        ];
    }

    $completed = $DB->get_records(
        'masterypractice_sessions',
        [
            'masterypracticeid' => $activity->id,
            'userid' => $USER->id,
            'state' => 'completed',
        ],
        'timecompleted DESC',
        'id,timecompleted,masteryafter',
        0,
        30
    );
    $charthtml = '';
    if ($completed) {
        $completed = array_reverse(array_values($completed));
        $chart = new \core\chart_line();
        $chart->set_title(get_string('evolution', 'masterypractice'));
        $chart->set_labels(array_map(
            static fn($item) => userdate(
                (int) $item->timecompleted,
                get_string('strftimedateshort', 'langconfig')
            ),
            $completed
        ));
        $chart->add_series(new \core\chart_series(
            get_string('persistedmastery', 'masterypractice'),
            array_map(static fn($item) => round((float) $item->masteryafter, 2), $completed)
        ));
        $yaxis = $chart->get_yaxis(0, true);
        $yaxis->set_min(0);
        $yaxis->set_max(100);
        $charthtml = $OUTPUT->render_chart($chart, false);
    }

    $data = [
        'questioncount' => (int) $activity->questionspersession,
        'estimatedminutes' => (int) $activity->estimatedminutes,
        'concepts' => $conceptdata,
        'history' => $history,
        'hashistory' => !empty($history),
        'charthtml' => $charthtml,
        'haschart' => $charthtml !== '',
        'starturl' => (new moodle_url('/mod/masterypractice/session.php', [
            'id' => $cm->id,
            'action' => 'start',
            'sesskey' => sesskey(),
        ]))->out(false),
        'inprogressurl' => $inprogress
            ? (new moodle_url('/mod/masterypractice/session.php', [
                'id' => $cm->id,
                'session' => $inprogress->id,
            ]))->out(false)
            : '',
    ];

    echo $OUTPUT->render_from_template('mod_masterypractice/student_dashboard', $data);
} else if (!has_capability('mod/masterypractice:viewreports', $context)) {
    echo $OUTPUT->notification(get_string('nopermissions', 'error'), 'notifyproblem');
}

echo $OUTPUT->footer();
