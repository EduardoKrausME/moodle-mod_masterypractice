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
 * concept_repository.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice;

use core_question\local\bank\question_version_status;

/**
 * Class concept_repository.
 */
final class concept_repository {
    /**
     * Property descendantcache.
     *
     * @var array
     */
    private static array $descendantcache = [];

    /**
     * Method get_all.
     *
     * @param int $activityid Parameter activityid.
     * @return array Return value.
     */
    public static function get_all(int $activityid): array {
        global $DB;
        return array_values($DB->get_records('masterypractice_concepts',
            ['masterypracticeid' => $activityid], 'sortorder ASC, id ASC'));
    }

    /**
     * Method label.
     *
     * @param \stdClass $concept Parameter concept.
     * @return string Return value.
     */
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

    /**
     * Method labels.
     *
     * @param int $activityid Parameter activityid.
     * @return array Return value.
     */
    public static function labels(int $activityid): array {
        $labels = [];
        foreach (self::get_all($activityid) as $concept) {
            $labels[$concept->id] = self::label($concept);
        }
        return $labels;
    }

    /**
     * Method course_context_ids.
     *
     * @param int $courseid Parameter courseid.
     * @return array Return value.
     */
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

    /**
     * Method category_options.
     *
     * @param int $courseid Parameter courseid.
     * @return array Return value.
     */
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

    /**
     * Method tag_options.
     *
     * @param int $courseid Parameter courseid.
     * @return array Return value.
     */
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

    /**
     * Method source_is_available.
     *
     * @param int $courseid Parameter courseid.
     * @param string $type Parameter type.
     * @param int $sourceid Parameter sourceid.
     * @return bool Return value.
     */
    public static function source_is_available(int $courseid, string $type, int $sourceid): bool {
        $options = $type === 'category' ? self::category_options($courseid) : self::tag_options($courseid);
        return array_key_exists($sourceid, $options);
    }

    /**
     * Method category_ids.
     *
     * @param \stdClass $concept Parameter concept.
     * @return array Return value.
     */
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

    /**
     * Method matching_concepts.
     *
     * @param int $activityid Parameter activityid.
     * @param int $questionid Parameter questionid.
     * @return array Return value.
     */
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
