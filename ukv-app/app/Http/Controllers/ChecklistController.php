<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SyncChecklistLead;
use App\Mail\ChecklistDelivery;
use App\Mail\NewChecklistLead;
use App\Models\ChecklistRequest;
use App\Models\Destination;
use App\Services\ChecklistPdfService;
use App\Services\ChecklistService;
use App\Services\IcsService;
use App\Services\StripeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Public document-checklist tool (build wave 1: web layer).
 *
 * Value-first flow (stance locked in the spec):
 *   1. GET  /document-checklist  -> the wizard (destination + a few trip questions).
 *   2. POST /document-checklist  -> build the tailored items + persist a ChecklistRequest,
 *                                   then redirect to the shareable token result.
 *   3. GET  /checklist/{token}   -> render the saved result (on-screen, free) + the
 *                                   "send me this" delivery offer + apply CTA. noindex.
 *
 * Works with NO JavaScript: the wizard is a normal same-origin POST; the result lives at
 * its own URL. Progressive enhancement is optional and layered on top.
 *
 * Wiring (NOT done here — see reply to caller):
 *   GET  /document-checklist        -> tool()
 *   POST /document-checklist        -> result()   (apply `throttle:` to this route)
 *   GET  /checklist/{token}         -> show()     (route-model-bound on token)
 *   POST /checklist/{token}/send    -> (delivery agent owns this — placeholder)
 *
 * Snapshot contract: ChecklistService::create() stores the computed items on the request,
 * so the saved link / PDF / email stay stable even if the admin-editable rules change later.
 */
class ChecklistController extends Controller
{
    public function __construct(private readonly ChecklistService $checklists) {}

    /**
     * Render the wizard. Shares the live destination list (same source as /apply + /tools)
     * so every seeded destination is selectable.
     */
    public function tool(): View
    {
        return view('public.document-checklist', [
            'navDestinations' => Destination::orderBy('name')->get(),
        ]);
    }

