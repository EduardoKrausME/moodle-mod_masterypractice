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
 * restore_masterypractice_stepslib.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure restore step.
 */
class restore_masterypractice_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines paths.
     *
     * @return restore_path_element[]
     */
    protected function define_structure() {
        return $this->prepare_activity_structure([
            new restore_path_element('masterypractice', '/activity/masterypractice'),
            new restore_path_element(
                'masterypractice_categoryconcept',
                '/activity/masterypractice/concepts/categoryconcept'
            ),
            new restore_path_element(
                'masterypractice_tagconcept',
                '/activity/masterypractice/concepts/tagconcept'
            ),
        ]);
    }

    /**
     * Restores the activity instance.
     *
     * @param array|stdClass $data Backup data.
     * @return void
     */
    protected function process_masterypractice($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $newid = $DB->insert_record('masterypractice', $data);
        $this->apply_activity_instance($newid);
        $this->set_mapping('masterypractice', $oldid, $newid);
    }

    /**
     * Restores a category concept if its source can be mapped safely.
     *
     * @param array|stdClass $data Backup data.
     * @return void
     */
    protected function process_masterypractice_categoryconcept($data) {
        $data = (object) $data;
        $mapped = $this->get_mappingid('question_category', $data->sourceid, 0);

        if (!$mapped && $this->source_available('category', (int) $data->sourceid)) {
            $mapped = (int) $data->sourceid;
        }
        if (!$mapped) {
            return;
        }

        $this->insert_concept($data, 'category', $mapped);
    }

    /**
     * Restores a tag concept by existing id or source label.
     *
     * @param array|stdClass $data Backup data.
     * @return void
     */
    protected function process_masterypractice_tagconcept($data) {
        global $DB;

        $data = (object) $data;
        $sourceid = 0;

        if ($this->source_available('tag', (int) $data->sourceid)) {
            $sourceid = (int) $data->sourceid;
        } else if (!empty($data->sourcename)) {
            $candidates = $DB->get_records('tag', ['rawname' => $data->sourcename], 'id ASC', 'id');
            foreach ($candidates as $candidate) {
                if ($this->source_available('tag', (int) $candidate->id)) {
                    $sourceid = (int) $candidate->id;
                    break;
                }
            }
        }

        if ($sourceid) {
            $this->insert_concept($data, 'tag', $sourceid);
        }
    }

    /**
     * Inserts one restored concept without learner-derived state.
     *
     * @param stdClass $data Data.
     * @param string $type Source type.
     * @param int $sourceid New source id.
     * @return void
     */
    private function insert_concept(stdClass $data, string $type, int $sourceid): void {
        global $DB;

        $record = (object) [
            'masterypracticeid' => $this->get_new_parentid('masterypractice'),
            'sourcetype' => $type,
            'sourceid' => $sourceid,
            'includesubcategories' => $type === 'category' && !empty($data->includesubcategories) ? 1 : 0,
            'weight' => (float) $data->weight,
            'critical' => !empty($data->critical) ? 1 : 0,
            'criticalthreshold' => (float) $data->criticalthreshold,
            'sortorder' => (int) $data->sortorder,
            'timecreated' => (int) $data->timecreated,
            'timemodified' => (int) $data->timemodified,
        ];

        if (!$DB->record_exists('masterypractice_concepts', [
            'masterypracticeid' => $record->masterypracticeid,
            'sourcetype' => $record->sourcetype,
            'sourceid' => $record->sourceid,
        ])) {
            $DB->insert_record('masterypractice_concepts', $record);
        }
    }

    /**
     * Whether a source is usable in the restored course.
     *
     * @param string $type category|tag.
     * @param int $sourceid Source id.
     * @return bool
     */
    private function source_available(string $type, int $sourceid): bool {
        global $CFG;

        require_once($CFG->dirroot . '/mod/masterypractice/classes/local/concept_repository.php');
        return \mod_masterypractice\concept_repository::source_is_available(
            $this->get_courseid(),
            $type,
            $sourceid
        );
    }

    /**
     * Restores intro files.
     *
     * @return void
     */
    protected function after_execute() {
        $this->add_related_files('mod_masterypractice', 'intro', null);
    }
}
