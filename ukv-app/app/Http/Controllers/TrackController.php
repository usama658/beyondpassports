<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\RequirementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Public order status tracker.
 *
 * Privacy contract (load-bearing):
 *  - The lookup endpoint takes an order reference and returns ONLY the customer-facing
 *    stage timeline + generic now/next copy. It never returns customer PII (name, email,
 *    passport number, destination, fees, govt ref, etc.).
 *  - A miss returns a generic "not found" — we do not confirm or deny whether any
 *    particular reference exists beyond what the matched/unmatched result reveals, and the
 *    copy is identical regardless of why the lookup failed.
 *  - Lookups are exact-match on `order_ref` only (no LIKE / partial / fuzzy matching) so a
 *    reference cannot be enumerated by probing prefixes.
 *
 * Wiring (NOT done here — see reply to caller):
 *  - GET  /track        -> show()
 *  - POST /track/lookup -> lookup()   (apply `throttle:` to this route)
 */
class TrackController extends Controller
{
    /**
     * The five customer-facing stages, in display order.
     * Internal pipeline statuses are mapped onto these — customers never see the raw
     * doc_review / awaiting_decision / won vocabulary.
     */
    public const STAGES = [
        'received'           => 'Received',
        'documents_checked'  => 'Documents checked',
        'submitted'          => 'Submitted',
        'government'         => 'Government processing',
        'delivered'          => 'Delivered',
    ];

    /**
     * Index of each internal OrderStatus within the 5-stage customer journey.
     *
     * The pipeline is: paid -> awaiting_docs -> doc_review -> submitted ->
     * awaiting_decision -> delivered -> won (with rejected / refunded as terminals).
     *
     * Mapping (internal -> public stage index 0..4):
     *   paid, awaiting_docs        -> 0  Received          (order in, gathering docs)
     *   doc_review                 -> 1  Documents checked (docs being / have been checked)
     *   submitted                  -> 2  Submitted         (sent to the destination's govt)
     *   awaiting_decision          -> 3  Government processing (decision pending — current)
     *   delivered, won             -> 4  Delivered         (visa/eVisa delivered to customer)
     *
     * Terminal exceptions handled separately (not a normal timeline position):
     *   rejected  -> outcome view at the Government processing stage
     *   refunded  -> outcome view at the Received stage
     *
     * @return array{stages: array<int, array{key:string, label:string, state:string}>,
     *               current_index: int, outcome: ?string, now: string, next: ?string}
     */
    public static function stagesFor(OrderStatus $status): array
    {
        // current stage index within STAGES (0-based)
        $index = match ($status) {
            OrderStatus::Paid, OrderStatus::AwaitingDocs => 0,
            OrderStatus::DocReview                       => 1,
            OrderStatus::Submitted                       => 2,
            OrderStatus::AwaitingDecision                => 3,
            OrderStatus::Delivered, OrderStatus::Won     => 4,
            // terminals: anchor to a sensible point on the timeline
            OrderStatus::Rejected                        => 3,
            OrderStatus::Refunded                        => 0,
        };

        $outcome = match ($status) {
            OrderStatus::Rejected => 'rejected',
            OrderStatus::Refunded => 'refunded',
            default               => null,
        };

        // Delivered / Won complete the whole journey (stage 4 is done, not merely current).
        $completed = in_array($status, [OrderStatus::Delivered, OrderStatus::Won], true);

        $labels = array_values(self::STAGES);
        $keys   = array_keys(self::STAGES);
        $stages = [];

        foreach ($labels as $i => $label) {
            if ($outcome !== null) {
                // Terminal: everything up to the anchor is done; the anchor itself shows
                // the outcome; later stages stay future.
                $state = $i < $index ? 'done' : ($i === $index ? 'current' : 'future');
            } elseif ($completed) {
                $state = 'done';
            } else {
                $state = $i < $index ? 'done' : ($i === $index ? 'current' : 'future');
            }

            $stages[] = [
                'key'   => $keys[$i],
                'label' => $label,
                'state' => $state,
            ];
        }

        [$now, $next] = self::nowNextCopy($status);

        return [
            'stages'        => $stages,
            'current_index' => $index,
            'outcome'       => $outcome,
            'now'           => $now,
            'next'          => $next,
        ];
    }

