<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => '\\mod_masterypractice\\task\\rebuild_summaries',
        'blocking' => 0,
        'minute' => '*/15',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
    [
        'classname' => '\\mod_masterypractice\\task\\send_review_notifications',
        'blocking' => 0,
        'minute' => '17',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];
