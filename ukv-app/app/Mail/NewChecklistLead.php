<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ChecklistRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Document-checklist lead — INTERNAL ops email to the owner.
 *
 * Fires when a motivated visitor opts to have their tailored checklist delivered (the
 * /checklist/{token}/send step), which is the first point a contact detail is captured. Mirrors
 * the other internal lead notifications; the HubSpot sync still runs alongside this.
 */
final class NewChecklistLead extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public readonly string $destination;

    public readonly string $email;

    public readonly string $phone;

    public readonly string $channels;

    public readonly bool $marketingConsent;

    public readonly string $savedLink;

    public function __construct(ChecklistRequest $checklist, string $savedLink = '')
    {
        $this->destination = (string) ($checklist->destination->name ?? $checklist->destination_name ?? '—');
        $this->email = (string) ($checklist->email ?? '');
        $this->phone = (string) ($checklist->phone ?? '');
        $chans = is_array($checklist->channels) ? $checklist->channels : [];
        $this->channels = $chans === [] ? '—' : implode(', ', $chans);
        $this->marketingConsent = (bool) ($checklist->marketing_consent ?? false);
        $this->savedLink = $savedLink;
    }

    public function envelope(): Envelope
    {
        $who = $this->email !== '' ? $this->email : ($this->phone !== '' ? $this->phone : 'new lead');

        return new Envelope(
            subject: "[UKVisa] Checklist lead — {$this->destination} ({$who})",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-checklist-lead',
            with: [
                'destination' => $this->destination,
                'email' => $this->email,
                'phone' => $this->phone,
                'channels' => $this->channels,
                'marketingConsent' => $this->marketingConsent,
                'savedLink' => $this->savedLink,
            ],
        );
    }
}
