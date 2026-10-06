<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

/**
 * Indicates which features are supported.
 *
 * @param string $feature Feature constant.
 * @return mixed
 */
function masterypractice_supports(string $feature) {
    return match ($feature) {
        FEATURE_MOD_ARCHETYPE => MOD_ARCHETYPE_OTHER,
        FEATURE_GROUPS,
        FEATURE_GROUPINGS,
        FEATURE_MOD_INTRO,
        FEATURE_SHOW_DESCRIPTION,
        FEATURE_COMPLETION_TRACKS_VIEWS,
        FEATURE_COMPLETION_HAS_RULES,
        FEATURE_GRADE_HAS_GRADE,
        FEATURE_BACKUP_MOODLE2 => true,
        FEATURE_MOD_PURPOSE => MOD_PURPOSE_ASSESSMENT,
        default => null,
    };
}

/**
 * Adds a Mastery Practice activity.
 *
 * @param stdClass $data Submitted form data.
 * @param mod_masterypractice_mod_form|null $mform Form instance.
 * @return int New instance id.
 */
function masterypractice_add_instance(stdClass $data, ?mod_masterypractice_mod_form $mform = null): int {
    global $DB;

    $now = time();
    $data->timecreated = $now;
    $data->timemodified = $now;
    $id = $DB->insert_record('masterypractice', $data);

    $data->id = $id;
    masterypractice_grade_item_update($data);
    return $id;
}

/**
 * Updates a Mastery Practice activity.
 *
 * @param stdClass $data Submitted form data.
 * @param mod_masterypractice_mod_form|null $mform Form instance.
 * @return bool
 */
function masterypractice_update_instance(stdClass $data, ?mod_masterypractice_mod_form $mform = null): bool {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();
    $result = $DB->update_record('masterypractice', $data);
    masterypractice_grade_item_update($data);
    masterypractice_update_grades($data);
    return $result;
}

/**
 * Deletes an activity and all data owned by it.
 *
 * @param int $id Instance id.
 * @return bool
 */
function masterypractice_delete_instance(int $id): bool {
    global $DB;

    $activity = $DB->get_record('masterypractice', ['id' => $id]);
    if (!$activity) {
        return false;
    }

    \mod_masterypractice\local\data_manager::delete_activity_data($id);
    $DB->delete_records('masterypractice_csummary', ['masterypracticeid' => $id]);
    $DB->delete_records('masterypractice_qstats', ['masterypracticeid' => $id]);
    $DB->delete_records('masterypractice_concepts', ['masterypracticeid' => $id]);
    $DB->delete_records('masterypractice', ['id' => $id]);

    masterypractice_grade_item_delete($activity);
    return true;
}

/**
 * Marks the activity as viewed.
 *
 * @param stdClass $activity Activity record.
 * @param stdClass $course Course record.
 * @param cm_info|stdClass $cm Course module.
 * @param stdClass|null $context Unused compatibility parameter.
 * @return void
 */
function masterypractice_view(stdClass $activity, stdClass $course, $cm, ?stdClass $context = null): void {
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

/**
 * Supplies cached course-module information including custom completion rules.
 *
 * @param stdClass $coursemodule Course module record.
 * @return cached_cm_info|false
 */
function masterypractice_get_coursemodule_info(stdClass $coursemodule) {
    global $DB;

    $activity = $DB->get_record(
        'masterypractice',
        ['id' => $coursemodule->instance],
        'id,name,intro,introformat,completionsessions,completionquestions,completionmastery,completioncritical'
    );
    if (!$activity) {
        return false;
    }

    $info = new cached_cm_info();
    $info->name = $activity->name;
    if ($coursemodule->showdescription) {
        $info->content = format_module_intro('masterypractice', $activity, $coursemodule->id, false);
    }

    if ((int) $coursemodule->completion === COMPLETION_TRACKING_AUTOMATIC) {
        $info->customdata['customcompletionrules'] = [
            'completionsessions' => (int) $activity->completionsessions,
            'completionquestions' => (int) $activity->completionquestions,
            'completionmastery' => (int) $activity->completionmastery,
            'completioncritical' => (int) $activity->completioncritical,
        ];
    }

    return $info;
}

/**
 * Returns the state of custom completion rules.
 *
 * @param stdClass $course Course.
 * @param cm_info|stdClass $cm Course module.
 * @param int $userid User id.
 * @param bool $type COMPLETION_AND/OR represented as a boolean.
 * @return bool
 */
function masterypractice_get_completion_state($course, $cm, int $userid, bool $type): bool {
    global $DB;

    $activity = $DB->get_record('masterypractice', ['id' => $cm->instance], '*', MUST_EXIST);
    return \mod_masterypractice\local\completion\evaluator::evaluate_user($activity, $userid, $type);
}

/**
 * Describes enabled custom completion rules.
 *
 * @param cm_info|stdClass $cm Course module information.
 * @return string[]
 */
function masterypractice_get_completion_active_rule_descriptions($cm): array {
    $rules = $cm->customdata['customcompletionrules'] ?? [];
    $descriptions = [];

    if (!empty($rules['completionsessions'])) {
        $descriptions[] = get_string('completiondetail:sessions', 'masterypractice', $rules['completionsessions']);
    }
    if (!empty($rules['completionquestions'])) {
        $descriptions[] = get_string('completiondetail:questions', 'masterypractice', $rules['completionquestions']);
    }
    if (!empty($rules['completionmastery'])) {
        $descriptions[] = get_string('completiondetail:mastery', 'masterypractice', $rules['completionmastery']);
    }
    if (!empty($rules['completioncritical'])) {
        $descriptions[] = get_string('completiondetail:critical', 'masterypractice');
    }

    return $descriptions;
}

/**
 * Creates or updates the grade item.
 *
 * @param stdClass $activity Activity.
 * @param array|null $grades Optional grades.
 * @return int Gradebook status.
 */
function masterypractice_grade_item_update(stdClass $activity, ?array $grades = null): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $params = [
        'itemname' => $activity->name,
        'idnumber' => $activity->cmidnumber ?? null,
    ];

    if (($activity->gradepolicy ?? 'none') === 'none') {
        $params['gradetype'] = GRADE_TYPE_NONE;
    } else {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = (float) ($activity->grade ?? 100);
        $params['grademin'] = 0;
    }

    return grade_update(
        'mod/masterypractice',
        $activity->course,
        'mod',
        'masterypractice',
        $activity->id,
        0,
        $grades,
        $params
    );
}

