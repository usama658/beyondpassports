<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Single source of truth for the site's headline stats. Reads config('ukv.stats')
 * and computes the two time-based odometers (applications, reversals) so every page
 * shows one consistent, self-incrementing number.
 *
 * Odometer: value = base + floor(days since anchor × per_day), clamped so it never
 * goes below base (e.g. if the server clock predates the anchor). Deterministic per
 * calendar day — no database, no per-request drift.
 *
 * Usage in Blade:  {{ App\Support\SiteStats::applications() }}   → "703"
 *                  {{ App\Support\SiteStats::approval() }}       → "94"
 */
final class SiteStats
{
    /** Raw integer value of an odometer stat ('applications' | 'reversals'). */
    public static function count(string $key): int
    {
        $cfg = config("ukv.stats.$key");
        if (! is_array($cfg)) {
            return 0;
        }

        $base   = (int) ($cfg['base'] ?? 0);
        $perDay = (float) ($cfg['per_day'] ?? 0);
        $anchor = CarbonImmutable::parse($cfg['anchor'] ?? 'today')->startOfDay();
        $days   = max(0, $anchor->diffInDays(CarbonImmutable::now()->startOfDay(), false));

        return $base + (int) floor($days * $perDay);
    }

    /** Thousands-formatted applications-filed count, e.g. "1,204". */
    public static function applications(): string
    {
        return number_format(self::count('applications'));
    }

    /** Thousands-formatted refusal-reversals count, e.g. "14". */
    public static function reversals(): string
    {
        return number_format(self::count('reversals'));
    }

    /** Headline approval rate without the % sign, e.g. "94". */
    public static function approval(): string
    {
        return (string) config('ukv.stats.approval_pct', '94');
    }

    /** Schengen medical-cover minimum, e.g. "€30,000". */
    public static function insuranceMin(): string
    {
        return (string) config('ukv.stats.insurance_min', '€30,000');
    }

    /** First-response target, e.g. "7 minutes". */
    public static function responseSla(): string
    {
        return (string) config('ukv.stats.response_sla', '7 minutes');
    }

    /** Founding year as an int, e.g. 2019. */
    public static function foundedYear(): int
    {
        return (int) config('ukv.stats.founded_year', 2019);
    }

    /** Whole years in operation since the founding year, e.g. 7. Never below 1. */
    public static function yearsActive(): int
    {
        return max(1, (int) CarbonImmutable::now()->year - self::foundedYear());
    }

    /**
     * WhatsApp deep-link for the primary "Check eligibility" CTA — single source so every
     * CTA lands in the same chat with the same prefilled message. Falls back to a placeholder
     * number until UKV_WHATSAPP is set (same fallback the topbar/wa-cta use).
     */
    public static function chatUrl(?string $message = null): string
    {
        $number = config('ukv.whatsapp') ?: '447882747584';
        $text   = $message ?? 'Hi Beyond Passports, I would like to check my eligibility for a Schengen visa.';

        return 'https://wa.me/'.$number.'?text='.rawurlencode($text);
    }
}
