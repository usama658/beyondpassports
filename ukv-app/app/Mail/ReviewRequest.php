<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * review_request — LIVE. Trigger: fired immediately after `delivered`
 * on the same `delivered`/`won` transition.
 */
final class ReviewRequest extends OrderMailable
{
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "How did we do? Your {$this->dest()} visa ({$this->ref()})",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.review-request',
            with: $this->mergeData(),
        );
    }
}
