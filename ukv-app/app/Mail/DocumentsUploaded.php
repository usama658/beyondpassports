<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Documents uploaded — INTERNAL ops ping to the owner.
 *
 * Fires when a paying customer uploads one or more documents via the public upload page, so the
 * team can act without watching the admin panel. Pure presenter over the order ref + accepted
 * file names (no customer PII beyond the ref, which the uploader already holds).
 *
 * @param  list<string>  $fileNames
 */
final class DocumentsUploaded extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public readonly string $orderRef;

    public readonly string $destination;

    public readonly int $count;

    /** @var list<string> */
    public readonly array $fileNames;

    public function __construct(Order $order, array $fileNames)
    {
        $this->orderRef = (string) ($order->order_ref ?? '—');
        $this->destination = (string) ($order->destination_name ?? '—');
        $names = array_values(array_filter(array_map(static fn ($n) => trim((string) $n), $fileNames)));
        $this->count = count($names);
        $this->fileNames = $names;
    }

    public function envelope(): Envelope
    {
        $plural = $this->count === 1 ? 'document' : 'documents';

        return new Envelope(
            subject: "[UKVisa] {$this->count} {$plural} uploaded — {$this->orderRef}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.documents-uploaded',
            with: [
                'orderRef' => $this->orderRef,
                'destination' => $this->destination,
                'count' => $this->count,
                'fileNames' => $this->fileNames,
            ],
        );
    }
}
