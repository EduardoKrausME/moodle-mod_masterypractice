<?php
namespace mod_masterypractice\local\scheduler;

defined('MOODLE_INTERNAL') || die();

abstract class abstract_scheduler implements scheduler_interface {
    protected function bound_interval(int|float $seconds, review_input $input): int {
        $seconds = (int) round($seconds);
        return max($input->mininterval, min($input->maxinterval, $seconds));
    }

    protected function streak_fields(review_input $input): array {
        if ($input->is_success()) {
            return [
                'successstreak' => $input->successstreak + 1,
                'failurestreak' => 0,
            ];
        }

        return [
            'successstreak' => 0,
            'failurestreak' => $input->failurestreak + 1,
        ];
    }

    protected function spacing_factor(review_input $input): float {
        $days = $input->days_since_review();
        if ($days <= 0.0) {
            return 0.45;
        }

        return min(1.5, max(0.55, log(1.0 + $days, 2) / 3.0 + 0.45));
    }
}