/**
 * Deletes the gradebook item.
 *
 * @param stdClass $activity Activity.
 * @return int Gradebook status.
 */
function masterypractice_grade_item_delete(stdClass $activity): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update(
        'mod/masterypractice',
        $activity->course,
        'mod',
        'masterypractice',
        $activity->id,
        0,
        null,
        ['deleted' => 1]
    );
}

/**
 * Updates gradebook grades from persisted summaries.
 *
 * @param stdClass $activity Activity.
 * @param int $userid User id, or 0 for all users.
 * @param bool $nullifnone Whether to send null when no summary exists.
 * @return void
 */
function masterypractice_update_grades(stdClass $activity, int $userid = 0, bool $nullifnone = true): void {
    global $DB;

    if (($activity->gradepolicy ?? 'none') === 'none') {
        masterypractice_grade_item_update($activity);
        return;
    }

    $params = ['masterypracticeid' => $activity->id];
    if ($userid) {
        $params['userid'] = $userid;
    }

    $summaries = $DB->get_records('masterypractice_usummary', $params);
    $grades = [];
    foreach ($summaries as $summary) {
        $percent = match ($activity->gradepolicy) {
            'best' => (float) $summary->bestsession,
            'average' => (float) $summary->avgsessionscore,
            'mastery' => (float) $summary->overallmastery,
            default => 0.0,
        };
        $grades[$summary->userid] = (object) [
            'userid' => $summary->userid,
            'rawgrade' => $percent * ((float) $activity->grade / 100.0),
        ];
    }

    if ($userid && empty($grades) && $nullifnone) {
        $grades[$userid] = (object) ['userid' => $userid, 'rawgrade' => null];
    }

    masterypractice_grade_item_update($activity, $grades ?: null);
}

/**
 * Adds reset-course controls.
 *
 * @param MoodleQuickForm $mform Form.
 * @return void
 */
function masterypractice_reset_course_form_definition(&$mform): void {
    $mform->addElement('header', 'masterypracticeheader', get_string('modulenameplural', 'masterypractice'));
    $mform->addElement('checkbox', 'reset_masterypractice', get_string('resetuserdata', 'masterypractice'));
}

/**
 * Returns reset-course default values.
 *
 * @param stdClass $course Course.
 * @return array
 */
function masterypractice_reset_course_form_defaults(stdClass $course): array {
    return ['reset_masterypractice' => 1];
}

/**
 * Removes learner state while retaining activity configuration.
 *
 * @param stdClass $data Reset-course data.
 * @return array Reset status.
 */
function masterypractice_reset_userdata(stdClass $data): array {
    global $DB;

    if (empty($data->reset_masterypractice)) {
        return [];
    }

    $instances = $DB->get_records('masterypractice', ['course' => $data->courseid], '', 'id');
    foreach ($instances as $instance) {
        \mod_masterypractice\local\data_manager::delete_activity_data((int) $instance->id);
    }

    return [[
        'component' => get_string('modulenameplural', 'masterypractice'),
        'item' => get_string('resetuserdata', 'masterypractice'),
        'error' => false,
    ]];
}

/**
 * Serves files embedded in the activity introduction.
 *
 * Question response/content files are served by the native Question Engine;
 * this callback owns only the module's own intro file area.
 *
 * @param stdClass $course Course.
 * @param stdClass $cm Course module.
 * @param context $context File context.
 * @param string $filearea File area.
 * @param array $args Remaining path arguments.
 * @param bool $forcedownload Whether download is forced.
 * @param array $options Send-file options.
 * @return bool
 */
function masterypractice_pluginfile(
    stdClass $course,
    stdClass $cm,
    context $context,
    string $filearea,
    array $args,
    bool $forcedownload,
    array $options = []
): bool {
    if ($context->contextlevel !== CONTEXT_MODULE || $filearea !== 'intro') {
        return false;
    }

    require_login($course, true, $cm);
    require_capability('mod/masterypractice:view', $context);

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/{$context->id}/mod_masterypractice/intro/0/{$relativepath}";
    $file = $fs->get_file_by_hash(sha1($fullpath));

    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
    return true;
}
