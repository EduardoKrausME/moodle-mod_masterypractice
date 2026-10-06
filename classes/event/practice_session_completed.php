<?php
namespace mod_masterypractice\event;
defined('MOODLE_INTERNAL') || die();

final class practice_session_completed extends \core\event\base {
    protected function init(): void {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'masterypractice_sessions';
    }

    public static function get_name(): string {
        return get_string('eventpracticesessioncompleted', 'masterypractice');
    }

    public function get_description(): string {
        return "The user with id '{$this->relateduserid}' completed Mastery Practice session '{$this->objectid}'.";
    }
}
