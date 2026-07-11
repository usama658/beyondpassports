<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\NewSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Footer email capture ("visa-rule updates") — a consented marketing opt-in.
 *
 * Mirrors ContactController's deliberately minimal lead pattern: NO DB table, NO Order.
 * We require an explicit marketing-consent tick (GDPR/PECR — never capture marketing
 * contacts without consent), then email the owner + log the opt-in. The email/log is the
 * record. If the list grows or needs syncing to the CRM, persist to a `subscribers` table
 * (email, consent, ip, created_at) and push to HubSpot off that row instead.
 */
class SubscribeController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
            // Marketing consent is mandatory — the checkbox must be ticked.
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => 'Please tick the box to confirm you’re happy to receive email updates.',
        ]);

        Log::info('Newsletter opt-in', [
            'email' => $data['email'],
            'consent' => true,
            'ip' => $request->ip(),
        ]);

        // Notify the owner so opt-ins actually reach the mailing list (the log alone is easy to
        // miss). Inline send + try/catch, mirroring ContactController — an SMTP hiccup logs but
        // never breaks the traveller's confirmation.
        $recipient = config('ukv.owner_email') ?: config('mail.from.address');
        if (! empty($recipient)) {
            try {
                Mail::to($recipient)->send(new NewSubscriber(
                    email: $data['email'],
                    capturedAt: now()->format('D j M Y, H:i'),
                ));
                Log::info('Newsletter opt-in emailed', ['to' => $recipient]);
            } catch (\Throwable $e) {
                Log::error('Newsletter opt-in email failed', ['to' => $recipient, 'error' => $e->getMessage()]);
            }
        }

        return back()->with('subscribe_status',
            'Thanks — you’re on the list. We’ll email occasional visa-rule updates, and you can unsubscribe any time.');
    }
}
