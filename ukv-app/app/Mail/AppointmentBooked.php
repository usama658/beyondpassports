<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * appointment_booked — DORMANT in WP (template only).
 * Intended trigger: an appointment is booked for the order.
 */
final class AppointmentBooked extends OrderMailable
{
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your appointment is booked — {$this->dest()} visa ({$this->ref()})",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointment-booked',
            with: $this->mergeData(),
        );
    }
}
