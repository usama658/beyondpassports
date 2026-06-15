<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Models\Order;
use App\Models\OrderEvent;
use Illuminate\Support\Carbon;

/**
 * Advisory fraud / risk guard (Phase-2 #128).
 *
 * Purpose: FLAG risky orders for a human to review — it never blocks a legitimate customer.
 * It complements Stripe Radar (which is dashboard-configured and acts on the *card*); this
 * layer scores the *application* using only data we already hold. No external calls, no
 * third-party lookups — privacy-safe by construction.
 *
 * assess() is a pure read + score: it returns {score, flags} and writes nothing. The caller
 * (ApplyController) decides whether to persist a flag via flagIfRisky() once the score crosses
 * THRESHOLD.
 *
 * Heuristics (each contributes a weight to the score):
 *   - velocity       : >= VELOCITY_LIMIT orders from the same email OR IP inside VELOCITY_WINDOW
 *   - duplicate      : another recent order with the same email + destination (resubmission)
 *   - disposable     : email domain is a known throwaway/disposable provider
 *   - email_pattern  : structurally suspicious email (no domain, heavy plus/dot noise, gibberish)
 *   - prior_refusal  : applicant declared a previous visa refusal (already captured on intake)
 *   - contradictory  : mismatched/contradictory intake fields (e.g. minor with no guardian,
 *                      passport expired, travel date in the past)
 *   - missing        : key contact fields absent (no email, no phone)
 */
final class FraudService
{
    /** Score at/above which an order is flagged for human review. */
    public const THRESHOLD = 50;

    /** Velocity: this many orders (inclusive) from one email/IP inside the window trips it.
     *  2 = a repeat application from the same email/IP within the window is already suspicious. */
    private const VELOCITY_LIMIT = 2;

    /** Velocity / duplicate look-back window, in hours. */
    private const VELOCITY_WINDOW_HOURS = 24;

    /** Per-heuristic weights. Tuned so any single strong signal alone does not auto-flag, but
     *  velocity (the clearest abuse signal) does. Sum of two soft signals also crosses. */
    private const WEIGHTS = [
        'velocity' => 50,
        'duplicate' => 30,
        'disposable_email' => 30,
        'email_pattern' => 20,
        'prior_refusal' => 20,
        'contradictory_fields' => 25,
        'missing_contact' => 20,
    ];

    /** Known disposable / throwaway email domains (lower-case, no leading @). */
    private const DISPOSABLE_DOMAINS = [
        'mailinator.com', 'guerrillamail.com', 'guerrillamail.info', 'sharklasers.com',
        'yopmail.com', 'trashmail.com', 'temp-mail.org', 'tempmail.com', 'getnada.com',
        '10minutemail.com', 'dispostable.com', 'maildrop.cc', 'throwawaymail.com',
        'fakeinbox.com', 'mailnesia.com', 'spam4.me', 'mohmal.com', 'tmail.io',
        'discard.email', 'mailcatch.com', 'emailondeck.com',
    ];

    /**
     * Score an order with no side effects.
     *
     * @return array{score:int, flags:list<string>}
     */
    public function assess(Order $order, ?string $ip = null): array
    {
        $flags = [];

        if ($this->velocityTripped($order, $ip)) {
            $flags[] = 'velocity';
        }

        if ($this->isDuplicate($order)) {
            $flags[] = 'duplicate';
        }

        $email = $this->normaliseEmail($order->email);

        if ($email !== null && $this->isDisposableEmail($email)) {
            $flags[] = 'disposable_email';
        }

        if ($email !== null && $this->isSuspiciousEmailPattern($email)) {
            $flags[] = 'email_pattern';
        }

        if ((bool) $order->prior_refusal) {
            $flags[] = 'prior_refusal';
        }

        if ($this->hasContradictoryFields($order)) {
            $flags[] = 'contradictory_fields';
        }

        if ($this->hasMissingContact($order)) {
            $flags[] = 'missing_contact';
        }

        $score = 0;
        foreach ($flags as $flag) {
            $score += self::WEIGHTS[$flag] ?? 0;
        }

        return ['score' => $score, 'flags' => $flags];
    }

    /**
     * Assess + persist the advisory flag when the score crosses THRESHOLD. Records a system
     * OrderEvent (agent=fraud) with the reasons. NEVER blocks — only annotates the order.
     *
     * Returns the same {score, flags} as assess() so the caller can log/telemetry it.
     *
     * @return array{score:int, flags:list<string>, flagged:bool}
     */
    public function flagIfRisky(Order $order, ?string $ip = null): array
    {
        $result = $this->assess($order, $ip);

        $flagged = $result['score'] >= self::THRESHOLD;

        if ($flagged) {
            $order->risk_flag = true;
            $order->risk_score = $result['score'];
            $order->risk_reason = $result['flags'];
            $order->save();

            $this->recordEvent($order, $result['score'], $result['flags'], $ip);
        }

        return $result + ['flagged' => $flagged];
    }

    // -----------------------------------------------------------------------------------
    // Heuristics (all read-only)
    // -----------------------------------------------------------------------------------

