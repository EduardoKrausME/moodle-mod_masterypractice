<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/masterypractice/backup/moodle2/backup_masterypractice_stepslib.php');

/**
 * Backup task for Mastery Practice.
 */
class backup_masterypractice_activity_task extends backup_activity_task {
    /**
     * No activity-specific backup settings.
     */
    protected function define_my_settings() {
    }

    /**
     * Adds the structure step.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_masterypractice_activity_structure_step(
            'masterypractice_structure',
            'masterypractice.xml'
        ));
    }

    /**
     * Encodes links in activity content.
     *
     * @param string $content Content.
     * @return string
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot . '/mod/masterypractice', '#');

        $content = preg_replace(
            '#(' . $base . '/index\.php\?id=)([0-9]+)#',
            '$@MASTERYPRACTICEINDEX*$2@$',
            $content
        );
        $content = preg_replace(
            '#(' . $base . '/view\.php\?id=)([0-9]+)#',
            '$@MASTERYPRACTICEVIEWBYID*$2@$',
            $content
        );

        return $content;
    }
}
