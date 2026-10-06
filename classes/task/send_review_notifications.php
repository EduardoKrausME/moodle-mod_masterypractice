<?php
namespace mod_masterypractice\task;
defined('MOODLE_INTERNAL') || die();

final class send_review_notifications extends \core\task\scheduled_task {
    public function get_name(): string {
        return get_string('tasksendreviewnotifications', 'masterypractice');
    }

    public function execute(): void {
        global $DB;

        $now = time();
        $sql = "SELECT us.*, mp.course, mp.name, mp.notifcooldown
                  FROM {masterypractice_usummary} us
                  JOIN {masterypractice} mp ON mp.id = us.masterypracticeid
                 WHERE mp.notifreview = 1
                   AND us.nextreview > 0
                   AND us.nextreview <= :now
                   AND (us.lastnotified = 0 OR us.lastnotified <= (:now2 - mp.notifcooldown))
              ORDER BY us.nextreview ASC";
        $records = $DB->get_records_sql($sql, ['now' => $now, 'now2' => $now], 0, 500);

        foreach ($records as $record) {
            $cm = get_coursemodule_from_instance(
                'masterypractice',
                $record->masterypracticeid,
                $record->course,
                false,
                IGNORE_MISSING
            );
            if (!$cm) {
                continue;
            }
            $context = \context_module::instance($cm->id);
            if (!has_capability('mod/masterypractice:attempt', $context, $record->userid)) {
                continue;
            }

            $user = \core_user::get_user($record->userid, '*', IGNORE_MISSING);
            if (!$user || !empty($user->deleted) || !empty($user->suspended)) {
                continue;
            }

            $message = new \core\message\message();
            $message->component = 'mod_masterypractice';
            $message->name = 'reviewavailable';
            $message->userfrom = \core_user::get_noreply_user();
            $message->userto = $user;
            $message->subject = get_string('reviewmessagesubject', 'masterypractice');
            $message->fullmessage = get_string('reviewmessagebody', 'masterypractice',
                (object) ['activity' => format_string($record->name)]);
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml = '';
            $message->smallmessage = get_string('reviewmessagesmall', 'masterypractice',
                format_string($record->name));
            $message->notification = 1;
            $message->contexturl = (new \moodle_url('/mod/masterypractice/view.php', ['id' => $cm->id]))->out(false);
            $message->contexturlname = format_string($record->name);
            message_send($message);

            $DB->set_field('masterypractice_usummary', 'lastnotified', $now, ['id' => $record->id]);
        }
    }
}
