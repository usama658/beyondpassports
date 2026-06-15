<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * refunded — LIVE. Trigger: refund processed (status set to `refunded`).
 * Compliance copy: government fee is non-refundable (only service fee refunded).
 */
final class Refunded extends OrderMailable
{
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your refund for the {$this->dest()} application ({$this->ref()})",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.refunded',
            with: $this->mergeData(),
        );
    }
}
