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
 * checker_abandon — DORMANT in WP (cron exists but lead source is an empty
 * placeholder). Lead-only: there is NO order, ref or guaranteed name — only a
 * captured email + destination from an abandoned visa-checker submission.
 *
 * Because it has no Order, this Mailable does NOT extend OrderMailable; it takes
 * the lead's name + destination directly and applies the same WP fallbacks.
 */
final class CheckerAbandon extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public readonly string $name;

    public readonly string $dest;

    public function __construct(?string $name, ?string $dest)
    {
        $this->name = ($name !== null && trim($name) !== '') ? trim($name) : 'there';
        $this->dest = ($dest !== null && trim($dest) !== '') ? trim($dest) : 'your destination';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Finish your {$this->dest} visa check — we can help",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.checker-abandon',
            with: [
                'name' => $this->name,
                'dest' => $this->dest,
            ],
        );
    }
}
