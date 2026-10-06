<?php
require_once('../../config.php');

$id = required_param('id', PARAM_INT);
$sessionid = required_param('session', PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'masterypractice');
$activity = $DB->get_record('masterypractice', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/masterypractice:attempt', $context);

$session = \mod_masterypractice\local\session\service::get(
    $sessionid,
    (int) $activity->id,
    (int) $USER->id
);
if ($session->state !== 'completed') {
    redirect(new moodle_url('/mod/masterypractice/session.php', [
        'id' => $cm->id,
        'session' => $session->id,
    ]));
}

$PAGE->set_url('/mod/masterypractice/summary.php', ['id' => $cm->id, 'session' => $session->id]);
$PAGE->set_title(get_string('sessioncompleted', 'masterypractice'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$labels = \mod_masterypractice\local\concept_repository::labels((int) $activity->id);
$history = $DB->get_records('masterypractice_history', ['sessionid' => $session->id], 'id ASC');
$strengthened = [];
$needsreview = [];
foreach ($history as $record) {
    $item = [
        'label' => $labels[$record->conceptid] ?? '#' . $record->conceptid,
        'mastery' => round((float) $record->mastery),
    ];
    if ((float) $record->masterydelta > 0.05) {
        $strengthened[] = $item;
    }
    if ((float) $record->mastery < 60 || (float) $record->masterydelta < 0) {
        $needsreview[] = $item;
    }
}

$data = [
    'correctcount' => (int) $session->correctcount,
    'questioncount' => (int) $session->answeredcount,
    'score' => round((float) $session->score),
    'strengthened' => $strengthened,
    'needsreview' => $needsreview,
    'recommendednext' => $session->recommendednext
        ? ((int) $session->recommendednext <= time()
            ? get_string('practicenow', 'masterypractice')
            : userdate((int) $session->recommendednext, get_string('strftimedatetimeshort', 'langconfig')))
        : '-',
    'backurl' => (new moodle_url('/mod/masterypractice/view.php', ['id' => $cm->id]))->out(false),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_masterypractice/session_summary', $data);
echo $OUTPUT->footer();