    /**
     * Velocity: count other orders sharing this order's email (or the supplied IP, matched
     * against the fraud OrderEvent meta where we stamp it) within the window. The current
     * order is included in the tally, so VELOCITY_LIMIT=3 means "this is the 3rd+ in 24h".
     */
    private function velocityTripped(Order $order, ?string $ip): bool
    {
        $since = Carbon::now()->subHours(self::VELOCITY_WINDOW_HOURS);
        $email = $this->normaliseEmail($order->email);

        if ($email !== null) {
            $emailCount = Order::query()
                ->where('email', $order->email)
                ->where('created_at', '>=', $since)
                ->count();

            if ($emailCount >= self::VELOCITY_LIMIT) {
                return true;
            }
        }

        if ($ip !== null && $ip !== '') {
            // IPs are not stored on orders directly; they live in the fraud event meta so we
            // never persist a raw IP column. Count distinct recent orders stamped with this IP.
            $ipCount = OrderEvent::query()
                ->where('agent', 'fraud')
                ->where('occurred_at', '>=', $since)
                ->where('meta->ip', $ip)
                ->distinct()
                ->count('order_id');

            // +1 for the order being assessed now (not yet stamped).
            if ($ipCount + 1 >= self::VELOCITY_LIMIT) {
                return true;
            }
        }

        return false;
    }

    /**
     * Duplicate: a *different* order with the same email + destination created recently —
     * a likely resubmission of the same application.
     */
    private function isDuplicate(Order $order): bool
    {
        if ($this->normaliseEmail($order->email) === null) {
            return false;
        }

        $since = Carbon::now()->subHours(self::VELOCITY_WINDOW_HOURS);

        return Order::query()
            ->where('email', $order->email)
            ->where('destination_name', $order->destination_name)
            ->when($order->exists, fn ($q) => $q->whereKeyNot($order->getKey()))
            ->where('created_at', '>=', $since)
            ->exists();
    }

    private function isDisposableEmail(string $email): bool
    {
        $domain = $this->emailDomain($email);

        return $domain !== null && in_array($domain, self::DISPOSABLE_DOMAINS, true);
    }

    /**
     * Structurally suspicious email: missing/malformed domain, an excessive number of plus-tags
     * or dot-noise in the local part (sub-address abuse), or a long random-looking local part.
     */
    private function isSuspiciousEmailPattern(string $email): bool
    {
        if (! str_contains($email, '@')) {
            return true;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return true;
        }

        [$local, $domain] = explode('@', $email, 2);

        if ($domain === '' || ! str_contains($domain, '.')) {
            return true;
        }

        // Multiple plus-tags or 3+ dots in the local part -> sub-address churn.
        if (substr_count($local, '+') >= 2 || substr_count($local, '.') >= 3) {
            return true;
        }

        // Long local part that is almost entirely digits/consonants with no vowel -> gibberish.
        if (strlen($local) >= 16 && preg_match('/[aeiou]/i', $local) === 0) {
            return true;
        }

        return false;
    }

    /**
     * Contradictory / mismatched intake. Each is a logical inconsistency in what the applicant
     * submitted (not a value judgement on the person).
     */
    private function hasContradictoryFields(Order $order): bool
    {
        // Declared a minor but gave no guardian.
        if ((bool) $order->is_minor && $this->blank($order->guardian_name)) {
            return true;
        }

        // Passport already expired at time of application.
        if ($order->passport_expiry instanceof Carbon
            && $order->passport_expiry->isBefore(Carbon::today())) {
            return true;
        }

        // Travel date in the past.
        if ($order->travel_date instanceof Carbon
            && $order->travel_date->isBefore(Carbon::today())) {
            return true;
        }

        return false;
    }

    /** Missing core contact data: no email AND/OR no phone makes follow-up/verification impossible. */
    private function hasMissingContact(Order $order): bool
    {
        return $this->blank($order->email) || $this->blank($order->phone);
    }

    // -----------------------------------------------------------------------------------
    // Event log
    // -----------------------------------------------------------------------------------

    /**
     * @param  list<string>  $flags
     */
    private function recordEvent(Order $order, int $score, array $flags, ?string $ip): OrderEvent
    {
        $reasons = implode(', ', $flags);

        return $order->events()->create([
            'occurred_at' => Carbon::now(),
            'agent' => 'fraud',
            'channel' => EventChannel::Internal->value,
            'type' => EventType::System->value,
            'text' => "Flagged for review by fraud guard (score {$score}): {$reasons}.",
            'meta' => [
                'score' => $score,
                'threshold' => self::THRESHOLD,
                'flags' => $flags,
                // IP is recorded ONLY in the audit event meta (never a raw orders column) so
                // velocity-by-IP works without widening the order's PII surface.
                'ip' => $ip,
            ],
            'email_event' => null,
        ]);
    }

    // -----------------------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------------------

    private function normaliseEmail(mixed $email): ?string
    {
        if ($email === null) {
            return null;
        }

        $clean = strtolower(trim((string) $email));

        return $clean === '' ? null : $clean;
    }

    private function emailDomain(string $email): ?string
    {
        if (! str_contains($email, '@')) {
            return null;
        }

        $domain = trim(substr(strrchr($email, '@') ?: '', 1));

        return $domain === '' ? null : $domain;
    }

    private function blank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }
}
