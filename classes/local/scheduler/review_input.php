<?php
namespace mod_masterypractice\local\scheduler;

defined('MOODLE_INTERNAL') || die();

final class review_input {
    public function __construct(
        public readonly float $fraction,
        public readonly float $difficulty,
        public readonly int $now,
        public readonly int $lastreview,
        public readonly int $mininterval,
        public readonly int $maxinterval,
        public readonly float $mastery,
        public readonly float $confidence,
        public readonly int $successstreak,
        public readonly int $failurestreak,
        public readonly ?\stdClass $itemstate = null,
    ) {
    }

    public function is_success(): bool {
        return $this->fraction >= 0.8;
    }

    public function days_since_review(): float {
        if ($this->lastreview <= 0 || $this->now <= $this->lastreview) {
            return 0.0;
        }
        return ($this->now - $this->lastreview) / DAYSECS;
    }
}
