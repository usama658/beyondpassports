<?php

namespace Database\Seeders;

use App\Enums\EligibilityLane;
use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Enums\OrderBlocker;
use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\OrderTier;
use App\Enums\ResidencyStatus;
use App\Enums\TripPurpose;
use App\Models\Destination;
use App\Models\Order;
use App\Models\OrderEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds ~6 demo orders spanning pipeline statuses and eligibility lanes so the
 * admin cockpit and public tracker show realistic data.
 *
 * Guard: all demo orders use the @demo.ukv email domain. If any such order
 * already exists, the seeder skips entirely (re-run safe). order_ref is set
 * explicitly because DatabaseSeeder runs WithoutModelEvents (the model's
 * auto-ref hook does not fire during seeding).
 *
 * PLACEHOLDER data — names, refs and fees are illustrative demo content.
 */
class DemoOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Re-run guard: skip if demo orders already present.
        if (Order::where('email', 'like', '%@demo.ukv')->exists()) {
            $this->command?->info('DemoOrderSeeder: demo orders already exist — skipping.');

            return;
        }

        // Destination lookups (by slug). Skip if catalogue not seeded.
        $dest = Destination::whereIn('slug', [
            'turkey', 'egypt', 'india', 'usa-esta', 'australia-eta', 'vietnam',
        ])->get()->keyBy('slug');

        if ($dest->isEmpty()) {
            $this->command?->warn('DemoOrderSeeder: no destinations found — run DestinationSeeder first. Skipping.');

            return;
        }

        $now = Carbon::now();
        $year = $now->format('Y');
        $seq = 0;

        // Helper to build base attributes from a destination + tier.
        $price = function (Destination $d, OrderTier $tier): array {
            $service = match ($tier) {
                OrderTier::Express => (float) $d->tier_express_gbp,
                OrderTier::Premium => (float) $d->tier_premium_gbp,
                OrderTier::Standard => (float) $d->tier_standard_gbp,
            };
            $govt = (float) $d->govt_fee_gbp;

            return [
                'service_fee' => $service,
                'govt_fee' => $govt,
                'total' => round($service + $govt, 2),
            ];
        };

        $orders = [
            // 1. Fresh paid order, standard lane — awaiting docs.
            [
                'slug' => 'turkey',
                'tier' => OrderTier::Standard,
                'attrs' => [
                    'name' => 'James Whitfield',
                    'email' => 'james.whitfield@demo.ukv',
                    'status' => OrderStatus::AwaitingDocs,
                    'status_last' => OrderStatus::Paid->value,
                    'blocker' => OrderBlocker::DocsMissing,
                    'priority' => OrderPriority::Normal,
                    'eligibility' => EligibilityLane::Standard,
                    'nationality' => 'British',
                    'residence_country' => 'United Kingdom',
                    'residency_status' => ResidencyStatus::Citizen,
                    'trip_purpose' => TripPurpose::Tourist,
                    'next_action' => 'Chase passport bio page upload',
                    'next_due' => $now->copy()->addDays(2)->toDateString(),
                    'travel_date' => $now->copy()->addDays(40)->toDateString(),
                    'passport_expiry' => $now->copy()->addYears(4)->toDateString(),
                    'required_docs_count' => 3,
                ],
                'events' => [
                    ['days' => -1, 'agent' => 'system', 'channel' => EventChannel::Internal, 'type' => EventType::System, 'text' => 'Order paid — Standard tier. Pipeline opened.'],
                    ['days' => 0, 'agent' => 'system', 'channel' => EventChannel::Email, 'type' => EventType::Email, 'text' => 'Welcome + document checklist email sent.', 'email_event' => 'welcome_docs'],
                ],
            ],

            // 2. Manual-review lane — eligibility blocked (prior refusal).
            [
                'slug' => 'india',
                'tier' => OrderTier::Express,
                'attrs' => [
                    'name' => 'Priya Nair',
                    'email' => 'priya.nair@demo.ukv',
                    'status' => OrderStatus::Paid,
                    'status_last' => OrderStatus::Paid->value,
                    'blocker' => OrderBlocker::Eligibility,
                    'priority' => OrderPriority::High,
                    'eligibility' => EligibilityLane::ManualReview,
                    'eligibility_note' => 'Prior refusal declared — needs caseworker review before submission.',
                    'nationality' => 'British',
                    'residence_country' => 'United Kingdom',
                    'residency_status' => ResidencyStatus::Citizen,
                    'trip_purpose' => TripPurpose::Business,
                    'prior_refusal' => true,
                    'risk_flag' => true,
                    'next_action' => 'Caseworker eligibility review',
                    'next_due' => $now->copy()->addDay()->toDateString(),
                    'travel_date' => $now->copy()->addDays(55)->toDateString(),
                    'passport_expiry' => $now->copy()->addYears(6)->toDateString(),
                    'required_docs_count' => 3,
                ],
                'events' => [
                    ['days' => 0, 'agent' => 'system', 'channel' => EventChannel::Internal, 'type' => EventType::System, 'text' => 'Routed to manual review: prior refusal flag set.'],
                ],
            ],

            // 3. In document review — standard lane.
            [
                'slug' => 'egypt',
                'tier' => OrderTier::Standard,
                'attrs' => [
                    'name' => 'Tom Brady',
                    'email' => 'tom.brady@demo.ukv',
                    'status' => OrderStatus::DocReview,
                    'status_last' => OrderStatus::AwaitingDocs->value,
                    'blocker' => OrderBlocker::None,
                    'priority' => OrderPriority::Normal,
                    'eligibility' => EligibilityLane::Standard,
                    'nationality' => 'British',
                    'residence_country' => 'United Kingdom',
                    'residency_status' => ResidencyStatus::Citizen,
                    'trip_purpose' => TripPurpose::Tourist,
                    'next_action' => 'QA review of uploaded documents',
                    'next_due' => $now->copy()->addDay()->toDateString(),
                    'travel_date' => $now->copy()->addDays(25)->toDateString(),
                    'passport_expiry' => $now->copy()->addYears(2)->toDateString(),
                    'required_docs_count' => 3,
                ],
                'events' => [
                    ['days' => -3, 'agent' => 'system', 'channel' => EventChannel::Upload, 'type' => EventType::System, 'text' => 'All 3 required documents uploaded by customer.'],
                    ['days' => 0, 'agent' => 'A. Hassan', 'channel' => EventChannel::Internal, 'type' => EventType::Note, 'text' => 'Started QA review — photo looks within spec.'],
                ],
            ],

            // 4. Submitted to government — awaiting decision (express).
            [
                'slug' => 'vietnam',
                'tier' => OrderTier::Express,
                'attrs' => [
                    'name' => 'Sarah Okonkwo',
                    'email' => 'sarah.okonkwo@demo.ukv',
                    'status' => OrderStatus::AwaitingDecision,
                    'status_last' => OrderStatus::Submitted->value,
                    'blocker' => OrderBlocker::None,
                    'priority' => OrderPriority::Normal,
                    'eligibility' => EligibilityLane::Cleared,
                    'nationality' => 'British',
                    'residence_country' => 'United Kingdom',
                    'residency_status' => ResidencyStatus::Citizen,
                    'trip_purpose' => TripPurpose::Tourist,
                    'govt_ref' => 'VN-EVISA-884213',
                    'govt_fee_paid' => true,
                    'govt_fee_paid_at' => $now->copy()->subDays(4),
                    'qa_signed_off' => true,
                    'next_action' => 'Await government decision',
                    'next_due' => $now->copy()->addDays(3)->toDateString(),
                    'travel_date' => $now->copy()->addDays(20)->toDateString(),
                    'passport_expiry' => $now->copy()->addYears(5)->toDateString(),
                    'required_docs_count' => 3,
                ],
                'events' => [
                    ['days' => -4, 'agent' => 'system', 'channel' => EventChannel::Internal, 'type' => EventType::StageChange, 'text' => 'Application submitted to Vietnamese e-visa portal.'],
                    ['days' => -4, 'agent' => 'system', 'channel' => EventChannel::Email, 'type' => EventType::Email, 'text' => 'Submission confirmation email sent.', 'email_event' => 'submitted'],
                ],
            ],

            // 5. Won — delivered & approved (premium).
            [
                'slug' => 'australia-eta',
                'tier' => OrderTier::Premium,
                'attrs' => [
                    'name' => 'Daniel Cheng',
                    'email' => 'daniel.cheng@demo.ukv',
                    'status' => OrderStatus::Won,
                    'status_last' => OrderStatus::Delivered->value,
                    'blocker' => OrderBlocker::None,
                    'priority' => OrderPriority::Normal,
                    'eligibility' => EligibilityLane::Cleared,
                    'nationality' => 'British',
                    'residence_country' => 'United Kingdom',
                    'residency_status' => ResidencyStatus::Citizen,
                    'trip_purpose' => TripPurpose::Tourist,
                    'govt_ref' => 'AU-ETA-552097',
                    'govt_fee_paid' => true,
                    'govt_fee_paid_at' => $now->copy()->subDays(12),
                    'qa_signed_off' => true,
                    'story_consent' => true,
                    'travel_date' => $now->copy()->addDays(60)->toDateString(),
                    'passport_expiry' => $now->copy()->addYears(7)->toDateString(),
                    'required_docs_count' => 3,
                    'closed_at' => $now->copy()->subDays(9),
                ],
                'events' => [
                    ['days' => -10, 'agent' => 'system', 'channel' => EventChannel::Internal, 'type' => EventType::StageChange, 'text' => 'ETA granted by Australian authorities.'],
                    ['days' => -9, 'agent' => 'system', 'channel' => EventChannel::Email, 'type' => EventType::Email, 'text' => 'Approval + outcome email sent to customer.', 'email_event' => 'approved'],
                ],
            ],

            // 6. Refunded — service fee returned, govt fee not charged.
            [
                'slug' => 'usa-esta',
                'tier' => OrderTier::Standard,
                'attrs' => [
                    'name' => 'Megan Fields',
                    'email' => 'megan.fields@demo.ukv',
                    'status' => OrderStatus::Refunded,
                    'status_last' => OrderStatus::Paid->value,
                    'blocker' => OrderBlocker::None,
                    'priority' => OrderPriority::Normal,
                    'eligibility' => EligibilityLane::Referred,
                    'eligibility_note' => 'Customer cancelled before submission — full service-fee refund.',
                    'nationality' => 'British',
                    'residence_country' => 'United Kingdom',
                    'residency_status' => ResidencyStatus::Citizen,
                    'trip_purpose' => TripPurpose::Tourist,
                    'refund_reason' => 'Customer cancelled trip',
                    'travel_date' => null,
                    'passport_expiry' => $now->copy()->addYears(3)->toDateString(),
                    'required_docs_count' => 3,
                    'refunded_at' => $now->copy()->subDays(2),
                    'closed_at' => $now->copy()->subDays(2),
                ],
                'events' => [
                    ['days' => -2, 'agent' => 'system', 'channel' => EventChannel::Internal, 'type' => EventType::System, 'text' => 'Service fee refunded; government fee was never charged.'],
                ],
            ],
        ];

        foreach ($orders as $row) {
            $d = $dest->get($row['slug']);
            if (! $d) {
                continue; // destination missing — skip this demo order
            }

            $seq++;
            $tier = $row['tier'];
            $pricing = $price($d, $tier);

            $attrs = array_merge([
                'order_ref' => sprintf('UKV-%s-%06d', $year, 900000 + $seq), // demo ref band, avoids clashing with live sequence
                'destination_id' => $d->id,
                'destination_name' => $d->name,
                'tier' => $tier,
            ], $pricing, $row['attrs']);

            // Refunded order returns the service fee only.
            if (($attrs['status'] ?? null) === OrderStatus::Refunded) {
                $attrs['refund_amount'] = $pricing['service_fee'];
            }

            $order = Order::create($attrs);

            foreach ($row['events'] as $e) {
                OrderEvent::create([
                    'order_id' => $order->id,
                    'occurred_at' => $now->copy()->addDays($e['days']),
                    'agent' => $e['agent'],
                    'channel' => $e['channel'],
                    'type' => $e['type'],
                    'text' => $e['text'],
                    'email_event' => $e['email_event'] ?? null,
                ]);
            }
        }

        $this->command?->info('DemoOrderSeeder: created '.$seq.' demo orders (@demo.ukv).');
    }
}
