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
 * mod_form.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Class mod_masterypractice_mod_form.
 */
class mod_masterypractice_mod_form extends moodleform_mod {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('masterypracticename', 'masterypractice'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $this->standard_intro_elements();

        $mform->addElement('header', 'sessionheader', get_string('sessionheader', 'masterypractice'));
        $mform->addElement('text', 'questionspersession', get_string('questionspersession', 'masterypractice'), ['size' => 6]);
        $mform->setType('questionspersession', PARAM_INT);
        $mform->setDefault('questionspersession', 10);

        $mform->addElement('text', 'minquestions', get_string('minquestions', 'masterypractice'), ['size' => 6]);
        $mform->setType('minquestions', PARAM_INT);
        $mform->setDefault('minquestions', 5);

        $mform->addElement('text', 'maxquestions', get_string('maxquestions', 'masterypractice'), ['size' => 6]);
        $mform->setType('maxquestions', PARAM_INT);
        $mform->setDefault('maxquestions', 20);

        $mform->addElement('text', 'estimatedminutes', get_string('estimatedminutes', 'masterypractice'), ['size' => 6]);
        $mform->setType('estimatedminutes', PARAM_INT);
        $mform->setDefault('estimatedminutes', 8);

        $mform->addElement('advcheckbox', 'mixconcepts', get_string('mixconcepts', 'masterypractice'));
        $mform->setDefault('mixconcepts', 1);

        $mform->addElement('advcheckbox', 'allowextra', get_string('allowextra', 'masterypractice'));
        $mform->setDefault('allowextra', 1);

        $mform->addElement('duration', 'minsessioninterval', get_string('minsessioninterval', 'masterypractice'));
        $mform->setDefault('minsessioninterval', 0);

        $mform->addElement('text', 'maxdailyreviews', get_string('maxdailyreviews', 'masterypractice'), ['size' => 6]);
        $mform->setType('maxdailyreviews', PARAM_INT);
        $mform->setDefault('maxdailyreviews', 50);

        $mform->addElement('header', 'algorithmheader', get_string('algorithmheader', 'masterypractice'));
        $mform->addElement('select', 'scheduler', get_string('scheduler', 'masterypractice'), [
            'adaptive' => get_string('scheduler_adaptive', 'masterypractice'),
            'sm2' => get_string('scheduler_sm2', 'masterypractice'),
            'leitner' => get_string('scheduler_leitner', 'masterypractice'),
        ]);
        $mform->setDefault('scheduler', 'adaptive');

        $mform->addElement('duration', 'mininterval', get_string('mininterval', 'masterypractice'));
        $mform->setDefault('mininterval', HOURSECS);

        $mform->addElement('duration', 'maxinterval', get_string('maxinterval', 'masterypractice'));
        $mform->setDefault('maxinterval', 180 * DAYSECS);

        $mform->addElement('text', 'decayhalflifedays', get_string('decayhalflifedays', 'masterypractice'), ['size' => 6]);
        $mform->setType('decayhalflifedays', PARAM_INT);
        $mform->setDefault('decayhalflifedays', 90);
        $mform->addHelpButton('decayhalflifedays', 'decayhalflifedays', 'masterypractice');

        $mform->addElement('header', 'conceptsheader', get_string('conceptsheader', 'masterypractice'));
        $mform->addElement('static', 'conceptsinfo', '', get_string('conceptsconfiguredafter', 'masterypractice'));
        if (!empty($this->_cm) && !empty($this->_cm->id)) {
            $url = new moodle_url('/mod/masterypractice/concepts.php', ['id' => $this->_cm->id]);
            $mform->addElement('static', 'conceptslink', '',
                html_writer::link($url, get_string('manageconcepts', 'masterypractice')));
        }

        $mform->addElement('header', 'gradeheader', get_string('gradeheader', 'masterypractice'));
        $mform->addElement('select', 'gradepolicy', get_string('gradepolicy', 'masterypractice'), [
            'none' => get_string('gradepolicy_none', 'masterypractice'),
            'best' => get_string('gradepolicy_best', 'masterypractice'),
            'average' => get_string('gradepolicy_average', 'masterypractice'),
            'mastery' => get_string('gradepolicy_mastery', 'masterypractice'),
        ]);
        $mform->setDefault('gradepolicy', 'none');
        $mform->addElement('text', 'grade', get_string('grademax', 'masterypractice'), ['size' => 6]);
        $mform->setType('grade', PARAM_INT);
        $mform->setDefault('grade', 100);
        $mform->hideIf('grade', 'gradepolicy', 'eq', 'none');
        $mform->addElement('static', 'gradeexplain', '', get_string('masterygradeexplain', 'masterypractice'));

        $mform->addElement('header', 'notificationheader', get_string('notificationheader', 'masterypractice'));
        $mform->addElement('advcheckbox', 'notifreview', get_string('notifreview', 'masterypractice'));
        $mform->addElement('duration', 'notifcooldown', get_string('notifcooldown', 'masterypractice'));
        $mform->setDefault('notifcooldown', 3 * DAYSECS);
        $mform->hideIf('notifcooldown', 'notifreview', 'notchecked');

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Method add_completion_rules.
     *
     * @return array Return value.
     */
    public function add_completion_rules(): array {
        $mform = $this->_form;
        $suffix = $this->get_suffix();
        $groups = [];

        $definitions = [
            'completionsessions' => get_string('completionsessions', 'masterypractice'),
            'completionquestions' => get_string('completionquestions', 'masterypractice'),
            'completionmastery' => get_string('completionmastery', 'masterypractice'),
        ];

        foreach ($definitions as $field => $label) {
            $enabled = $field . 'enabled' . $suffix;
            $value = $field . $suffix;
            $groupname = $field . 'group' . $suffix;
            $elements = [
                $mform->createElement('checkbox', $enabled, '', $label),
                $mform->createElement('text', $value, '', ['size' => 5]),
            ];
            $mform->addGroup($elements, $groupname, '', ' ', false);
            $mform->setType($value, PARAM_INT);
            $mform->hideIf($value, $enabled, 'notchecked');
            $groups[] = $groupname;
        }

        $critical = 'completioncritical' . $suffix;
        $mform->addElement('checkbox', $critical, '', get_string('completioncritical', 'masterypractice'));
        $groups[] = $critical;

        return $groups;
    }

    /**
     * Method completion_rule_enabled.
     *
     * @param mixed $data Parameter data.
     * @return bool Return value.
     */
    public function completion_rule_enabled($data): bool {
        $suffix = $this->get_suffix();
        foreach (['completionsessions', 'completionquestions', 'completionmastery'] as $field) {
            if (!empty($data[$field . 'enabled' . $suffix]) && !empty($data[$field . $suffix])) {
                return true;
            }
        }
        return !empty($data['completioncritical' . $suffix]);
    }

    /**
     * Method data_postprocessing.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    public function data_postprocessing($data): void {
        parent::data_postprocessing($data);

        if (empty($data->completionunlocked)) {
            return;
        }

        $suffix = $this->get_suffix();
        $completionfield = 'completion' . $suffix;
        $automatic = !empty($data->{$completionfield})
            && (int) $data->{$completionfield} === COMPLETION_TRACKING_AUTOMATIC;

        foreach (['completionsessions', 'completionquestions', 'completionmastery'] as $field) {
            $enabled = $field . 'enabled' . $suffix;
            $value = $field . $suffix;
            if (!$automatic || empty($data->{$enabled})) {
                $data->{$value} = 0;
            }
        }

        if (!$automatic) {
            $data->{'completioncritical' . $suffix} = 0;
        }
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

        $minquestions = (int) ($data['minquestions'] ?? 0);
        $maxquestions = (int) ($data['maxquestions'] ?? 0);
        $per = (int) ($data['questionspersession'] ?? 0);

        if ($minquestions < 1 || $maxquestions < 1 || $per < 1) {
            $errors['minquestions'] = get_string('errorpositive', 'masterypractice');
        } else if ($minquestions > $maxquestions) {
            $errors['minquestions'] = get_string('errorminmaxquestions', 'masterypractice');
        } else if ($per < $minquestions || $per > $maxquestions) {
            $errors['questionspersession'] = get_string('errorquestionspersession', 'masterypractice');
        }

        $mininterval = (int) ($data['mininterval'] ?? 0);
        $maxinterval = (int) ($data['maxinterval'] ?? 0);
        $adminmin = (int) get_config('masterypractice', 'adminmininterval');
        $adminmax = (int) get_config('masterypractice', 'adminmaxinterval');
        $adminmaxdaily = (int) get_config('masterypractice', 'adminmaxdailyreviews');

        if ($mininterval > $maxinterval) {
            $errors['mininterval'] = get_string('errorintervalorder', 'masterypractice');
        }
        if ($adminmin && $mininterval < $adminmin) {
            $errors['mininterval'] = get_string('erroradminmininterval', 'masterypractice');
        }
        if ($adminmax && $maxinterval > $adminmax) {
            $errors['maxinterval'] = get_string('erroradminmaxinterval', 'masterypractice');
        }
        if ($adminmaxdaily && (int) ($data['maxdailyreviews'] ?? 0) > $adminmaxdaily) {
            $errors['maxdailyreviews'] = get_string('erroradminmaxdaily', 'masterypractice');
        }
        if ((int) ($data['decayhalflifedays'] ?? 0) < 1) {
            $errors['decayhalflifedays'] = get_string('errorpositive', 'masterypractice');
        }
        if (($data['gradepolicy'] ?? 'none') !== 'none'
                && ((int) ($data['grade'] ?? 0) < 1 || (int) $data['grade'] > 1000)) {
            $errors['grade'] = get_string('errorpositive', 'masterypractice');
        }

        return $errors;
    }
}
