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
 * Contact / callback enquiry — INTERNAL ops email to the business owner (queued).
 *
 * Like OwnerDigestMail this is an internal notification, NOT a customer-facing message, so it
 * deliberately carries no customer compliance footer. It is a pure presenter over the validated
 * form fields (no Order, no DB row) — cheap to queue and serialize.
 */
final class ContactEnquiry extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public readonly string $contactName;

    public readonly string $contactPhone;

    public readonly string $bestTime;

    public readonly string $contactMessage;

    public function __construct(string $name, string $phone, ?string $bestTime, ?string $message)
    {
        $this->contactName = trim($name);
        $this->contactPhone = trim($phone);
        $this->bestTime = ($bestTime !== null && trim($bestTime) !== '') ? trim($bestTime) : 'Any time, Mon–Sat 9–6';
        $this->contactMessage = ($message !== null && trim($message) !== '') ? trim($message) : '(no message left)';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[UKVisa] Callback request — {$this->contactName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-enquiry',
            with: [
                'contactName' => $this->contactName,
                'contactPhone' => $this->contactPhone,
                'bestTime' => $this->bestTime,
                'contactMessage' => $this->contactMessage,
            ],
        );
    }
}
