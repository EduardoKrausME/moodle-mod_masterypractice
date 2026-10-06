<?php
namespace mod_masterypractice\local\scheduler;

defined('MOODLE_INTERNAL') || die();

final class review_result {
    public function __construct(
        public readonly int $nextreview,
        public readonly int $interval,
        public readonly float $masterydelta,
        public readonly float $confidencedelta,
        public readonly array $itemfields = [],
    ) {
    }
}
