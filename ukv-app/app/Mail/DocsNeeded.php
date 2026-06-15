<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * docs_needed — DORMANT in WP (template only).
 * Intended trigger: order enters the `awaiting_docs` stage.
 */
final class DocsNeeded extends OrderMailable
{
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Action needed: documents for your {$this->dest()} visa ({$this->ref()})",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.docs-needed',
            with: $this->mergeData(),
        );
    }
}
