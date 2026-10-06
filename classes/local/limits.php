<?php
// This file is part of Moodle - http://moodle.org/

namespace mod_masterypractice\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Resolves activity limits against the site's current administrative caps.
 */
final class limits {
    /**
     * Returns effective minimum and maximum review intervals.
     *
     * @param \stdClass $activity Activity.
     * @return array{0:int,1:int}
     */
    public static function intervals(\stdClass $activity): array {
        $adminmin = max(0, (int) get_config('masterypractice', 'adminmininterval'));
        $adminmax = max(0, (int) get_config('masterypractice', 'adminmaxinterval'));

        $minimum = max((int) $activity->mininterval, $adminmin);
        $maximum = (int) $activity->maxinterval;
        if ($adminmax > 0) {
            $maximum = min($maximum, $adminmax);
        }

        // A site administrator can tighten limits after an activity was saved.
        // In that case the safe interpretation is to clamp both to the new min.
        $maximum = max($minimum, $maximum);
        return [$minimum, $maximum];
    }

    /**
     * Returns the effective daily review cap.
     *
     * @param \stdClass $activity Activity.
     * @return int
     */
    public static function max_daily_reviews(\stdClass $activity): int {
        $activitylimit = max(1, (int) $activity->maxdailyreviews);
        $adminlimit = max(0, (int) get_config('masterypractice', 'adminmaxdailyreviews'));

        return $adminlimit > 0 ? min($activitylimit, $adminlimit) : $activitylimit;
    }
}
