<?php
namespace mod_masterypractice\local\mastery;
defined('MOODLE_INTERNAL') || die();

final class difficulty_estimator {
    public static function estimate(int $attempts, float $totalfraction, int $minimumsample): float {
        $minimumsample = max(1, $minimumsample);
        if ($attempts < $minimumsample) {
            return 0.5;
        }
        $priorstrength = (float) $minimumsample;
        $posterioraccuracy = ($totalfraction + 0.5 * $priorstrength) / ($attempts + $priorstrength);
        return max(0.05, min(0.95, 1.0 - $posterioraccuracy));
    }
}
