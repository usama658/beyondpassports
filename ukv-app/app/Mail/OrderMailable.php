<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Shared base for every lifecycle email.
 *
 * Each concrete Mailable is one event from docs/superpowers/port/03-emails.md.
 * The constructor takes the Order; merge fields are read from the order with the
 * SAME fallbacks as the WP source:
 *   - name → "there"            (ukv_name fallback)
 *   - dest → "your destination" (ukv_destination fallback)
 *   - ref  → "—"               (ukv_order_ref fallback)
 *
 * The status/track link and help links are built from config('ukv.base_url'),
 * NOT a hardcoded domain. See viewData().
 *
 * All concrete mailables implement ShouldQueue (queued send) and reuse the
 * mandatory compliance footer via the shared `emails.partials.footer` partial,
 * which every view includes so it can never be omitted.
 *
 * Idempotency, audit log and journey note are NOT here — they live in
 * App\Services\EmailService so a Mailable can never double-fire.
 */
abstract class OrderMailable extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    // NOTE: not `readonly` — Laravel's SerializesModels restores this property when a queued
    // mailable is unserialized, and PHP forbids re-initialising a readonly promoted property
    // from the child class scope (fatal "Cannot initialize readonly property ... from scope").
    public function __construct(public Order $order)
    {
    }

    /**
     * Customer name, with the WP "there" fallback.
     */
    protected function name(): string
    {
        $name = trim((string) ($this->order->name ?? ''));

        return $name !== '' ? $name : 'there';
    }

    /**
     * Destination display name, with the WP "your destination" fallback.
     */
    protected function dest(): string
    {
        $dest = trim((string) ($this->order->destination_name ?? ''));

        return $dest !== '' ? $dest : 'your destination';
    }

    /**
     * Order reference, with the WP "—" fallback.
     */
    protected function ref(): string
    {
        $ref = trim((string) ($this->order->order_ref ?? ''));

        return $ref !== '' ? $ref : '—';
    }

    /**
     * Base app URL from config (config/ukv.php → 'base_url'), trailing slash trimmed.
     * Links in views are built as {base}/track, {base}/how-it-works, etc.
     */
    protected function baseUrl(): string
    {
        return rtrim((string) config('ukv.base_url', ''), '/');
    }

    /**
     * Customer email (empty string if none).
     */
    protected function email(): string
    {
        return trim((string) ($this->order->email ?? ''));
    }

    /**
     * Absolute URL to the public document-upload page, prefilled with this order's ref + email
     * so the customer lands ready to authenticate. Replaces the old dead /how-to-send-documents/.
     */
    protected function documentsUrl(): string
    {
        $query = http_build_query(array_filter([
            'ref' => $this->ref() === '—' ? null : $this->ref(),
            'email' => $this->email() !== '' ? $this->email() : null,
        ]));

        return $this->baseUrl().'/documents'.($query !== '' ? '?'.$query : '');
    }

    /**
     * Merge data shared by every lifecycle email view.
     *
     * @return array<string, string>
     */
    protected function mergeData(): array
    {
        return [
            'name' => $this->name(),
            'dest' => $this->dest(),
            'ref' => $this->ref(),
            'baseUrl' => $this->baseUrl(),
            'email' => $this->email(),
            'documentsUrl' => $this->documentsUrl(),
        ];
    }
}
