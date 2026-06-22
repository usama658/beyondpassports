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
        // BCC Trustpilot's Automatic Feedback alias so a review invite fires alongside our own
        // post-delivery review ask. Configured value only; blank = no BCC. (Only this email.)
        $inviteBcc = config('ukv.trustpilot.invite_bcc');

        return new Envelope(
            subject: "How did we do? Your {$this->dest()} visa ({$this->ref()})",
            bcc: array_values(array_filter([$inviteBcc])),
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
