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
 * session.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT);
$sessionid = optional_param('session', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'masterypractice');
$activity = $DB->get_record('masterypractice', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/masterypractice:attempt', $context);

if ($action === 'start') {
    require_sesskey();
    $session = \mod_masterypractice\local\session\service::start($activity, $cm, $USER->id);
    redirect(new moodle_url('/mod/masterypractice/session.php', [
        'id' => $cm->id,
        'session' => $session->id,
    ]));
}

if (!$sessionid) {
    redirect(new moodle_url('/mod/masterypractice/view.php', ['id' => $cm->id]));
}

$session = \mod_masterypractice\local\session\service::get(
    $sessionid,
    (int) $activity->id,
    (int) $USER->id
);

if ($session->state === 'completed') {
    redirect(new moodle_url('/mod/masterypractice/summary.php', [
        'id' => $cm->id,
        'session' => $session->id,
    ]));
}

if (data_submitted() && optional_param('finish', 0, PARAM_BOOL)) {
    require_sesskey();
    $responsetimes = optional_param_array('masterytime', [], PARAM_INT);
    \mod_masterypractice\local\session\service::finish(
        $activity,
        $cm,
        (int) $session->id,
        (int) $USER->id,
        $responsetimes
    );
    redirect(new moodle_url('/mod/masterypractice/summary.php', [
        'id' => $cm->id,
        'session' => $session->id,
    ]));
}

require_once($CFG->dirroot . '/question/engine/lib.php');
$quba = question_engine::load_questions_usage_by_activity((int) $session->qubaid);

$PAGE->set_url('/mod/masterypractice/session.php', ['id' => $cm->id, 'session' => $session->id]);
$PAGE->set_title(format_string($activity->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->js_call_amd('mod_masterypractice/session', 'init');

$options = new question_display_options();
$options->marks = question_display_options::HIDDEN;
$options->correctness = question_display_options::HIDDEN;
$options->feedback = question_display_options::HIDDEN;
$options->generalfeedback = question_display_options::HIDDEN;
$options->rightanswer = question_display_options::HIDDEN;
$options->history = question_display_options::HIDDEN;
$options->flags = question_display_options::HIDDEN;

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('todaypractice', 'masterypractice'));

echo html_writer::start_tag('form', [
    'method' => 'post',
    'action' => (new moodle_url('/mod/masterypractice/session.php'))->out(false),
    'class' => 'masterypractice-session',
]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'session', 'value' => $session->id]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'finish', 'value' => '1']);

foreach ($quba->get_slots() as $slot) {
    echo html_writer::start_div('masterypractice-question', ['data-mastery-slot' => $slot]);
    echo $quba->render_question($slot, $options, $slot);
    echo html_writer::end_div();
}

echo html_writer::tag(
    'button',
    get_string('finishpractice', 'masterypractice'),
    ['type' => 'submit', 'class' => 'btn btn-primary btn-lg']
);
echo html_writer::end_tag('form');

echo $OUTPUT->footer();
