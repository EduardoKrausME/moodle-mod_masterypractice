<?php
namespace mod_masterypractice\local;

defined('MOODLE_INTERNAL') || die();

use core_question\local\bank\question_version_status;

final class question_repository {
    private const WINDOW_SIZE = 250;

    public static function candidates(\stdClass $activity, int $userid, int $now): array {
        global $DB;

        $concepts = concept_repository::get_all((int) $activity->id);
        if (!$concepts) {
            return [];
        }

        $pool = [];
        foreach ($concepts as $concept) {
            foreach (self::concept_window($activity, $concept, $userid, $now) as $question) {
                $entryid = (int) $question->entryid;
                if (!isset($pool[$entryid])) {
                    $question->conceptids = [];
                    $pool[$entryid] = $question;
                }
                $pool[$entryid]->conceptids[(int) $concept->id] = (int) $concept->id;
            }
        }

        $due = $DB->get_records_select(
            'masterypractice_qstate',
            'masterypracticeid = :activityid AND userid = :userid AND nextreview > 0 AND nextreview <= :now',
            ['activityid' => $activity->id, 'userid' => $userid, 'now' => $now],
            'nextreview ASC',
            'entryid',
            0,
            150
        );
        if ($due) {
            foreach (self::latest_by_entries(array_map('intval', array_keys($due))) as $question) {
                $entryid = (int) $question->entryid;
                if (!isset($pool[$entryid])) {
                    $question->conceptids = [];
                    $pool[$entryid] = $question;
                }
                foreach (concept_repository::matching_concepts(
                    (int) $activity->id,
                    (int) $question->questionid
                ) as $concept) {
                    $pool[$entryid]->conceptids[(int) $concept->id] = (int) $concept->id;
                }
            }
        }

        foreach ($pool as $entryid => $question) {
            $question->conceptids = array_values($question->conceptids);
            if (!$question->conceptids) {
                unset($pool[$entryid]);
            }
        }
        return $pool;
    }

    private static function concept_window(
        \stdClass $activity,
        \stdClass $concept,
        int $userid,
        int $now
    ): array {
        global $DB;

        $contextids = concept_repository::course_context_ids((int) $activity->course);
        if (!$contextids) {
            return [];
        }

        $params = [
            'ready' => question_version_status::QUESTION_STATUS_READY,
            'ready2' => question_version_status::QUESTION_STATUS_READY,
        ];

        if ($concept->sourcetype === 'category') {
            [$sourcesql, $sourceparams] = $DB->get_in_or_equal(
                concept_repository::category_ids($concept),
                SQL_PARAMS_NAMED,
                'cat'
            );
            $params += $sourceparams;
            $join = '';
            $where = "qbe.questioncategoryid {$sourcesql}";
        } else {
            [$contextsql, $contextparams] = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'ctx');
            $params += $contextparams;
            $params += [
                'tagcomponent' => 'core_question',
                'tagitemtype' => 'question',
                'tagid' => $concept->sourceid,
            ];
            $join = "JOIN {tag_instance} ti
                       ON ti.itemid = q.id
                      AND ti.component = :tagcomponent
                      AND ti.itemtype = :tagitemtype
                      AND ti.tagid = :tagid
                     JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid";
            $where = "qc.contextid {$contextsql}";
        }

        $from = "{question_bank_entries} qbe
                JOIN {question_versions} qv
                  ON qv.questionbankentryid = qbe.id
                 AND qv.status = :ready
                 AND qv.version = (
                     SELECT MAX(qv2.version)
                       FROM {question_versions} qv2
                      WHERE qv2.questionbankentryid = qbe.id
                        AND qv2.status = :ready2
                 )
                JOIN {question} q ON q.id = qv.questionid
                {$join}";

        $count = (int) $DB->count_records_sql(
            "SELECT COUNT(DISTINCT qbe.id) FROM {$from} WHERE {$where}",
            $params
        );
        if ($count === 0) {
            return [];
        }

        $maxoffset = max(0, $count - self::WINDOW_SIZE);
        $seed = $activity->id . ':' . $userid . ':' . $concept->id . ':' . (int) floor($now / DAYSECS);
        $unsigned = (int) sprintf('%u', crc32($seed));
        $offset = $maxoffset > 0 ? $unsigned % ($maxoffset + 1) : 0;

        return array_values($DB->get_records_sql(
            "SELECT qbe.id AS entryid, q.id AS questionid, q.qtype, qbe.questioncategoryid AS categoryid
               FROM {$from}
              WHERE {$where}
           ORDER BY qbe.id ASC",
            $params,
            $offset,
            self::WINDOW_SIZE
        ));
    }

    private static function latest_by_entries(array $entryids): array {
        global $DB;

        if (!$entryids) {
            return [];
        }
        [$insql, $params] = $DB->get_in_or_equal($entryids, SQL_PARAMS_NAMED, 'entry');
        $params += [
            'ready' => question_version_status::QUESTION_STATUS_READY,
            'ready2' => question_version_status::QUESTION_STATUS_READY,
        ];

        return array_values($DB->get_records_sql(
            "SELECT qbe.id AS entryid, q.id AS questionid, q.qtype, qbe.questioncategoryid AS categoryid
               FROM {question_bank_entries} qbe
               JOIN {question_versions} qv
                 ON qv.questionbankentryid = qbe.id
                AND qv.status = :ready
                AND qv.version = (
                    SELECT MAX(qv2.version)
                      FROM {question_versions} qv2
                     WHERE qv2.questionbankentryid = qbe.id
                       AND qv2.status = :ready2
                )
               JOIN {question} q ON q.id = qv.questionid
              WHERE qbe.id {$insql}",
            $params
        ));
    }
}
