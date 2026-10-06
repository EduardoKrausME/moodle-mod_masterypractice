<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/masterypractice/backup/moodle2/restore_masterypractice_stepslib.php');

/**
 * Restore task for Mastery Practice.
 */
class restore_masterypractice_activity_task extends restore_activity_task {
    /**
     * No activity-specific restore settings.
     */
    protected function define_my_settings() {
    }

    /**
     * Adds the structure step.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_masterypractice_activity_structure_step(
            'masterypractice_structure',
            'masterypractice.xml'
        ));
    }

    /**
     * Content requiring link decoding.
     *
     * @return restore_decode_content[]
     */
    public static function define_decode_contents() {
        return [
            new restore_decode_content('masterypractice', ['intro'], 'masterypractice'),
        ];
    }

    /**
     * Link decoding rules.
     *
     * @return restore_decode_rule[]
     */
    public static function define_decode_rules() {
        return [
            new restore_decode_rule(
                'MASTERYPRACTICEINDEX',
                '/mod/masterypractice/index.php?id=$1',
                'course'
            ),
            new restore_decode_rule(
                'MASTERYPRACTICEVIEWBYID',
                '/mod/masterypractice/view.php?id=$1',
                'course_module'
            ),
        ];
    }
}
