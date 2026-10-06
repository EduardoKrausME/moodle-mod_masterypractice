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
 * selection_test.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice;

defined('MOODLE_INTERNAL') || die();

use mod_masterypractice\local\selection\question_selector;

/**
 * Pure selection-priority tests.
 *
 * @covers \mod_masterypractice\local\selection\question_selector
 */
final class selection_test extends \advanced_testcase {
    /**
     * A due timestamp is due only when it has actually arrived.
     */
    public function test_question_due_detection(): void {
        $now = 2000000000;

        $this->assertTrue(question_selector::is_due($now, $now));
        $this->assertTrue(question_selector::is_due($now - 1, $now));
        $this->assertFalse(question_selector::is_due($now + 1, $now));
        $this->assertFalse(question_selector::is_due(0, $now));
    }

    /**
     * Low mastery and confidence produce higher selection priority.
     */
    public function test_low_mastery_is_prioritised(): void {
        $now = 2000000000;
        $concept = (object) ['weight' => 1.0, 'critical' => 0];
        $weak = (object) [
            'mastery' => 30,
            'confidence' => 35,
            'attempts' => 5,
            'nextreview' => $now - DAYSECS,
        ];
        $strong = (object) [
            'mastery' => 92,
            'confidence' => 90,
            'attempts' => 5,
            'nextreview' => $now + 30 * DAYSECS,
        ];

        $this->assertGreaterThan(
            question_selector::concept_priority($concept, $strong, $now),
            question_selector::concept_priority($concept, $weak, $now)
        );
    }

    /**
     * Critical concepts and configured weights affect priority.
     */
    public function test_critical_concept_and_weight_raise_priority(): void {
        $now = 2000000000;
        $state = (object) [
            'mastery' => 60,
            'confidence' => 60,
            'attempts' => 4,
            'nextreview' => $now + DAYSECS,
        ];
        $normal = (object) ['weight' => 1.0, 'critical' => 0];
        $critical = (object) ['weight' => 2.0, 'critical' => 1];

        $this->assertGreaterThan(
            question_selector::concept_priority($normal, $state, $now),
            question_selector::concept_priority($critical, $state, $now)
        );
    }
}
