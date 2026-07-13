<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * order_paid — payment receipt / order confirmation.
 * Intended trigger: order reaches the `paid` entry stage.
 * Replies route to the billing inbox (config ukv.email_billing → forwards to hello@).
 */
final class OrderPaid extends OrderMailable
{
    public function envelope(): Envelope
    {
        $billing = (string) config('ukv.email_billing');

        return new Envelope(
            subject: "Your {$this->dest()} visa order is confirmed ({$this->ref()})",
            replyTo: $billing !== '' ? [new Address($billing, 'Beyond Passports Billing')] : [],
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
