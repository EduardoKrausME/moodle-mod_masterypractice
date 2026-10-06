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
 * summary_manager.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice;

/**
 * Class summary_manager.
 */
final class summary_manager {
    /**
     * Method refresh_user.
     *
     * @param \stdClass $activity Parameter activity.
     * @param int $userid Parameter userid.
     * @param int $now Parameter now.
     * @return \stdClass Return value.
     */
    public static function refresh_user(\stdClass $activity, int $userid, int $now): \stdClass {
        global $DB;

        $domain = $DB->get_record_sql(
            "SELECT
                COALESCE(SUM(c.weight * COALESCE(s.mastery, 0)) / NULLIF(SUM(c.weight), 0), 0) AS mastery,
                COALESCE(SUM(c.weight * COALESCE(s.confidence, 0)) / NULLIF(SUM(c.weight), 0), 0) AS confidence
               FROM {masterypractice_concepts} c
          LEFT JOIN {masterypractice_cstate} s
                 ON s.conceptid = c.id
                AND s.masterypracticeid = c.masterypracticeid
                AND s.userid = :userid
              WHERE c.masterypracticeid = :activityid",
            ['userid' => $userid, 'activityid' => $activity->id]
        );

        $sessions = $DB->get_record_sql(
            "SELECT COUNT(id) AS sessioncount,
                    COALESCE(SUM(answeredcount), 0) AS questioncount,
                    COALESCE(MAX(score), 0) AS bestscore,
                    COALESCE(AVG(score), 0) AS avgscore,
                    COALESCE(MAX(timecompleted), 0) AS lastsession
               FROM {masterypractice_sessions}
              WHERE masterypracticeid = :activityid
                AND userid = :userid
                AND state = :state",
            ['activityid' => $activity->id, 'userid' => $userid, 'state' => 'completed']
        );

        $nextreview = (int) $DB->get_field_sql(
            "SELECT COALESCE(MIN(nextreview), 0)
               FROM {masterypractice_qstate}
              WHERE masterypracticeid = :activityid
                AND userid = :userid
                AND nextreview > 0",
            ['activityid' => $activity->id, 'userid' => $userid]
        );

        $summary = $DB->get_record('masterypractice_usummary', [
            'masterypracticeid' => $activity->id,
            'userid' => $userid,
        ]) ?: (object) [
            'masterypracticeid' => $activity->id,
            'userid' => $userid,
            'lastnotified' => 0,
        ];

        $summary->sessionscompleted = (int) ($sessions->sessioncount ?? 0);
        $summary->questionsanswered = (int) ($sessions->questioncount ?? 0);
        $summary->overallmastery = round((float) ($domain->mastery ?? 0), 2);
        $summary->overallconfidence = round((float) ($domain->confidence ?? 0), 2);
        $summary->lastsession = (int) ($sessions->lastsession ?? 0);
        $summary->nextreview = $nextreview;
        $summary->bestsession = round((float) ($sessions->bestscore ?? 0), 2);
        $summary->avgsessionscore = round((float) ($sessions->avgscore ?? 0), 2);
        $summary->timemodified = $now;

        if (empty($summary->id)) {
            $summary->id = $DB->insert_record('masterypractice_usummary', $summary);
        } else {
            $DB->update_record('masterypractice_usummary', $summary);
        }

        \masterypractice_update_grades($activity, $userid);

        $cm = get_coursemodule_from_instance('masterypractice', $activity->id, $activity->course, false, IGNORE_MISSING);
        if ($cm) {
            (new \completion_info(get_course($activity->course)))->update_state($cm, COMPLETION_UNKNOWN, $userid);
        }

        return $summary;
    }

    /**
     * Method snapshot.
     *
     * @param \stdClass $activity Parameter activity.
     * @param int $userid Parameter userid.
     * @param int $sessionid Parameter sessionid.
     * @param array $deltas Parameter deltas.
     * @param int $now Parameter now.
     * @return void Return value.
     */
    public static function snapshot(
        \stdClass $activity,
        int $userid,
        int $sessionid,
        array $deltas,
        int $now
    ): void {
        global $DB;
        foreach ($deltas as $conceptid => $delta) {
            $state = $DB->get_record('masterypractice_cstate', [
                'masterypracticeid' => $activity->id,
                'userid' => $userid,
                'conceptid' => $conceptid,
            ]);
            if (!$state) {
                continue;
            }
            $DB->insert_record('masterypractice_history', (object) [
                'masterypracticeid' => $activity->id,
                'userid' => $userid,
                'conceptid' => $conceptid,
                'sessionid' => $sessionid,
                'mastery' => $state->mastery,
                'confidence' => $state->confidence,
                'masterydelta' => $delta['masterydelta'],
                'confidencedelta' => $delta['confidencedelta'],
                'timecreated' => $now,
            ]);
        }
    }
}
