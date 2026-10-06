<?php
namespace mod_masterypractice\local\mastery;
defined('MOODLE_INTERNAL') || die();

final class decay {
    public static function estimate(float $value, int $lastreview, int $now, int $halflifedays): float {
        $value = max(0.0, min(100.0, $value));
        if ($value <= 0.0 || $lastreview <= 0 || $now <= $lastreview || $halflifedays <= 0) {
            return $value;
        }
        $elapsed = ($now - $lastreview) / DAYSECS;
        return max(0.0, min(100.0, $value * pow(0.5, $elapsed / $halflifedays)));
    }

    public static function estimate_confidence(float $confidence, int $lastreview, int $now, int $halflifedays): float {
        return self::estimate($confidence, $lastreview, $now, max(1, (int) round($halflifedays * 0.75)));
    }
}
