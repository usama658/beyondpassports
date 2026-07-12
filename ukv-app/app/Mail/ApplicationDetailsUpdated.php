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
use Illuminate\Support\Carbon;

/**
 * Application details updated — INTERNAL ops ping to the owner.
 *
 * Fires when a customer saves the post-pay document-detail form (employment, accommodation,
 * funding, return date, payer) against their order. May fire more than once as the form is
 * submitted incrementally; each is a real customer action, so a light ping is intended.
 * Pure presenter over the order ref + the just-saved detail fields.
 */
final class ApplicationDetailsUpdated extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public readonly string $orderRef;

    public readonly string $destination;

    /** @var array<string, string> label => value */
    public readonly array $fields;

    /**
     * @param  array<string, mixed>  $details  The validated detail fields (DocumentDetailRequest::detailAttributes()).
     */
    public function __construct(Order $order, array $details)
    {
        $this->orderRef = (string) ($order->order_ref ?? '—');
        $this->destination = (string) ($order->destination_name ?? '—');

        $employment = [
            'employed' => 'Employed', 'self_employed' => 'Self-employed', 'student' => 'Student',
            'retired' => 'Retired', 'unemployed' => 'Unemployed', 'other' => 'Other',
        ];
        $accommodation = [
            'hotel' => 'Hotel', 'host' => 'Staying with a host', 'own_property' => 'Own property', 'other' => 'Other',
        ];
        $funding = ['self' => 'Self-funded', 'sponsored' => 'Sponsored'];

        $map = static fn (array $m, $v): ?string => ($v === null || $v === '') ? null : ($m[$v] ?? (string) $v);
        $date = static function ($v): ?string {
            if (empty($v)) {
                return null;
            }
            try {
                return Carbon::parse($v)->format('D j M Y');
            } catch (\Throwable) {
                return (string) $v;
            }
        };

        $payer = $details['payer_is_applicant'] ?? null;

        $this->fields = array_filter([
            'Reference' => $this->orderRef,
            'Destination' => $this->destination,
            'Employment status' => $map($employment, $details['employment_status'] ?? null),
            'Accommodation' => $map($accommodation, $details['accommodation_type'] ?? null),
            'Funding source' => $map($funding, $details['funding_source'] ?? null),
            'Return date' => $date($details['return_date'] ?? null),
            'Traveller is the payer' => $payer === null ? null : ($payer ? 'Yes' : 'No'),
        ], static fn ($v) => $v !== null && $v !== '');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[UKVisa] Application details updated — {$this->orderRef}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.application-details-updated',
            with: ['fields' => $this->fields],
        );
    }
}