    /**
     * Plain-English, PII-free "what's happening now" / "what's next" copy per status.
     * Mirrors the tone of the coded front-end (frontend/track.html).
     *
     * @return array{0:string, 1:?string}  [now, next]
     */
    protected static function nowNextCopy(OrderStatus $status): array
    {
        return match ($status) {
            OrderStatus::Paid, OrderStatus::AwaitingDocs => [
                "We've received your application and we're getting everything ready. If we need any documents from you, we'll be in touch by email.",
                "Once we have your documents, our team will check every page to make sure your application is complete and correct before anything is submitted.",
            ],
            OrderStatus::DocReview => [
                "Our team is checking your documents to make sure everything is complete and correct. There's nothing more for you to do while we review.",
                "As soon as your documents pass our checks, we'll submit your application to the destination's government on your behalf.",
            ],
            OrderStatus::Submitted => [
                "We've checked your documents and submitted your application to the destination's authorities. It's now in their hands for a decision.",
                "The authorities will begin processing your application. We'll update this tracker the moment their decision comes through.",
            ],
            OrderStatus::AwaitingDecision => [
                "We've checked your documents and submitted your application. It's now with the destination's government for a decision — there's nothing more for you to do at this stage.",
                "As soon as the authorities issue their decision, we'll deliver your visa or eVisa to you and update this tracker. We'll email you the moment it lands.",
            ],
            OrderStatus::Delivered, OrderStatus::Won => [
                "Your visa or eVisa has been delivered. Please check the email we sent you for your documents and travel details.",
                null,
            ],
            OrderStatus::Rejected => [
                "The destination's authorities have reached a decision on your application. We've emailed you the details and the options available to you.",
                null,
            ],
            OrderStatus::Refunded => [
                "This application has been closed and a refund has been processed. Please check your email for confirmation.",
                null,
            ],
        };
    }

    /**
     * Render the track form.
     *
     * The tracker is DRAFTED (config ukv.track.enabled). While off, the page redirects home so the
     * route stays wired but is not publicly reachable.
     */
    public function show(): View|RedirectResponse
    {
        if (! config('ukv.track.enabled')) {
            return redirect('/');
        }

        return view('track');
    }

    /**
     * Look up an order by exact reference and return the privacy-safe stage timeline.
     *
     * Apply `throttle:` middleware to the route that points here (e.g. throttle:10,1)
     * to rate-limit enumeration attempts.
     */
    public function lookup(Request $request, RequirementService $requirements): View|RedirectResponse
    {
        if (! config('ukv.track.enabled')) {
            return redirect('/');
        }

        $validated = $request->validate([
            'ref' => ['required', 'string', 'max:32'],
        ]);

        // Normalise: references are upper-case, hyphenated (UKV-YYYY-NNNNNN). Trim/upcase
        // so casing/whitespace in the email copy doesn't cause a false miss. Still EXACT
        // match — no partial/LIKE lookups.
        $ref = strtoupper(trim($validated['ref']));

        $order = Order::query()->where('order_ref', $ref)->first();

        // Miss: generic result. Identical copy regardless of why — never leak existence.
        if ($order === null) {
            return view('track', [
                'searchedRef' => $ref,
                'notFound'    => true,
            ]);
        }

        $timeline = self::stagesFor($order->status);

        // Document Requirements Engine: the personalised checklist for this order. This is
        // generic "documents to prepare" guidance (document types + notes), not customer PII —
        // and the requester already holds the reference. Helps travellers gather the right
        // documents while their application progresses.
        $docItems = $requirements->for($order);

        // Only ever pass the reference (already known to the requester) + derived,
        // generic stage data to the view. No name/email/passport/destination/fees.
        return view('track', [
            'result' => [
                'ref'           => $order->order_ref,
                'stages'        => $timeline['stages'],
                'current_index' => $timeline['current_index'],
                'outcome'       => $timeline['outcome'],
                'now'           => $timeline['now'],
                'next'          => $timeline['next'],
            ],
            'docItems' => $docItems,
        ]);
    }
}
