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
 * Tests for the standard activity view event.
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice;

use mod_masterypractice\event\course_module_viewed;

/**
 * Verifies the standard Moodle module view event is emitted.
 *
 * @covers \mod_masterypractice\event\course_module_viewed
 */
final class event_test extends \advanced_testcase {
    /**
     * Opening the activity emits a standard view event for its module context.
     */
    public function test_activity_view_emits_course_module_viewed(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module("masterypractice", ["course" => $course->id]);
        $cm = get_coursemodule_from_instance("masterypractice", $activity->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);

        $sink = $this->redirectEvents();
        masterypractice_view($activity, $course, $cm, $context);
        $events = array_values(array_filter(
            $sink->get_events(),
            static fn($event) => $event instanceof course_module_viewed
        ));
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertSame((int) $activity->id, (int) $events[0]->objectid);
        $this->assertSame((int) $context->id, (int) $events[0]->contextid);
        $this->assertSame("masterypractice", $events[0]->objecttable);
        $this->assertSame("r", $events[0]->crud);
    }
}
