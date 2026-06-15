<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * delivered — LIVE. Trigger: status transitions to `delivered` OR `won`.
 * Fired together with ReviewRequest on the same transition.
 */
final class Delivered extends OrderMailable
{
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your {$this->dest()} visa is ready ({$this->ref()})",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.delivered',
            with: $this->mergeData(),
        );
    }
}
