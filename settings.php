<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * settings.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
