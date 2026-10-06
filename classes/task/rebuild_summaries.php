<?php
namespace mod_masterypractice\task;
defined('MOODLE_INTERNAL') || die();

use mod_masterypractice\local\class_summary_manager;

final class rebuild_summaries extends \core\task\scheduled_task {
    public function get_name(): string {
        return get_string('taskrebuildsummaries', 'masterypractice');
    }

    public function execute(): void {
        global $DB;
        $now = time();
        foreach ($DB->get_records('masterypractice', null, '', 'id') as $activity) {
            class_summary_manager::rebuild_activity((int) $activity->id, $now);
        }
    }
}
