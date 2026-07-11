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
 * New apply-form submission — INTERNAL ops email to the business owner.
 *
 * Fires for BOTH lanes so a lead never sits silently: standard (traveller heading to payment)
 * and manual_review (needs a hand-checked quote + callback). Pure presenter over scalar order
 * fields — no compliance footer (internal), safe to send inline from ApplyController.
 */
final class NewApplication extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public readonly string $orderRef;

    public readonly string $applicantName;

    public readonly string $email;

    public readonly string $phone;

    public readonly string $destination;

    public readonly string $tier;

    public readonly string $lane;

    public readonly string $travelDate;

    public function __construct(Order $order)
    {
        $this->orderRef = (string) ($order->order_ref ?? '—');
        $this->applicantName = trim((string) ($order->applicant_name ?? $order->name ?? '')) ?: '(no name)';
        $this->email = (string) ($order->email ?? '');
        $this->phone = (string) ($order->phone ?? '');
        $this->destination = (string) ($order->destination_name ?? '—');
        $tier = $order->tier;
        $this->tier = is_object($tier) && property_exists($tier, 'value') ? (string) $tier->value : (string) ($tier ?? '—');
        $lane = $order->eligibility;
        $laneValue = is_object($lane) && property_exists($lane, 'value') ? (string) $lane->value : (string) ($lane ?? '');
        $this->lane = $laneValue === 'manual_review'
            ? 'Manual review — needs a bespoke quote + callback'
            : ($laneValue === 'standard' ? 'Standard — traveller heading to payment' : ($laneValue ?: '—'));
        $this->travelDate = $order->travel_date ? $order->travel_date->format('D j M Y') : '—';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[UKVisa] New application {$this->orderRef} — {$this->applicantName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-application',
            with: [
                'orderRef' => $this->orderRef,
                'applicantName' => $this->applicantName,
                'email' => $this->email,
                'phone' => $this->phone,
                'destination' => $this->destination,
                'tier' => $this->tier,
                'lane' => $this->lane,
                'travelDate' => $this->travelDate,
            ],
        );
    }
}
