<?php
namespace mod_masterypractice\local\scheduler;

defined('MOODLE_INTERNAL') || die();

final class leitner_scheduler extends abstract_scheduler {
    private const BOX_INTERVALS = [
        DAYSECS,
        3 * DAYSECS,
        7 * DAYSECS,
        14 * DAYSECS,
        30 * DAYSECS,
        60 * DAYSECS,
    ];

    public function key(): string {
        return 'leitner';
    }

    public function review(review_input $input): review_result {
        $oldbox = max(1, min(6, (int) ($input->itemstate->leitnerbox ?? 1)));
        $box = $input->is_success() ? min(6, $oldbox + 1) : 1;
        $interval = $this->bound_interval(self::BOX_INTERVALS[$box - 1], $input);

        $spacing = $this->spacing_factor($input);
        if ($input->is_success()) {
            $gain = (7.0 + 4.0 * $input->difficulty) * $spacing;
            $diminishing = max(0.2, 1.0 - ($input->mastery / 120.0));
            $masterydelta = $gain * $diminishing;
            $confidencedelta = 3.0 + 2.0 * min(1.0, $spacing);
        } else {
            $penalty = (10.0 + 4.0 * (1.0 - $input->difficulty))
                * (0.65 + $input->mastery / 100.0);
            $masterydelta = -$penalty;
            $confidencedelta = -(6.0 + $input->mastery * 0.04);
        }

        return new review_result(
            $input->now + $interval,
            $interval,
            $masterydelta,
            $confidencedelta,
            ['leitnerbox' => $box] + $this->streak_fields($input)
        );
    }
}
