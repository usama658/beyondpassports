<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Builds a valid iCalendar (.ics, RFC 5545) string for a document-checklist request — NO external
 * library. Produces a single VCALENDAR with two all-day VEVENTs:
 *
 *   1. "Start your {destination} application by {date}" — an actionable deadline computed as
 *      travel_date − processing_time − buffer (all in days). This is the date by which the traveller
 *      must START so the visa is in hand before they fly.
 *   2. "Check passport validity" — a fixed nudge a few days before the start deadline (most refusals
 *      trace back to a passport with too little validity left).
 *
 * The traveller downloads this file or receives it as an email attachment; it imports into Apple
 * Calendar / Google Calendar / Outlook unchanged.
 *
 * COMPLIANCE: the deadline is an ESTIMATE to help the traveller plan — it does NOT speed up or
 * guarantee any government decision. The event descriptions say so, mirroring the email/footer strip.
 *
 * No structured processing-time column exists on destinations yet, so the caller passes the
 * processing-time days explicitly; when omitted we fall back to a conservative config default
 * (config('ukv.checklist.default_processing_days')) plus a safety buffer
 * (config('ukv.checklist.deadline_buffer_days')). Both default sensibly if unset.
 */
final class IcsService
{
    private const PRODID = '-//Beyond Passports//Document Checklist//EN';

    /** Conservative fallbacks (days) when config is unset. */
    private const FALLBACK_PROCESSING_DAYS = 21;

    private const FALLBACK_BUFFER_DAYS = 7;

    /** Days BEFORE the start deadline to nudge the passport-validity check. */
    private const PASSPORT_CHECK_LEAD_DAYS = 3;

    /**
     * Build the .ics document for a checklist request.
     *
     * Returns null when there is no travel date to anchor the deadline to (nothing useful to remind
     * about) — callers should treat null as "no calendar offered" rather than an error.
     *
     * @param  string       $destination     display name, e.g. "Thailand"
     * @param  string|null  $travelDate      the trip/travel date (any Carbon-parseable string)
     * @param  int|null     $processingDays  destination processing time in days; null => config default
     */
    public function buildForChecklist(string $destination, ?string $travelDate, ?int $processingDays = null): ?string
    {
        $travel = $this->parseDate($travelDate);
        if ($travel === null) {
            return null;
        }

        $destination = trim($destination) !== '' ? trim($destination) : 'your';

        $processing = $processingDays !== null && $processingDays > 0
            ? $processingDays
            : (int) config('ukv.checklist.default_processing_days', self::FALLBACK_PROCESSING_DAYS);

        $buffer = (int) config('ukv.checklist.deadline_buffer_days', self::FALLBACK_BUFFER_DAYS);

        // Deadline = travel − processing − buffer. Never schedule the deadline in the past relative to
        // "now": if the trip is already too soon, clamp the deadline to today so the reminder still fires.
        $deadline = $travel->copy()->subDays($processing + $buffer)->startOfDay();
        $today = Carbon::now()->startOfDay();
        if ($deadline->lessThan($today)) {
            $deadline = $today;
        }

        $passportCheck = $deadline->copy()->subDays(self::PASSPORT_CHECK_LEAD_DAYS);
        if ($passportCheck->lessThan($today)) {
            $passportCheck = $today;
        }

        $events = [
            $this->event(
                summary: "Start your {$destination} application by {$deadline->format('j M Y')}",
                date: $deadline,
                description: "Apply by this date to allow time for processing before you travel on {$travel->format('j M Y')}. "
                    .'This is an estimate to help you plan — it does not speed up or guarantee any government decision. '
                    .'Independent service, not a government website.',
            ),
            $this->event(
                summary: 'Check passport validity',
                date: $passportCheck,
                description: 'Confirm your passport has enough validity left for your trip (and any required blank pages). '
                    .'Renew now if it is close — passport issues are a common, avoidable cause of refusal.',
            ),
        ];

        return $this->wrap($events);
    }

    // -----------------------------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------------------------

    /** Wrap VEVENTs in a VCALENDAR with CRLF line endings (RFC 5545 requires CRLF). */
    private function wrap(array $events): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:'.self::PRODID,
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            // Each event() returns a list of lines; flatten the array-of-events into one line list.
            ...array_merge(...$events),
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines)."\r\n";
    }

    /**
     * Build one all-day VEVENT. All-day events use DATE values (no time) with DTEND = day after
     * (exclusive end, per the spec). A VALARM fires a 1-day-before display reminder.
     *
     * @return list<string>
     */
    private function event(string $summary, Carbon $date, string $description): array
    {
        $stamp = Carbon::now()->utc()->format('Ymd\THis\Z');
        $start = $date->format('Ymd');
        $end = $date->copy()->addDay()->format('Ymd');
        $uid = Str::uuid()->toString().'@ukvisaco';

        return [
            'BEGIN:VEVENT',
            "UID:{$uid}",
            "DTSTAMP:{$stamp}",
            "DTSTART;VALUE=DATE:{$start}",
            "DTEND;VALUE=DATE:{$end}",
            'SUMMARY:'.$this->escape($summary),
            'DESCRIPTION:'.$this->escape($description),
            'TRANSP:TRANSPARENT',
            'BEGIN:VALARM',
            'TRIGGER:-P1D',
            'ACTION:DISPLAY',
            'DESCRIPTION:'.$this->escape($summary),
            'END:VALARM',
            'END:VEVENT',
        ];
    }

    /** Escape per RFC 5545 §3.3.11: backslash, comma, semicolon, and newlines. */
    private function escape(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace([',', ';'], ['\\,', '\\;'], $text);
        $text = str_replace(["\r\n", "\n", "\r"], '\\n', $text);

        return $text;
    }

    private function parseDate(?string $value): ?Carbon
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
