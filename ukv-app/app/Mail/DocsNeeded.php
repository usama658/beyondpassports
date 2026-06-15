<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * docs_needed — DORMANT in WP (template only).
 * Intended trigger: order enters the `awaiting_docs` stage.
 *
 * Carries the personalised document checklist (RequirementService shape) computed
 * by EmailService::sendDocsNeeded. The items are plain arrays, so they serialise
 * through the queued send. May be empty — the view then omits the checklist section.
 *
 * @param  list<array{document_key:string,label:string,note:?string,category:string,mandatory:bool}>  $items
 */
final class DocsNeeded extends OrderMailable
{
    public function __construct(Order $order, public readonly array $items = [])
    {
        parent::__construct($order);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Action needed: documents for your {$this->dest()} visa ({$this->ref()})",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.docs-needed',
            with: $this->mergeData() + ['items' => $this->items],
        );
    }
}
