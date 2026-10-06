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
 * scheduler_test.php
 *
 * @package   mod_masterypractice
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_masterypractice;

defined('MOODLE_INTERNAL') || die();

use mod_masterypractice\local\scheduler\adaptive_mastery_scheduler;
use mod_masterypractice\local\scheduler\factory;
use mod_masterypractice\local\scheduler\leitner_scheduler;
use mod_masterypractice\local\scheduler\review_input;
use mod_masterypractice\local\scheduler\sm2_scheduler;

/**
 * Deterministic tests for all scheduling strategies.
 *
 * @covers \mod_masterypractice\local\scheduler\leitner_scheduler
 * @covers \mod_masterypractice\local\scheduler\sm2_scheduler
 * @covers \mod_masterypractice\local\scheduler\adaptive_mastery_scheduler
 * @covers \mod_masterypractice\local\scheduler\factory
 */
final class scheduler_test extends \advanced_testcase {
    /**
     * Builds a default item state.
     *
     * @return \stdClass
     */
    private function item_state(): \stdClass {
        return (object) [
            'leitnerbox' => 1,
            'sm2repetitions' => 0,
            'sm2interval' => 0,
            'sm2easiness' => 2.5,
        ];
    }

    /**
     * Leitner advances and resets boxes exactly as expected.
     */
    public function test_leitner_advances_and_resets_boxes(): void {
        $now = 2000000000;
        $state = $this->item_state();
        $scheduler = new leitner_scheduler();

        $success = $scheduler->review(new review_input(
            1.0,
            0.5,
            $now,
            $now - 7 * DAYSECS,
            HOURSECS,
            365 * DAYSECS,
            50,
            50,
            0,
            0,
            $state
        ));

        $this->assertSame(2, $success->itemfields['leitnerbox']);
        $this->assertSame(3 * DAYSECS, $success->interval);
        $this->assertGreaterThan(0, $success->masterydelta);

        $state->leitnerbox = 5;
        $failure = $scheduler->review(new review_input(
            0.0,
            0.5,
            $now,
            $now - 14 * DAYSECS,
            HOURSECS,
            365 * DAYSECS,
            80,
            80,
            4,
            0,
            $state
        ));

        $this->assertSame(1, $failure->itemfields['leitnerbox']);
        $this->assertSame(DAYSECS, $failure->interval);
        $this->assertLessThan(0, $failure->masterydelta);
        $this->assertLessThan(0, $failure->confidencedelta);
    }

    /**
     * SM-2 keeps the standard quality, easiness and interval progression.
     */
    public function test_sm2_progression_is_consistent(): void {
        $now = 2000000000;
        $scheduler = new sm2_scheduler();
        $state = $this->item_state();

        $first = $scheduler->review(new review_input(
            1.0,
            0.5,
            $now,
            0,
            HOURSECS,
            365 * DAYSECS,
            20,
            20,
            0,
            0,
            $state
        ));
        $this->assertSame(5, sm2_scheduler::quality_from_fraction(1.0));
        $this->assertSame(1, $first->itemfields['sm2repetitions']);
        $this->assertSame(DAYSECS, $first->interval);
        $this->assertEqualsWithDelta(2.6, $first->itemfields['sm2easiness'], 0.0001);

        $state->sm2repetitions = 1;
        $state->sm2interval = $first->interval;
        $state->sm2easiness = $first->itemfields['sm2easiness'];

        $second = $scheduler->review(new review_input(
            1.0,
            0.5,
            $now + DAYSECS,
            $now,
            HOURSECS,
            365 * DAYSECS,
            30,
            30,
            1,
            0,
            $state
        ));
        $this->assertSame(2, $second->itemfields['sm2repetitions']);
        $this->assertSame(6 * DAYSECS, $second->interval);

        $state->sm2repetitions = 3;
        $state->sm2interval = 20 * DAYSECS;
        $state->sm2easiness = 2.5;
        $failed = $scheduler->review(new review_input(
            0.2,
            0.5,
            $now,
            $now - 20 * DAYSECS,
            HOURSECS,
            365 * DAYSECS,
            75,
            80,
            3,
            0,
            $state
        ));
        $this->assertSame(0, $failed->itemfields['sm2repetitions']);
        $this->assertSame(DAYSECS, $failed->interval);
    }

    /**
     * Activity interval limits are applied after strategy calculation.
     */
    public function test_scheduler_interval_limits_are_enforced(): void {
        $now = 2000000000;
        $scheduler = new sm2_scheduler();
        $state = $this->item_state();
        $state->sm2repetitions = 5;
        $state->sm2interval = 60 * DAYSECS;
        $state->sm2easiness = 2.7;

        $boundedmax = $scheduler->review(new review_input(
            1.0,
            0.8,
            $now,
            $now - 60 * DAYSECS,
            2 * HOURSECS,
            10 * DAYSECS,
            70,
            70,
            5,
            0,
            $state
        ));
        $this->assertSame(10 * DAYSECS, $boundedmax->interval);

        $boundedmin = $scheduler->review(new review_input(
            0.0,
            0.5,
            $now,
            $now - DAYSECS,
            2 * DAYSECS,
            365 * DAYSECS,
            70,
            70,
            0,
            1,
            $state
        ));
        $this->assertSame(2 * DAYSECS, $boundedmin->interval);
    }

    /**
     * Adaptive Mastery values durable, difficult retrieval more strongly.
     */
    public function test_adaptive_mastery_rewards_difficult_spaced_success(): void {
        $now = 2000000000;
        $scheduler = new adaptive_mastery_scheduler();
        $state = $this->item_state();

        $easyrepeated = $scheduler->review(new review_input(
            1.0,
            0.1,
            $now,
            $now,
            HOURSECS,
            365 * DAYSECS,
            50,
            50,
            4,
            0,
            $state
        ));

        $hardspaced = $scheduler->review(new review_input(
            1.0,
            0.9,
            $now,
            $now - 21 * DAYSECS,
            HOURSECS,
            365 * DAYSECS,
            50,
            50,
            0,
            0,
            $state
        ));

        $this->assertGreaterThan($easyrepeated->masterydelta, $hardspaced->masterydelta);
        $this->assertGreaterThan($easyrepeated->interval, $hardspaced->interval);
    }

    /**
     * A surprising failure at high mastery lowers both mastery and confidence.
     */
    public function test_adaptive_failure_reduces_confidence_and_schedules_early_review(): void {
        $now = 2000000000;
        $result = (new adaptive_mastery_scheduler())->review(new review_input(
            0.0,
            0.3,
            $now,
            $now - 30 * DAYSECS,
            HOURSECS,
            365 * DAYSECS,
            92,
            90,
            5,
            0,
            $this->item_state()
        ));

        $this->assertLessThan(0, $result->masterydelta);
        $this->assertLessThan(0, $result->confidencedelta);
        $this->assertLessThanOrEqual(2 * DAYSECS, $result->interval);
    }

    /**
     * Adaptive calculations are deterministic.
     */
    public function test_adaptive_mastery_is_deterministic(): void {
        $input = new review_input(
            0.9,
            0.7,
            2000000000,
            2000000000 - 9 * DAYSECS,
            HOURSECS,
            365 * DAYSECS,
            63,
            58,
            2,
            0,
            $this->item_state()
        );
        $scheduler = factory::create('adaptive');

        $a = $scheduler->review($input);
        $b = $scheduler->review($input);

        $this->assertEquals($a, $b);
    }
}
