<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configduration(
        'masterypractice/adminmininterval',
        get_string('adminmininterval', 'masterypractice'),
        get_string('adminmininterval_desc', 'masterypractice'),
        60,
        1
    ));
    $settings->add(new admin_setting_configduration(
        'masterypractice/adminmaxinterval',
        get_string('adminmaxinterval', 'masterypractice'),
        get_string('adminmaxinterval_desc', 'masterypractice'),
        31536000,
        1
    ));
    $settings->add(new admin_setting_configtext(
        'masterypractice/adminmaxdailyreviews',
        get_string('adminmaxdailyreviews', 'masterypractice'),
        get_string('adminmaxdailyreviews_desc', 'masterypractice'),
        500,
        PARAM_INT
    ));
    $settings->add(new admin_setting_configtext(
        'masterypractice/difficultysamples',
        get_string('difficultysamples', 'masterypractice'),
        get_string('difficultysamples_desc', 'masterypractice'),
        10,
        PARAM_INT
    ));
}
