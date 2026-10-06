<?php
// This file is part of Moodle - http://moodle.org/

namespace mod_masterypractice;

defined('MOODLE_INTERNAL') || die();

use mod_masterypractice\local\mastery\decay;
use mod_masterypractice\local\mastery\difficulty_estimator;

/**
 * Tests for current-mastery decay and aggregate item difficulty.
 *
 * @covers \mod_masterypractice\local\mastery\decay
 * @covers \mod_masterypractice\local\mastery\difficulty_estimator
 */
final class mastery_test extends \advanced_testcase {
    /**
     * Persisted mastery reaches half after one configured half-life.
     */
    public function test_knowledge_decay_half_life(): void {
        $now = 2000000000;
        $value = decay::estimate(100.0, $now - 90 * DAYSECS, $now, 90);
        $this->assertEqualsWithDelta(50.0, $value, 0.0001);
    }

    /**
     * Decay never mutates the historic persisted value supplied by the caller.
     */
    public function test_decay_is_only_an_estimate(): void {
        $now = 2000000000;
        $persisted = 88.0;
        $current = decay::estimate($persisted, $now - 180 * DAYSECS, $now, 90);

        $this->assertSame(88.0, $persisted);
        $this->assertEqualsWithDelta(22.0, $current, 0.0001);
    }

    /**
     * Sparse evidence stays neutral.
     */
    public function test_question_difficulty_has_neutral_fallback(): void {
        $this->assertSame(0.5, difficulty_estimator::estimate(1, 0.0, 10));
        $this->assertSame(0.5, difficulty_estimator::estimate(9, 9.0, 10));
    }

    /**
     * Sufficient aggregate evidence changes difficulty with a neutral prior.
     */
    public function test_question_difficulty_uses_aggregate_evidence(): void {
        $hard = difficulty_estimator::estimate(20, 4.0, 10);
        $easy = difficulty_estimator::estimate(20, 18.0, 10);

        $this->assertGreaterThan(0.5, $hard);
        $this->assertLessThan(0.5, $easy);
        $this->assertGreaterThan($easy, $hard);
    }
}
