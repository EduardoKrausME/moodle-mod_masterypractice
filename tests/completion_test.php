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
 * completion_test.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice;

use mod_masterypractice\completion\evaluator;

/**
 * Completion rules stay separate from decayed current mastery.
 *
 * @covers \mod_masterypractice\completion\evaluator
 */
final class completion_test extends \advanced_testcase {
    /**
     * All enabled rules must pass in AND mode.
     */
    public function test_completion_requires_all_enabled_rules(): void {
        $rules = [
            'sessions' => 5,
            'questions' => 40,
            'mastery' => 80,
            'critical' => true,
        ];
        $values = [
            'sessions' => 5,
            'questions' => 50,
            'mastery' => 84,
            'criticalok' => true,
        ];

        $this->assertTrue(evaluator::evaluate_values($values, $rules, true));

        $values['criticalok'] = false;
        $this->assertFalse(evaluator::evaluate_values($values, $rules, true));
    }

    /**
     * A critical concept can block completion despite high overall mastery.
     */
    public function test_critical_concept_blocks_high_overall_mastery(): void {
        $this->assertFalse(evaluator::evaluate_values(
            [
                'sessions' => 10,
                'questions' => 100,
                'mastery' => 95,
                'criticalok' => false,
            ],
            [
                'sessions' => 5,
                'questions' => 20,
                'mastery' => 80,
                'critical' => true,
            ],
            true
        ));
    }

    /**
     * OR mode succeeds when any enabled rule succeeds.
     */
    public function test_completion_or_mode(): void {
        $this->assertTrue(evaluator::evaluate_values(
            [
                'sessions' => 1,
                'questions' => 100,
                'mastery' => 20,
                'criticalok' => false,
            ],
            [
                'sessions' => 5,
                'questions' => 50,
                'mastery' => 80,
                'critical' => true,
            ],
            false
        ));
    }
}
