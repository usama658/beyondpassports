<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Owner daily pending-actions digest (internal, queued).
 *
 * Unlike the customer lifecycle mailables (App\Mail\OrderMailable subclasses) this
 * is an INTERNAL ops email to the business owner, so it deliberately does NOT carry
 * the customer compliance footer.
 *
 * The command (App\Console\Commands\OwnerDigest) does the data gathering and decides
 * whether there is anything to send; this Mailable is a pure presenter over the
 * already-computed digest payload, so it stays cheap to queue and serialize.
 */
final class OwnerDigestMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{counts: array<string, int>, sections: array<string, array<int, array<string, string>>>, generated_at: string}  $digest
     */
    public function __construct(public readonly array $digest)
    {
    }

    public function envelope(): Envelope
    {
        $counts = $this->digest['counts'] ?? [];
        $pending = (int) ($counts['pending'] ?? 0);

        return new Envelope(
            subject: "[UKVisa] Owner digest — {$pending} order(s) need action",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.owner-digest',
            with: [
                'counts' => $this->digest['counts'] ?? [],
                'sections' => $this->digest['sections'] ?? [],
                'generatedAt' => $this->digest['generated_at'] ?? '',
            ],
        );
    }
}
