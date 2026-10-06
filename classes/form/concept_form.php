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
 * concept_form.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice\form;

use mod_masterypractice\concept_repository;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Class concept_form.
 */
final class concept_form extends \moodleform {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    public function definition(): void {
        $mform = $this->_form;
        $courseid = (int) $this->_customdata['courseid'];

        $mform->addElement('select', 'sourcetype', get_string('concepttype', 'masterypractice'), [
            'category' => get_string('concepttype_category', 'masterypractice'),
            'tag' => get_string('concepttype_tag', 'masterypractice'),
        ]);

        $mform->addElement('autocomplete', 'categoryid', get_string('conceptsource', 'masterypractice'),
            concept_repository::category_options($courseid));
        $mform->hideIf('categoryid', 'sourcetype', 'neq', 'category');

        $mform->addElement('autocomplete', 'tagid', get_string('conceptsource', 'masterypractice'),
            concept_repository::tag_options($courseid));
        $mform->hideIf('tagid', 'sourcetype', 'neq', 'tag');

        $mform->addElement('advcheckbox', 'includesubcategories', get_string('includesubcategories', 'masterypractice'));
        $mform->hideIf('includesubcategories', 'sourcetype', 'neq', 'category');

        $mform->addElement('text', 'weight', get_string('conceptweight', 'masterypractice'), ['size' => 8]);
        $mform->setType('weight', PARAM_FLOAT);
        $mform->setDefault('weight', 1);

        $mform->addElement('advcheckbox', 'critical', get_string('criticalconcept', 'masterypractice'));
        $mform->addElement('text', 'criticalthreshold', get_string('criticalthreshold', 'masterypractice'), ['size' => 6]);
        $mform->setType('criticalthreshold', PARAM_FLOAT);
        $mform->setDefault('criticalthreshold', 60);
        $mform->hideIf('criticalthreshold', 'critical', 'notchecked');

        $mform->addElement('hidden', 'conceptid', 0);
        $mform->setType('conceptid', PARAM_INT);
        $this->add_action_buttons();
    }

    /**
     * Method validation.
     *
     * @param mixed $data Parameter data.
     * @param mixed $files Parameter files.
     * @return array Return value.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $courseid = (int) $this->_customdata['courseid'];
        $type = $data['sourcetype'] ?? 'category';
        $sourceid = (int) ($type === 'tag' ? ($data['tagid'] ?? 0) : ($data['categoryid'] ?? 0));

        if (!$sourceid || !concept_repository::source_is_available($courseid, $type, $sourceid)) {
            $errors[$type === 'tag' ? 'tagid' : 'categoryid'] = get_string('invalidconceptsource', 'masterypractice');
        }
        if ((float) ($data['weight'] ?? 0) <= 0) {
            $errors['weight'] = get_string('errorpositive', 'masterypractice');
        }
        $threshold = (float) ($data['criticalthreshold'] ?? 0);
        if (!empty($data['critical']) && ($threshold < 0 || $threshold > 100)) {
            $errors['criticalthreshold'] = get_string('errorpercent', 'masterypractice');
        }

        return $errors;
    }
}
