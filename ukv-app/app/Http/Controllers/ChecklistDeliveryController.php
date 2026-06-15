<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SendChecklistWhatsApp;
use App\Jobs\SyncChecklistLead;
use App\Mail\ChecklistDelivery;
use App\Models\ChecklistRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * "Send me this checklist" — the delivery + lead-capture endpoint for the public document-checklist
 * tool. Bound to POST /checklist/{token}/send (token = ChecklistRequest route key).
 *
 * Stance (per spec): the full checklist is already shown on-screen with NO contact required. This
 * endpoint runs only when a MOTIVATED user opts to have it delivered / saved as a lead.
 *
 * Flow:
 *   1. Validate the chosen channels + the contact detail each channel needs + the consent split.
 *   2. Persist contact + chosen channels + marketing_consent onto the request (the lead record).
 *   3. Dispatch the chosen deliveries (email = transactional Mailable; whatsapp = opt-in job).
 *   4. Sync the lead to HubSpot (guarded; marketing only with consent).
 *   5. Redirect back to the saved link with a sent-confirmation flash.
 *
 * CONSENT SPLIT:
 *   - Email + WhatsApp delivery are TRANSACTIONAL — they fulfil the explicit "send me this" request
 *     and need NO marketing consent.
 *   - marketing_consent is a SEPARATE optional checkbox that only governs nurture/marketing (#23) and
 *     the marketing fields synced to HubSpot. It never gates the transactional delivery.
 */
class ChecklistDeliveryController extends Controller
{
    public function send(Request $request, ChecklistRequest $checklist): RedirectResponse
    {
        $data = $request->validate([
            // At least one delivery channel must be chosen.
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['string', Rule::in(['email', 'whatsapp'])],

            // Email required only when email delivery is chosen.
            'email' => [
                'nullable',
                'email:rfc',
                'max:190',
                Rule::requiredIf(fn (): bool => in_array('email', (array) $request->input('channels', []), true)),
            ],

            // Phone required only when WhatsApp delivery is chosen.
            'phone' => [
                'nullable',
                'string',
                'max:40',
                Rule::requiredIf(fn (): bool => in_array('whatsapp', (array) $request->input('channels', []), true)),
            ],

            // Separate, optional marketing opt-in — NOT required to deliver.
            'marketing_consent' => ['sometimes', 'boolean'],
        ], [
            'channels.required' => 'Choose at least one way to receive your checklist.',
            'email.required' => 'Enter an email address to have your checklist emailed.',
            'phone.required' => 'Enter a phone number to receive your checklist on WhatsApp.',
        ]);

        $channels = array_values(array_unique($data['channels']));
        $email = $this->blankToNull($data['email'] ?? null);
        $phone = $this->blankToNull($data['phone'] ?? null);

        // Persist contact + chosen channels + consent onto the request (this IS the lead record).
        // Only overwrite contact fields we actually received, so a re-send can add a channel without
        // wiping a previously captured detail.
        $checklist->channels = $channels;
        if ($email !== null) {
            $checklist->email = $email;
        }
        if ($phone !== null) {
            $checklist->phone = $phone;
        }
        $checklist->marketing_consent = (bool) ($data['marketing_consent'] ?? false);
        $checklist->save();

        // --- Dispatch chosen deliveries (transactional — no marketing consent needed) ---
        if (in_array('email', $channels, true) && $checklist->email !== null) {
            Mail::to($checklist->email)->queue(new ChecklistDelivery($checklist));
        }

        if (in_array('whatsapp', $channels, true) && $checklist->phone !== null) {
            // Opt-in only; the job re-checks the channel + creds + phone and no-ops otherwise.
            SendChecklistWhatsApp::dispatch($checklist);
        }

        // --- Lead sync (guarded; marketing fields gated on consent inside the job/service) ---
        if ($checklist->email !== null) {
            SyncChecklistLead::dispatch($checklist);
        }

        return redirect()
            ->to($this->savedLink($checklist))
            ->with('status', $this->confirmation($channels));
    }

    /** Human confirmation describing exactly which channels were sent. */
    private function confirmation(array $channels): string
    {
        $where = [];
        if (in_array('email', $channels, true)) {
            $where[] = 'emailed';
        }
        if (in_array('whatsapp', $channels, true)) {
            $where[] = 'sent to your WhatsApp';
        }

        $delivery = $where === [] ? 'saved' : implode(' and ', $where);

        return "Done — your checklist has been {$delivery}. It's also saved at this link so you can come back to it any time.";
    }

    private function savedLink(ChecklistRequest $checklist): string
    {
        // Prefer the named route when registered; fall back to a plain path.
        if (app('router')->has('checklist.show')) {
            return route('checklist.show', ['token' => $checklist->token]);
        }

        return url('/checklist/'.$checklist->token);
    }

    private function blankToNull(?string $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
