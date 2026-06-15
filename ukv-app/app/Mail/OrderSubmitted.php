<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * submitted — LIVE. Trigger: status transitions to `submitted`.
 */
final class OrderSubmitted extends OrderMailable
{
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your {$this->dest()} visa application has been submitted ({$this->ref()})",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.submitted',
            with: $this->mergeData(),
        );
    }
}
