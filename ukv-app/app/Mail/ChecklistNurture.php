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
 * Nurture follow-up for a checklist-taker who consented to marketing. Sent once, a couple of days
 * after they built a checklist, nudging them from "I have the list" to "hand it to us". Links back to
 * their saved checklist and to the apply funnel (destination preselected). Fires only for
 * marketing_consent leads; the scheduled command enforces that + one-shot delivery.
 */
final class ChecklistNurture extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public readonly string $destination;

    public readonly string $savedLink;

    public readonly string $applyUrl;

    public function __construct(ChecklistRequest $checklist)
    {
        $this->destination = (string) ($checklist->destination?->name
            ?? ($checklist->inputs['destination'] ?? 'your trip'));
        $this->savedLink = route('checklist.show', $checklist);
        $this->applyUrl = url('/apply').(
            $checklist->destination
                ? '?destination='.rawurlencode((string) $checklist->destination->name)
                : ''
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Ready to hand your '.$this->destination.' checklist to us?');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.checklist-nurture');
    }
}
