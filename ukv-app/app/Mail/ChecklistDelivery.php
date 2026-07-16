<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ChecklistRequest;
use App\Services\IcsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
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

    public function __construct(
        public readonly ChecklistRequest $request,
        public readonly bool $attachIcs = false,
        public readonly bool $includePdf = false,
        // Team copy: same snapshot, framed for the team to paste into the WhatsApp reply.
        public readonly bool $forTeam = false,
    ) {}

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
        if ($this->forTeam) {
            $inputs = is_array($this->request->inputs) ? $this->request->inputs : [];
            $when = ! empty($inputs['travel_date']) ? ' · travelling '.$inputs['travel_date'] : '';

            return new Envelope(
                subject: "[Share on WhatsApp] {$this->dest()} checklist ready{$when}",
            );
        }

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
                'forTeam' => $this->forTeam,
                'dest' => $destination,
                'items' => is_array($this->request->items) ? $this->request->items : [],
                'savedLink' => $base.'/checklist/'.$this->request->token,
                'applyUrl' => $base.'/apply'.($slug ? '?destination='.urlencode((string) $slug) : ''),
                'baseUrl' => $base,
                'travelDate' => $inputs['travel_date'] ?? null,
                // Printable/save-as-PDF link, only when the user asked to include a PDF.
                'printUrl' => $this->includePdf ? $base.'/checklist/'.$this->request->token.'/print' : null,
            ],
        );
    }

    /**
     * Attach the calendar reminder (.ics) when the user ticked "Calendar reminder". Built from the
     * destination's processing_days; skipped silently if there is no travel date to anchor it.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->attachIcs) {
            return [];
        }

        $inputs = is_array($this->request->inputs) ? $this->request->inputs : [];

        $ics = app(IcsService::class)->buildForChecklist(
            $this->dest(),
            $inputs['travel_date'] ?? null,
            $this->request->destination?->processing_days,
        );

        if ($ics === null) {
            return [];
        }

        return [
            Attachment::fromData(fn (): string => $ics, 'ukvisaco-reminder.ics')
                ->withMime('text/calendar'),
        ];
    }
}