    /**
     * Build the tailored checklist from the wizard answers, persist a ChecklistRequest
     * (snapshotting the items + minting a token), then redirect to the shareable result.
     *
     * No contact is required at this step — the value-first stance shows the full list on
     * the result page first; delivery is offered there.
     */
    public function result(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'destination'        => ['required', 'string', 'max:120'],
            'email'              => ['required', 'email:rfc', 'max:190'],
            'trip_purpose'       => ['nullable', 'string', 'in:tourist,business,study,other'],
            'is_minor'           => ['nullable', 'in:yes,no'],
            'residency_status'   => ['nullable', 'string', 'in:citizen,permanent,visa_holder'],
            'employment_status'  => ['nullable', 'string', 'in:employed,self_employed,student,retired,unemployed'],
            'accommodation_type' => ['nullable', 'string', 'in:hotel,host,own,other'],
            'funding_source'     => ['nullable', 'string', 'in:self,sponsor,employer'],
            'travel_date'        => ['nullable', 'date'],
            'return_date'        => ['nullable', 'date'],
            'visa_entries'       => ['nullable', 'string', 'in:single,multiple'],
            'prior_refusal'      => ['nullable', 'in:yes,no'],
            'marketing_consent'  => ['sometimes', 'boolean'],
        ], [
            'email.required' => 'Enter an email address so we can send your checklist.',
        ]);

        // Resolve by slug OR display name (mirrors /apply's destination handling — the apply
        // form posts the display name; deep links may carry a slug).
        $destination = Destination::query()
            ->where('slug', $validated['destination'])
            ->orWhere('name', $validated['destination'])
            ->first();

        if ($destination === null) {
            return back()
                ->withInput()
                ->withErrors(['destination' => 'Please choose a destination from the list so we can tailor your checklist.']);
        }

        $inputs = $this->engineInputs($validated);

        $checklist = $this->checklists->create($destination, $inputs, [
            'ip' => $request->ip(),
        ]);

        // Email the checklist + capture the lead (the wizard now IS the delivery + lead step).
        $checklist->email = $validated['email'];
        $checklist->channels = ['email'];
        $checklist->marketing_consent = (bool) ($validated['marketing_consent'] ?? false);
        $checklist->save();

        Mail::to($checklist->email)->queue(new ChecklistDelivery($checklist, false, false));
        SyncChecklistLead::dispatch($checklist);

        $recipient = config('ukv.owner_email') ?: config('mail.from.address');
        if (! empty($recipient)) {
            try {
                Mail::to($recipient)->send(new NewChecklistLead($checklist, url('/checklist/'.$checklist->token)));
            } catch (\Throwable $e) {
                Log::error('Checklist lead email failed', ['token' => $checklist->token, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('checklist.thanks', ['checklistRequest' => $checklist->token]);
    }

    /**
     * Thank-you page after the wizard: confirms the checklist was emailed and auto-redirects the
     * user to WhatsApp (same design as apply-thanks). Replaces the on-page /checklist result while
     * the result page is drafted (config ukv.checklist.result_enabled).
     */
    public function thanks(ChecklistRequest $checklistRequest): View
    {
        $checklistRequest->loadMissing('destination');
        $dest = (string) ($checklistRequest->destination?->name ?? 'your trip');
        $inputs = is_array($checklistRequest->inputs) ? $checklistRequest->inputs : [];

        $bits = ['Hi Beyond Passports, I just got my document checklist for '.$dest.'.'];
        if (! empty($inputs['travel_date'])) {
            $bits[] = 'Travelling around '.$inputs['travel_date'].'.';
        }
        $bits[] = 'Please help me get it right.';
        $waNum = preg_replace('/\D+/', '', (string) (config('ukv.whatsapp') ?: '447882747584'));
        $waUrl = 'https://wa.me/'.$waNum.'?text='.rawurlencode(implode(' ', $bits));

        return view('public.checklist-thanks', [
            'destination' => $dest,
            'email'       => $checklistRequest->email,
            'waUrl'       => $waUrl,
        ]);
    }

    /**
     * Take the chosen tier + immediate-delivery consent, snapshot the price server-side,
     * and start a Stripe Checkout session for the instant checklist. Already-paid requests
     * skip straight to the (now full) result. paid_at is written only by the webhook.
     */
    public function checkout(Request $request, ChecklistRequest $checklistRequest, StripeService $stripe): RedirectResponse
    {
        $validated = $request->validate([
            'tier' => ['required', 'in:standard,express,premium'],
            'consent' => ['accepted'],
            'email' => ['nullable', 'email', 'max:160'],
        ]);

        if ($checklistRequest->isPaid()) {
            return redirect()->route('checklist.show', ['checklistRequest' => $checklistRequest->token]);
        }

        // Graceful fallback: if Stripe isn't configured on this environment, don't 500 trying
        // to open a Checkout Session. Send them back to the result page with a friendly notice
        // (the free WhatsApp path stays available). Nothing is saved — they can retry once keys land.
        if ((string) config('services.stripe.secret') === '') {
            return redirect()
                ->route('checklist.show', ['checklistRequest' => $checklistRequest->token])
                ->with('pay_unavailable', true);
        }

        $checklistRequest->loadMissing('destination');
        abort_if($checklistRequest->destination === null, 404);

        $amount = app(\App\Services\ChecklistPricing::class)
            ->priceFor($checklistRequest->destination, $validated['tier']);

        $checklistRequest->fill([
            'tier' => $validated['tier'],
            'amount_gbp' => $amount,
            'currency' => 'gbp',
            'immediate_delivery_consent' => true,
            'consent_at' => now(),
        ]);
        if (! empty($validated['email'])) {
            $checklistRequest->email = $validated['email'];
        }
        $checklistRequest->save();

        // Never 500 the buyer if Stripe rejects the session create — degrade to the friendly
        // notice and log the reason (the free WhatsApp path stays available).
        try {
            return redirect()->away($stripe->createChecklistSession($checklistRequest));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Checklist checkout: Stripe session create failed.', [
                'token' => $checklistRequest->token,
                'tier' => $checklistRequest->tier,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('checklist.show', ['checklistRequest' => $checklistRequest->token])
                ->with('pay_unavailable', true)
                ->with('pay_error', $e->getMessage());
        }
    }

    /**
     * Render a saved checklist by its public token. noindex (per-user / thin) — set in the
     * view via partials.seo-meta. Unknown token -> 404 (implicit route-model binding).
     *
     * Paid-aware: reads $paid from isPaid() OR a valid Stripe session_id query param.
     * The session_id path is read-only — show() must NOT write paid_at (webhook does).
     */
    public function show(ChecklistRequest $checklistRequest, Request $request, StripeService $stripe): View|RedirectResponse
    {
        // The on-page result is DRAFTED (config ukv.checklist.result_enabled). While off, a saved
        // link sends the user back to the wizard instead of showing the old checklist page.
        if (! config('ukv.checklist.result_enabled')) {
            return redirect()->route('checklist.tool');
        }

        $checklistRequest->loadMissing('destination');

        $sessionId = (string) $request->query('session_id', '');
        $paid = $checklistRequest->isPaid()
            || ($sessionId !== '' && $stripe->isChecklistSessionPaid($checklistRequest->token, $sessionId));

        $tierCards = $checklistRequest->destination !== null
            ? app(\App\Services\ChecklistPricing::class)->cards($checklistRequest->destination)
            : [];

        return view('public.checklist-result', [
            'request'     => $checklistRequest,
            'destination' => $checklistRequest->destination,
            'paid'        => $paid,
            'peek'        => $checklistRequest->peek(),
            'tierCards'   => $tierCards,
        ]);
    }

    /**
     * Instant calendar reminder (.ics) — no contact required (value-first). 404 when the request
     * has no travel date to anchor the "apply by" deadline. Uses the destination's processing_days
     * (falls back to the config default inside IcsService).
     */
    public function calendar(ChecklistRequest $checklistRequest, IcsService $ics): Response
    {
        abort_unless($checklistRequest->isPaid(), 403);

        $checklistRequest->loadMissing('destination');
        $inputs = is_array($checklistRequest->inputs) ? $checklistRequest->inputs : [];

        $body = $ics->buildForChecklist(
            (string) ($checklistRequest->destination?->name ?? 'your trip'),
            $inputs['travel_date'] ?? null,
            $checklistRequest->destination?->processing_days,
        );

        abort_if($body === null, 404);

        $name = $checklistRequest->destination?->slug ?? 'trip';

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="ukvisaco-'.$name.'-reminder.ics"',
        ]);
    }

    /**
     * Printable / save-as-PDF view of the saved checklist (print-CSS; no PDF dependency). No
     * contact required.
     */
    public function printable(ChecklistRequest $checklistRequest, ChecklistPdfService $pdf): Response
    {
        abort_unless($checklistRequest->isPaid(), 403);

        $checklistRequest->loadMissing('destination');

        return $pdf->renderPrintable($checklistRequest);
    }

    /**
     * Normalise the validated wizard answers into the keys the requirements engine reads.
     * is_minor / prior_refusal arrive as 'yes' | 'no' selects; the engine's matchBool casts
     * with (bool), and (bool) 'no' === true — so we map them to real booleans here and drop
     * blanks (a missing axis simply means the dependent rules don't fire).
     *
     * @param  array<string, mixed>  $v
     * @return array<string, mixed>
     */
    private function engineInputs(array $v): array
    {
        $inputs = [];

        foreach (['trip_purpose', 'residency_status', 'employment_status', 'accommodation_type', 'funding_source', 'travel_date', 'return_date', 'visa_entries'] as $key) {
            if (! empty($v[$key])) {
                $inputs[$key] = $v[$key];
            }
        }

        foreach (['is_minor', 'prior_refusal'] as $key) {
            if (isset($v[$key]) && $v[$key] !== '' && $v[$key] !== null) {
                $inputs[$key] = $v[$key] === 'yes';
            }
        }

        return $inputs;
    }
}
