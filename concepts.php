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
 * concepts.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT);
$editid = optional_param('edit', 0, PARAM_INT);
$deleteid = optional_param('delete', 0, PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'masterypractice');
$activity = $DB->get_record('masterypractice', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/masterypractice:manageconcepts', $context);

$PAGE->set_url('/mod/masterypractice/concepts.php', ['id' => $cm->id]);
$PAGE->set_title(get_string('manageconcepts', 'masterypractice'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

if ($deleteid) {
    require_sesskey();
    $concept = $DB->get_record('masterypractice_concepts', [
        'id' => $deleteid, 'masterypracticeid' => $activity->id,
    ], '*', MUST_EXIST);
    \mod_masterypractice\local\data_manager::delete_concept_data((int) $concept->id);
    $DB->delete_records('masterypractice_concepts', ['id' => $concept->id]);
    $DB->delete_records('masterypractice_usummary', ['masterypracticeid' => $activity->id]);
    redirect(new moodle_url('/mod/masterypractice/concepts.php', ['id' => $cm->id]));
}

$editing = $editid ? $DB->get_record('masterypractice_concepts', [
    'id' => $editid, 'masterypracticeid' => $activity->id,
], '*', MUST_EXIST) : null;

$form = new \mod_masterypractice\form\concept_form(
    new moodle_url('/mod/masterypractice/concepts.php', ['id' => $cm->id, 'edit' => $editid ?: null]),
    ['courseid' => $course->id]
);

if ($editing) {
    $formdata = clone $editing;
    $formdata->conceptid = $editing->id;
    $formdata->categoryid = $editing->sourcetype === 'category' ? $editing->sourceid : 0;
    $formdata->tagid = $editing->sourcetype === 'tag' ? $editing->sourceid : 0;
    $form->set_data($formdata);
}

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/masterypractice/concepts.php', ['id' => $cm->id]));
} else if ($data = $form->get_data()) {
    $sourceid = $data->sourcetype === 'tag' ? (int) $data->tagid : (int) $data->categoryid;
    $duplicate = $DB->get_record('masterypractice_concepts', [
        'masterypracticeid' => $activity->id,
        'sourcetype' => $data->sourcetype,
        'sourceid' => $sourceid,
    ]);
    if ($duplicate && (int) $duplicate->id !== (int) $data->conceptid) {
        redirect(new moodle_url('/mod/masterypractice/concepts.php', ['id' => $cm->id]),
            get_string('duplicateconcept', 'masterypractice'), null,
            \core\output\notification::NOTIFY_ERROR);
    }

    $now = time();
    if (!empty($data->conceptid)) {
        $record = $DB->get_record('masterypractice_concepts', [
            'id' => $data->conceptid, 'masterypracticeid' => $activity->id,
        ], '*', MUST_EXIST);
        $sourcechanged = $record->sourcetype !== $data->sourcetype || (int) $record->sourceid !== $sourceid;
        $record->sourcetype = $data->sourcetype;
        $record->sourceid = $sourceid;
        $record->includesubcategories = !empty($data->includesubcategories) ? 1 : 0;
        $record->weight = (float) $data->weight;
        $record->critical = !empty($data->critical) ? 1 : 0;
        $record->criticalthreshold = (float) $data->criticalthreshold;
        $record->timemodified = $now;
        $DB->update_record('masterypractice_concepts', $record);
        if ($sourcechanged) {
            \mod_masterypractice\local\data_manager::delete_concept_data((int) $record->id);
        }
    } else {
        $sortorder = (int) $DB->get_field_sql(
            'SELECT COALESCE(MAX(sortorder), 0) FROM {masterypractice_concepts} WHERE masterypracticeid = :id',
            ['id' => $activity->id]
        ) + 1;
        $DB->insert_record('masterypractice_concepts', (object) [
            'masterypracticeid' => $activity->id,
            'sourcetype' => $data->sourcetype,
            'sourceid' => $sourceid,
            'includesubcategories' => !empty($data->includesubcategories) ? 1 : 0,
            'weight' => (float) $data->weight,
            'critical' => !empty($data->critical) ? 1 : 0,
            'criticalthreshold' => (float) $data->criticalthreshold,
            'sortorder' => $sortorder,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
    }
    $DB->delete_records('masterypractice_usummary', ['masterypracticeid' => $activity->id]);
    redirect(new moodle_url('/mod/masterypractice/concepts.php', ['id' => $cm->id]));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageconcepts', 'masterypractice'));

$concepts = \mod_masterypractice\local\concept_repository::get_all((int) $activity->id);
if ($concepts) {
    $table = new html_table();
    $table->head = [
        get_string('conceptsource', 'masterypractice'),
        get_string('concepttype', 'masterypractice'),
        get_string('conceptweight', 'masterypractice'),
        get_string('criticalconcept', 'masterypractice'),
        get_string('actions'),
    ];
    foreach ($concepts as $concept) {
        $table->data[] = [
            \mod_masterypractice\local\concept_repository::label($concept),
            get_string('concepttype_' . $concept->sourcetype, 'masterypractice'),
            format_float((float) $concept->weight, 2),
            $concept->critical ? get_string('yes') : get_string('no'),
            html_writer::link(new moodle_url('/mod/masterypractice/concepts.php', [
                'id' => $cm->id, 'edit' => $concept->id,
            ]), get_string('edit')) . ' · ' .
            html_writer::link(new moodle_url('/mod/masterypractice/concepts.php', [
                'id' => $cm->id, 'delete' => $concept->id, 'sesskey' => sesskey(),
            ]), get_string('delete')),
        ];
    }
    echo html_writer::table($table);
} else {
    echo $OUTPUT->notification(get_string('noconcepts', 'masterypractice'), 'notifyinfo');
}

echo $OUTPUT->heading($editing ? get_string('editconcept', 'masterypractice')
    : get_string('addconcept', 'masterypractice'), 3);
$form->display();
echo $OUTPUT->footer();
