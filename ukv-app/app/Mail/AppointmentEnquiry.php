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
 * Landing-page appointment / eligibility enquiry — INTERNAL ops email to the owner.
 *
 * The LP form's primary action still opens WhatsApp client-side; this is the belt-and-braces
 * server record so a lead is captured even if the traveller never sends the WhatsApp message.
 * Pure presenter over the validated name + phone (no Order, no DB row).
 */
final class AppointmentEnquiry extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public readonly string $leadName;

    public readonly string $leadPhone;

    public readonly string $leadEmail;

    public readonly string $source;

    public function __construct(string $name, string $phone, string $source, string $email = '')
    {
        $this->leadName = trim($name) !== '' ? trim($name) : '(no name given)';
        $this->leadPhone = trim($phone) !== '' ? trim($phone) : '(no number given)';
        $this->leadEmail = trim($email) !== '' ? trim($email) : '(no email given)';
        $this->source = trim($source) !== '' ? trim($source) : 'landing page';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[UKVisa] Appointment enquiry — {$this->leadName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointment-enquiry',
            with: [
                'leadName' => $this->leadName,
                'leadPhone' => $this->leadPhone,
                'leadEmail' => $this->leadEmail,
                'source' => $this->source,
            ],
        );
    }
}
