<?php
namespace mod_masterypractice\form;

defined('MOODLE_INTERNAL') || die();

use mod_masterypractice\local\concept_repository;

require_once($CFG->libdir . '/formslib.php');

final class concept_form extends \moodleform {
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
