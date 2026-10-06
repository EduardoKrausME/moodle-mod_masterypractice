<?php
require_once('../../config.php');

$id = required_param('id', PARAM_INT);
$course = get_course($id);
require_course_login($course);

$coursecontext = context_course::instance($course->id);
$PAGE->set_url('/mod/masterypractice/index.php', ['id' => $course->id]);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('modulenameplural', 'masterypractice'));
$PAGE->set_heading($course->fullname);

$instances = get_all_instances_in_course('masterypractice', $course);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'masterypractice'));

$table = new html_table();
$table->head = [get_string('name'), get_string('description')];
foreach ($instances as $instance) {
    if (empty($instance->visible) && !has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
        continue;
    }
    $link = html_writer::link(
        new moodle_url('/mod/masterypractice/view.php', ['id' => $instance->coursemodule]),
        format_string($instance->name)
    );
    $table->data[] = [$link, format_module_intro('masterypractice', $instance, $instance->coursemodule)];
}
echo html_writer::table($table);
echo $OUTPUT->footer();
