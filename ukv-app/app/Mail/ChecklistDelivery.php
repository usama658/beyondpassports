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
 * Transactional delivery of a public document-checklist request — the tailored list the user
 * explicitly asked us to send. There is NO Order, so (like CheckerAbandon) this does NOT extend
 * OrderMailable; it carries the ChecklistRequest directly.
 *
 * TRANSACTIONAL, NOT MARKETING: this email fulfils an explicit request and is sent regardless of
 * marketing_consent. It carries only what the user asked for (their checklist + their saved link +
 * an apply CTA) plus the mandatory compliance footer. Nurture/marketing emails (#23) are a separate
 * pipeline gated on marketing_consent — never piggy-backed onto this send.
 *
 * The view renders the stored SNAPSHOT (items), so the email is stable even if rules later change.
 * SerializesModels stores only the request id and re-fetches fresh on send.
 */
final class ChecklistDelivery extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly ChecklistRequest $request) {}

    private function dest(): string
    {
        $name = trim((string) ($this->request->destination?->name ?? ''));

        return $name !== '' ? $name : 'your destination';
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('ukv.base_url', ''), '/');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your {$this->dest()} document checklist",
        );
    }

    public function content(): Content
    {
        $base = $this->baseUrl();
        $destination = $this->dest();
        $inputs = is_array($this->request->inputs) ? $this->request->inputs : [];

        // Destination slug (for the apply CTA deep-link) — prefer the related model's slug.
        $slug = $this->request->destination?->slug;

        return new Content(
            markdown: 'emails.checklist',
            with: [
                'dest' => $destination,
                'items' => is_array($this->request->items) ? $this->request->items : [],
                'savedLink' => $base.'/checklist/'.$this->request->token,
                'applyUrl' => $base.'/apply'.($slug ? '?destination='.urlencode((string) $slug) : ''),
                'baseUrl' => $base,
                'travelDate' => $inputs['travel_date'] ?? null,
            ],
        );
    }
}
