<?php
namespace mod_masterypractice\local\scheduler;

defined('MOODLE_INTERNAL') || die();

/**
 * SM-2 adapted to Moodle fractions.
 *
 * Keeps the original SM-2 quality/easiness/repetition mechanics. Moodle
 * fractions are deterministically mapped to quality 0..5, then activity/site
 * interval limits are applied after the SM-2 interval is calculated.
 */
final class sm2_scheduler extends abstract_scheduler {
    public function key(): string {
        return 'sm2';
    }

    public static function quality_from_fraction(float $fraction): int {
        $fraction = max(0.0, min(1.0, $fraction));
        return match (true) {
            $fraction < 0.20 => 0,
            $fraction < 0.40 => 1,
            $fraction < 0.60 => 2,
            $fraction < 0.80 => 3,
            $fraction < 0.95 => 4,
            default => 5,
        };
    }

    public function review(review_input $input): review_result {
        $quality = self::quality_from_fraction($input->fraction);
        $oldrepetitions = max(0, (int) ($input->itemstate->sm2repetitions ?? 0));
        $oldinterval = max(0, (int) ($input->itemstate->sm2interval ?? 0));
        $easiness = (float) ($input->itemstate->sm2easiness ?? 2.5);
        if ($easiness <= 0.0) {
            $easiness = 2.5;
        }

        $difference = 5 - $quality;
        $easiness += 0.1 - $difference * (0.08 + $difference * 0.02);
        $easiness = max(1.3, $easiness);

        if ($quality < 3) {
            $repetitions = 0;
            $rawinterval = DAYSECS;
        } else {
            $repetitions = $oldrepetitions + 1;
            if ($repetitions === 1) {
                $rawinterval = DAYSECS;
            } else if ($repetitions === 2) {
                $rawinterval = 6 * DAYSECS;
            } else {
                $previous = $oldinterval > 0 ? $oldinterval : 6 * DAYSECS;
                $rawinterval = (int) round($previous * $easiness);
            }
        }

        $interval = $this->bound_interval($rawinterval, $input);
        $spacing = $this->spacing_factor($input);

        if ($quality >= 3) {
            $qualityfactor = ($quality - 2.0) / 3.0;
            $gain = (5.0 + 5.0 * $input->difficulty) * $qualityfactor * $spacing;
            $masterydelta = $gain * max(0.2, 1.0 - $input->mastery / 120.0);
            $confidencedelta = 2.5 + $qualityfactor * 3.5 + min(2.0, $spacing);
        } else {
            $severity = (3 - $quality) / 3.0;
            $masterydelta = -(8.0 + 8.0 * $severity)
                * (0.7 + $input->mastery / 100.0)
                * (1.15 - 0.3 * $input->difficulty);
            $confidencedelta = -(6.0 + 6.0 * $severity + $input->mastery * 0.035);
        }

        return new review_result(
            $input->now + $interval,
            $interval,
            $masterydelta,
            $confidencedelta,
            [
                'sm2repetitions' => $repetitions,
                'sm2interval' => $interval,
                'sm2easiness' => $easiness,
            ] + $this->streak_fields($input)
        );
    }
}
