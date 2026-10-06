<?php
namespace mod_masterypractice\event;
defined('MOODLE_INTERNAL') || die();

final class mastery_level_reached extends \core\event\base {
    protected function init(): void {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'masterypractice_concepts';
    }

    public static function get_name(): string {
        return get_string('eventmasterylevelreached', 'masterypractice');
    }

    public function get_description(): string {
        $mastery = $this->other['mastery'] ?? 0;
        return "The user with id '{$this->relateduserid}' reached mastery {$mastery}% for concept '{$this->objectid}'.";
    }
}
