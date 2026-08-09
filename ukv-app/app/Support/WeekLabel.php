<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Human "which week" label for an availability date, shared by the public appointment board and
 * the slot-picker modal when config('ukv.slots.week_labels') is on.
 *
 * Boundary is Monday-Sunday. Relative to today:
 *   - same week  -> "This Week {Mon Year}"
 *   - next week  -> "Next Week {Mon Year}"
 *   - 2+ weeks   -> "{Nth} Week {Mon Year}", where Nth is the week-of-month of the date itself
 *                   (ceil(day / 7)); e.g. the 17th-23rd is the 3rd week of its month.
 *
 * The month/year always come from the target date, so a "Next Week" that spills into September
 * reads "Next Week Sep 2026". Past dates (shouldn't occur — callers filter to available/future)
 * collapse to "This Week".
 */
final class WeekLabel
{
    private const ORDINAL = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th', 5 => '5th', 6 => '6th'];

    /** Full one-line label, e.g. "Next Week Aug 2026". */
    public static function for(Carbon $date, ?Carbon $today = null): string
    {
        ['rel' => $rel, 'my' => $my] = self::parts($date, $today);

        return $rel.' '.$my;
    }

    /**
     * The label split into its two display parts, for a two-line hierarchy on the board:
     *   rel = the relative/absolute week word ("This Week", "Next Week", "3rd Week")
     *   my  = the target date's "M Y" ("Aug 2026")
     *
     * @return array{rel:string, my:string}
     */
    public static function parts(Carbon $date, ?Carbon $today = null): array
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();

        $thisMonday = $today->copy()->startOfWeek(Carbon::MONDAY);
        $targetMonday = $date->copy()->startOfWeek(Carbon::MONDAY);

        // Both are Mondays, so the day gap is an exact multiple of 7 -> whole weeks, signed.
        $weeks = (int) round($thisMonday->diffInDays($targetMonday, false) / 7);

        $my = $date->format('M Y');

        if ($weeks <= 0) {
            return ['rel' => 'This Week', 'my' => $my];
        }

        if ($weeks === 1) {
            return ['rel' => 'Next Week', 'my' => $my];
        }

        $weekOfMonth = (int) ceil($date->day / 7);
        $ordinal = self::ORDINAL[$weekOfMonth] ?? $weekOfMonth.'th';

        return ['rel' => $ordinal.' Week', 'my' => $my];
    }
}
