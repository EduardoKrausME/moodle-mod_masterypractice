<?php
namespace mod_masterypractice\event;
defined('MOODLE_INTERNAL') || die();

final class practice_session_started extends \core\event\base {
    protected function init(): void {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'masterypractice_sessions';
    }

    public static function get_name(): string {
        return get_string('eventpracticesessionstarted', 'masterypractice');
    }

    public function get_description(): string {
        return "The user with id '{$this->relateduserid}' started Mastery Practice session '{$this->objectid}'.";
    }
}
