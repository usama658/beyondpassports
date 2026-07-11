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
 * New apply-form submission — INTERNAL ops email to the business owner.
 *
 * Fires for BOTH lanes so a lead never sits silently: standard (traveller heading to payment)
 * and manual_review (needs a hand-checked quote + callback). Presents the FULL submitted intake
 * (the validated apply-form data) so ops sees every answer, not just the persisted order columns.
 */
final class NewApplication extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public readonly string $orderRef;

    public readonly string $lane;

    /** @var array<string, string> label => display value */
    public readonly array $fields;

    /** @var string digits-only phone for the tel: button */
    public readonly string $phoneDigits;

    /**
     * @param  array<string, mixed>  $intake  The validated apply-form data (ApplyRequest::validated()).
     */
    public function __construct(Order $order, array $intake = [])
    {
        $this->orderRef = (string) ($order->order_ref ?? '—');

        $lane = $order->eligibility;
        $laneValue = is_object($lane) && property_exists($lane, 'value') ? (string) $lane->value : (string) ($lane ?? '');
        $this->lane = $laneValue === 'manual_review'
            ? 'Manual review — needs a bespoke quote + callback'
            : ($laneValue === 'standard' ? 'Standard — traveller heading to payment' : ($laneValue ?: '—'));

        $this->phoneDigits = preg_replace('/[^0-9+]/', '', (string) ($intake['phone'] ?? $order->phone ?? '')) ?? '';
        $this->fields = $this->buildFields($order, $intake);
    }

    /**
     * Flatten the intake into an ordered label => value map for the email table.
     *
     * @param  array<string, mixed>  $in
     * @return array<string, string>
     */
    private function buildFields(Order $order, array $in): array
    {
        $purpose = [
            'tourist' => 'Tourism / holiday', 'business' => 'Business',
            'study' => 'Study', 'other' => 'Other',
        ];
        $nat = ['UK' => 'United Kingdom', 'Other' => 'Other nationality'];
        $res = ['UK' => 'United Kingdom', 'Other' => 'Outside the UK'];
        $status = [
            'citizen' => 'Citizen', 'permanent' => 'Settled / permanent resident',
            'visa_holder' => 'Visa holder', 'other' => 'Other',
        ];
        $entries = ['single' => 'Single entry', 'multiple' => 'Multiple entry'];

        $map = static fn (array $m, ?string $v): string => $v === null || $v === '' ? '—' : ($m[$v] ?? $v);
        $yesNo = static fn ($v): string => $v ? 'Yes' : 'No';
        $val = static fn ($v): string => $v === null || $v === '' ? '—' : (string) $v;
        $date = static function ($v): string {
            if (empty($v)) {
                return '—';
            }
            try {
                return Carbon::parse($v)->format('D j M Y');
            } catch (\Throwable) {
                return (string) $v;
            }
        };

        return array_filter([
            'Reference' => $this->orderRef,
            'Destination' => $val($in['destination'] ?? $order->destination_name),
            'Trip purpose' => $map($purpose, $in['trip_purpose'] ?? null),
            'Approx travel date' => $date($in['travel_date'] ?? $order->travel_date),
            'Entries needed' => $map($entries, $in['visa_entries'] ?? null),
            'Traveller name' => $val($in['applicant_name'] ?? $order->applicant_name),
            'Passport nationality' => $map($nat, $in['nationality'] ?? null),
            'Country of residence' => $map($res, $in['residence_country'] ?? null),
            'Residency status' => $map($status, $in['residency_status'] ?? null),
            'Dual nationality' => $val($in['dual_nationality'] ?? null),
            'Minor (under 18)' => $yesNo($in['is_minor'] ?? false),
            'Guardian name' => $val($in['guardian_name'] ?? null),
            'Previous visa refusal' => $yesNo($in['prior_refusal'] ?? false),
            'Passport expiry' => $date($in['passport_expiry'] ?? $order->passport_expiry),
            'Service tier' => ucfirst($val($in['tier'] ?? ($order->tier?->value ?? null))),
            'Email' => $val($in['email'] ?? $order->email),
            'Phone' => $val($in['phone'] ?? $order->phone),
            'UK postcode' => $val($in['postcode'] ?? $order->postcode),
        ], static fn ($v) => $v !== null && $v !== '');
    }

    public function envelope(): Envelope
    {
        $name = $this->fields['Traveller name'] ?? 'application';

        return new Envelope(
            subject: "[UKVisa] New application {$this->orderRef} — {$name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-application',
            with: [
                'orderRef' => $this->orderRef,
                'lane' => $this->lane,
                'fields' => $this->fields,
                'phoneDigits' => $this->phoneDigits,
            ],
        );
    }
}
