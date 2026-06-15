<?php

namespace App\Enums;

/**
 * Order eligibility lane.
 * from: ukv_eligibility — standard/manual_review are computed; cleared/referred are agent-set.
 */
enum EligibilityLane: string
{
    case Standard = 'standard';
    case ManualReview = 'manual_review';
    case Cleared = 'cleared';
    case Referred = 'referred';

    /** Lanes that are considered "cleared" for the eligibility gate (allowed past `paid`). */
    public function isCleared(): bool
    {
        return in_array($this, [self::Standard, self::Cleared], true);
    }
}
