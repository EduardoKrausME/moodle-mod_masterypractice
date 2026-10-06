<?php
namespace mod_masterypractice\local;

defined('MOODLE_INTERNAL') || die();

use core_question\local\bank\question_version_status;

final class concept_repository {
    private static array $descendantcache = [];

    public static function get_all(int $activityid): array {
        global $DB;
        return array_values($DB->get_records('masterypractice_concepts',
            ['masterypracticeid' => $activityid], 'sortorder ASC, id ASC'));
    }

    public static function label(\stdClass $concept): string {
        global $DB;
        if ($concept->sourcetype === 'category') {
            return (string) $DB->get_field('question_categories', 'name', ['id' => $concept->sourceid]);
        }
        if ($concept->sourcetype === 'tag') {
            return (string) $DB->get_field('tag', 'rawname', ['id' => $concept->sourceid]);
        }
        return '#' . $concept->sourceid;
    }

    public static function labels(int $activityid): array {
        $labels = [];
        foreach (self::get_all($activityid) as $concept) {
            $labels[$concept->id] = self::label($concept);
        }
        return $labels;
    }

    public static function course_context_ids(int $courseid): array {
        global $DB;
        $coursecontext = \context_course::instance($courseid);
        $like = $DB->sql_like('path', ':childpath', false);
        $records = $DB->get_records_sql(
            "SELECT id FROM {context} WHERE id = :contextid OR {$like}",
            ['contextid' => $coursecontext->id, 'childpath' => $coursecontext->path . '/%']
        );
        return array_map('intval', array_keys($records));
    }

    public static function category_options(int $courseid): array {
        global $DB;
        $contextids = self::course_context_ids($courseid);
        if (!$contextids) {
            return [];
        }
        [$insql, $params] = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'ctx');
        $records = $DB->get_records_sql(
            "SELECT qc.id, qc.name FROM {question_categories} qc
              WHERE qc.contextid {$insql} ORDER BY qc.name ASC, qc.id ASC",
            $params
        );
        $options = [];
        foreach ($records as $record) {
            if ($record->name !== 'top') {
                $options[$record->id] = format_string($record->name);
            }
        }
        return $options;
    }

    public static function tag_options(int $courseid): array {
        global $DB;
        $contextids = self::course_context_ids($courseid);
        if (!$contextids) {
            return [];
        }
        [$insql, $params] = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'ctx');
        $params += [
            'component' => 'core_question',
            'itemtype' => 'question',
            'ready' => question_version_status::QUESTION_STATUS_READY,
        ];
        $sql = "SELECT DISTINCT t.id, t.rawname
                  FROM {tag} t
                  JOIN {tag_instance} ti ON ti.tagid = t.id
                   AND ti.component = :component AND ti.itemtype = :itemtype
                  JOIN {question} q ON q.id = ti.itemid
                  JOIN {question_versions} qv ON qv.questionid = q.id AND qv.status = :ready
                  JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                  JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
                 WHERE qc.contextid {$insql}
              ORDER BY t.rawname ASC";
        $records = $DB->get_records_sql($sql, $params);
        $options = [];
        foreach ($records as $record) {
            $options[$record->id] = format_string($record->rawname);
        }
        return $options;
    }

    public static function source_is_available(int $courseid, string $type, int $sourceid): bool {
        $options = $type === 'category' ? self::category_options($courseid) : self::tag_options($courseid);
        return array_key_exists($sourceid, $options);
    }

    public static function category_ids(\stdClass $concept): array {
        global $DB;
        $root = (int) $concept->sourceid;
        if (empty($concept->includesubcategories)) {
            return [$root];
        }
        if (isset(self::$descendantcache[$root])) {
            return self::$descendantcache[$root];
        }

        $ids = [$root];
        $frontier = [$root];
        while ($frontier) {
            [$insql, $params] = $DB->get_in_or_equal($frontier, SQL_PARAMS_NAMED, 'parent');
            $children = $DB->get_records_select('question_categories', "parent {$insql}", $params, '', 'id');
            $frontier = [];
            foreach ($children as $child) {
                $childid = (int) $child->id;
                if (!in_array($childid, $ids, true)) {
                    $ids[] = $childid;
                    $frontier[] = $childid;
                }
            }
        }
        return self::$descendantcache[$root] = $ids;
    }

    public static function matching_concepts(int $activityid, int $questionid): array {
        global $DB;
        $question = $DB->get_record_sql(
            "SELECT qbe.questioncategoryid AS categoryid
               FROM {question_versions} qv
               JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
              WHERE qv.questionid = :questionid",
            ['questionid' => $questionid]
        );
        if (!$question) {
            return [];
        }

        $tagids = array_map('intval', $DB->get_fieldset_select(
            'tag_instance',
            'tagid',
            'component = :component AND itemtype = :itemtype AND itemid = :itemid',
            ['component' => 'core_question', 'itemtype' => 'question', 'itemid' => $questionid]
        ));

        $matches = [];
        foreach (self::get_all($activityid) as $concept) {
            if ($concept->sourcetype === 'tag') {
                if (in_array((int) $concept->sourceid, $tagids, true)) {
                    $matches[] = $concept;
                }
            } else if (in_array((int) $question->categoryid, self::category_ids($concept), true)) {
                $matches[] = $concept;
            }
        }
        return $matches;
    }
}
