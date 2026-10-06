<?php
namespace mod_masterypractice\local;
defined('MOODLE_INTERNAL') || die();

final class class_summary_manager {
    public static function rebuild_activity(int $activityid, ?int $now = null): void {
        global $DB;
        $now = $now ?? time();

        foreach ($DB->get_records('masterypractice_concepts', ['masterypracticeid' => $activityid]) as $concept) {
            $aggregate = $DB->get_record_sql(
                "SELECT COUNT(id) AS usercount,
                        COALESCE(AVG(mastery), 0) AS avgmastery,
                        COALESCE(AVG(confidence), 0) AS avgconfidence,
                        COALESCE(SUM(CASE WHEN nextreview > 0 AND nextreview <= :now THEN 1 ELSE 0 END), 0) AS overduecount,
                        COALESCE(SUM(CASE WHEN mastery < 50 THEN 1 ELSE 0 END), 0) AS lowcount
                   FROM {masterypractice_cstate}
                  WHERE masterypracticeid = :activityid
                    AND conceptid = :conceptid",
                ['now' => $now, 'activityid' => $activityid, 'conceptid' => $concept->id]
            );

            $summary = $DB->get_record('masterypractice_csummary', [
                'masterypracticeid' => $activityid,
                'conceptid' => $concept->id,
            ]) ?: (object) [
                'masterypracticeid' => $activityid,
                'conceptid' => $concept->id,
            ];

            $summary->usercount = (int) ($aggregate->usercount ?? 0);
            $summary->avgmastery = round((float) ($aggregate->avgmastery ?? 0), 2);
            $summary->avgconfidence = round((float) ($aggregate->avgconfidence ?? 0), 2);
            $summary->overduecount = (int) ($aggregate->overduecount ?? 0);
            $summary->lowcount = (int) ($aggregate->lowcount ?? 0);
            $summary->timemodified = $now;

            if (empty($summary->id)) {
                $DB->insert_record('masterypractice_csummary', $summary);
            } else {
                $DB->update_record('masterypractice_csummary', $summary);
            }
        }
    }
}
