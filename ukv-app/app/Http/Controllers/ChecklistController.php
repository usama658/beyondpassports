<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use App\Services\ChecklistService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        // No contact captured at this step — value-first. The delivery agent's POST
        // /checklist/{token}/send records contact + channels onto the existing request.
        $checklist = $this->checklists->create($destination, $inputs, [
            'ip' => $request->ip(),
        ]);

        return redirect()->route('checklist.show', ['checklistRequest' => $checklist->token]);
    }

    /**
     * Render a saved checklist by its public token. noindex (per-user / thin) — set in the
     * view via partials.seo-meta. Unknown token -> 404 (implicit route-model binding).
     */
    public function show(ChecklistRequest $checklistRequest): View
    {
        $checklistRequest->loadMissing('destination');

        return view('public.checklist-result', [
            'request'     => $checklistRequest,
            'destination' => $checklistRequest->destination,
        ]);
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
