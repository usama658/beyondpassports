<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * decision — LIVE. Trigger: status transitions to `awaiting_decision`.
 * Deliberately content-light: the decision itself is delivered by a human on
 * phone/WhatsApp, never asserted in the email (no approval implied).
 */
final class DecisionMade extends OrderMailable
{
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Update on your {$this->dest()} visa decision ({$this->ref()})",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.decision',
            with: $this->mergeData(),
        );
    }
}
