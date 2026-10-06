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
 * teacher.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT);
$conceptid = optional_param('concept', 0, PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'masterypractice');
$activity = $DB->get_record('masterypractice', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/masterypractice:viewreports', $context);

$PAGE->set_url('/mod/masterypractice/teacher.php', ['id' => $cm->id, 'concept' => $conceptid ?: null]);
$PAGE->set_title(get_string('teacherdashboard', 'masterypractice'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$concepts = \mod_masterypractice\concept_repository::get_all((int) $activity->id);
$conceptmap = [];
foreach ($concepts as $concept) {
    $conceptmap[$concept->id] = $concept;
}

$summaries = $DB->get_records(
    'masterypractice_csummary',
    ['masterypracticeid' => $activity->id],
    'avgmastery ASC'
);
if (!$summaries && $DB->record_exists('masterypractice_cstate', ['masterypracticeid' => $activity->id])) {
    \mod_masterypractice\class_summary_manager::rebuild_activity((int) $activity->id);
    $summaries = $DB->get_records(
        'masterypractice_csummary',
        ['masterypracticeid' => $activity->id],
        'avgmastery ASC'
    );
}

$summarydata = [];
$lastupdate = 0;
foreach ($summaries as $summary) {
    if (!isset($conceptmap[$summary->conceptid])) {
        continue;
    }
    $concept = $conceptmap[$summary->conceptid];
    $lastupdate = max($lastupdate, (int) $summary->timemodified);
    $summarydata[] = [
        'label' => \mod_masterypractice\concept_repository::label($concept),
        'critical' => !empty($concept->critical),
        'mastery' => round((float) $summary->avgmastery),
        'confidence' => round((float) $summary->avgconfidence),
        'usercount' => (int) $summary->usercount,
        'overduecount' => (int) $summary->overduecount,
        'lowcount' => (int) $summary->lowcount,
        'detailsurl' => (new moodle_url('/mod/masterypractice/teacher.php', [
            'id' => $cm->id,
            'concept' => $concept->id,
        ]))->out(false),
    ];
}

$detaildata = [];
$detaillabel = '';
if ($conceptid) {
    $concept = $conceptmap[$conceptid] ?? null;
    if ($concept) {
        $detaillabel = \mod_masterypractice\concept_repository::label($concept);
        $sql = "SELECT cs.*, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename
                  FROM {masterypractice_cstate} cs
                  JOIN {user} u ON u.id = cs.userid
                 WHERE cs.masterypracticeid = :activityid
                   AND cs.conceptid = :conceptid
                   AND u.deleted = 0
              ORDER BY cs.mastery ASC, cs.confidence ASC";
        $records = $DB->get_records_sql($sql, [
            'activityid' => $activity->id,
            'conceptid' => $conceptid,
        ], 0, 250);

        foreach ($records as $record) {
            $detaildata[] = [
                'fullname' => fullname($record),
                'mastery' => round((float) $record->mastery),
                'confidence' => round((float) $record->confidence),
                'nextreview' => !empty($record->nextreview)
                    ? ((int) $record->nextreview <= time()
                        ? get_string('practicenow', 'masterypractice')
                        : userdate((int) $record->nextreview, get_string('strftimedatetimeshort', 'langconfig')))
                    : '-',
            ];
        }
    }
}

[$enrolledsql, $enrolledparams] = get_enrolled_sql($context, 'mod/masterypractice:attempt', 0, true);
$params = $enrolledparams + [
    'activityid' => $activity->id,
    'now' => time(),
    'minfailures' => 3,
    'declinesince' => time() - 30 * DAYSECS,
    'declinethreshold' => -10,
];
$sql = "SELECT u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
               u.middlename, u.alternatename,
               COALESCE(us.overallmastery, 0) AS overallmastery,
               COALESCE(us.sessionscompleted, 0) AS sessionscompleted,
               COALESCE(us.nextreview, 0) AS nextreview,
               COALESCE((
                   SELECT MAX(qs.failurestreak)
                     FROM {masterypractice_qstate} qs
                    WHERE qs.masterypracticeid = :activityid
                      AND qs.userid = u.id
               ), 0) AS maxfailure,
               COALESCE((
                   SELECT MIN(h.masterydelta)
                     FROM {masterypractice_history} h
                    WHERE h.masterypracticeid = :activityid4
                      AND h.userid = u.id
                      AND h.timecreated >= :declinesince
               ), 0) AS worstdecline
          FROM {user} u
          JOIN ({$enrolledsql}) eu ON eu.id = u.id
     LEFT JOIN {masterypractice_usummary} us
            ON us.masterypracticeid = :activityid2
           AND us.userid = u.id
         WHERE u.deleted = 0
           AND (
               us.id IS NULL
               OR us.overallmastery < 50
               OR (us.nextreview > 0 AND us.nextreview <= :now)
               OR us.sessionscompleted < 2
               OR EXISTS (
                   SELECT 1
                     FROM {masterypractice_qstate} qs2
                    WHERE qs2.masterypracticeid = :activityid3
                      AND qs2.userid = u.id
                      AND qs2.failurestreak >= :minfailures
               )
               OR EXISTS (
                   SELECT 1
                     FROM {masterypractice_history} h2
                    WHERE h2.masterypracticeid = :activityid5
                      AND h2.userid = u.id
                      AND h2.timecreated >= :declinesince2
                      AND h2.masterydelta <= :declinethreshold
               )
           )
      ORDER BY COALESCE(us.overallmastery, 0) ASC, u.lastname ASC, u.firstname ASC";
$params['activityid2'] = $activity->id;
$params['activityid3'] = $activity->id;
$params['activityid4'] = $activity->id;
$params['activityid5'] = $activity->id;
$params['declinesince2'] = $params['declinesince'];

$attentionrecords = $DB->get_records_sql($sql, $params, 0, 250);
$attention = [];
$now = time();
foreach ($attentionrecords as $record) {
    $indicators = [];
    if ((int) $record->sessionscompleted === 0) {
        $indicators[] = get_string('indicator_noparticipation', 'masterypractice');
    } else if ((int) $record->sessionscompleted < 2) {
        $indicators[] = get_string('indicator_lowparticipation', 'masterypractice');
    }
    if ((float) $record->overallmastery < 50 && (int) $record->sessionscompleted > 0) {
        $indicators[] = get_string('indicator_lowmastery', 'masterypractice');
    }
    if ((int) $record->nextreview > 0 && (int) $record->nextreview <= $now) {
        $indicators[] = get_string('indicator_overdue', 'masterypractice');
    }
    if ((int) $record->maxfailure >= 3) {
        $indicators[] = get_string('indicator_failures', 'masterypractice');
    }
    if ((float) $record->worstdecline <= -10) {
        $indicators[] = get_string('indicator_decline', 'masterypractice');
    }

    $attention[] = [
        'fullname' => fullname($record),
        'mastery' => round((float) $record->overallmastery),
        'indicators' => implode(', ', array_unique($indicators)),
    ];
}

$data = [
    'summaries' => array_values($summarydata),
    'hassummaries' => !empty($summarydata),
    'lastupdate' => $lastupdate
        ? userdate($lastupdate, get_string('strftimedatetimeshort', 'langconfig'))
        : '',
    'detail' => array_values($detaildata),
    'hasdetail' => !empty($detaildata),
    'detaillabel' => $detaillabel,
    'attention' => array_values($attention),
    'hasattention' => !empty($attention),
];

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('teacherdashboard', 'masterypractice'));
echo $OUTPUT->render_from_template('mod_masterypractice/teacher_dashboard', $data);
echo $OUTPUT->footer();
