<?php
namespace mod_masterypractice\local\scheduler;

defined('MOODLE_INTERNAL') || die();

interface scheduler_interface {
    public function key(): string;
    public function review(review_input $input): review_result;
}
