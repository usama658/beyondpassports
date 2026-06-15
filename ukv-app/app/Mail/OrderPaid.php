<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * order_paid — DORMANT in WP (template only; no hook fires it yet).
 * Intended trigger: order reaches the `paid` entry stage.
 */
final class OrderPaid extends OrderMailable
{
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your {$this->dest()} visa order is confirmed ({$this->ref()})",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-paid',
            with: $this->mergeData(),
        );
    }
}
