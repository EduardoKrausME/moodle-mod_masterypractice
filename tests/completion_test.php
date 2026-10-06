<?php
// This file is part of Moodle - http://moodle.org/

namespace mod_masterypractice;

defined('MOODLE_INTERNAL') || die();

use mod_masterypractice\local\completion\evaluator;

/**
 * Completion rules stay separate from decayed current mastery.
 *
 * @covers \mod_masterypractice\local\completion\evaluator
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
