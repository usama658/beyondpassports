<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\AppointmentEnquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Landing-page appointment / eligibility enquiry capture.
 *
 * The LP form (partials/lp-appt-form.blade.php) still opens WhatsApp client-side as its primary
 * action. This endpoint is fired in the background (fetch) so the lead is also emailed + logged —
 * belt-and-braces, so a lead is not lost if the traveller never sends the WhatsApp message.
 *
 * Deliberately minimal, mirroring ContactController: NO Order, NO DB table. Name is optional
 * (the LP button works with number only, or neither); we still record whatever was typed.
 */
class AppointmentEnquiryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'source' => ['nullable', 'string', 'max:120'],
        ]);

        // Nothing to capture if every identity field is blank — don't email an empty lead.
        if (trim((string) ($data['name'] ?? '')) === ''
            && trim((string) ($data['phone'] ?? '')) === ''
            && trim((string) ($data['email'] ?? '')) === '') {
            return response()->json(['ok' => false, 'message' => 'Nothing to record.'], 422);
        }

        $recipient = config('ukv.owner_email') ?: config('mail.from.address');

        Log::info('Appointment enquiry', [
            'has_name' => ! empty($data['name']),
            'has_phone' => ! empty($data['phone']),
            'has_email' => ! empty($data['email']),
            'source' => $data['source'] ?? null,
            'ip' => $request->ip(),
        ]);

        if (! empty($recipient)) {
            // Inline send (not queued) so a stalled worker never swallows the lead. Wrapped so an
            // SMTP hiccup logs but never breaks the traveller's WhatsApp hand-off on the LP.
            try {
                Mail::to($recipient)->send(new AppointmentEnquiry(
                    name: (string) ($data['name'] ?? ''),
                    phone: (string) ($data['phone'] ?? ''),
                    source: (string) ($data['source'] ?? 'landing page'),
                    email: (string) ($data['email'] ?? ''),
                ));
                Log::info('Appointment enquiry emailed', ['to' => $recipient]);
            } catch (\Throwable $e) {
                Log::error('Appointment enquiry email failed', ['to' => $recipient, 'error' => $e->getMessage()]);
            }
        } else {
            Log::warning('Appointment enquiry not emailed: no ukv.owner_email or mail.from.address configured.');
        }

        return response()->json(['ok' => true]);
    }
}
