<?php
namespace mod_masterypractice\local\scheduler;

defined('MOODLE_INTERNAL') || die();

final class factory {
    public static function create(string $key): scheduler_interface {
        return match ($key) {
            'leitner' => new leitner_scheduler(),
            'sm2' => new sm2_scheduler(),
            'adaptive' => new adaptive_mastery_scheduler(),
            default => throw new \coding_exception('Unknown Mastery Practice scheduler: ' . $key),
        };
    }
}
