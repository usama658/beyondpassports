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
 * Newsletter opt-in — INTERNAL ops notification to the business owner.
 *
 * Like ContactEnquiry this is an internal notification (no customer compliance footer). It is a
 * pure presenter over the validated email + consent flag: no DB row, no Order. Sent inline from
 * SubscribeController so a stalled queue worker never silently swallows a consented opt-in.
 */
final class NewSubscriber extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public readonly string $subscriberEmail;

    public readonly string $capturedAt;

    public function __construct(string $email, string $capturedAt)
    {
        $this->subscriberEmail = trim($email);
        $this->capturedAt = $capturedAt;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[UKVisa] New newsletter opt-in — {$this->subscriberEmail}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-subscriber',
            with: [
                'subscriberEmail' => $this->subscriberEmail,
                'capturedAt' => $this->capturedAt,
            ],
        );
    }
}
