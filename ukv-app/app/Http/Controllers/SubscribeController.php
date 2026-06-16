<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Footer email capture ("visa-rule updates") — a consented marketing opt-in.
 *
 * Mirrors ContactController's deliberately minimal lead pattern: NO DB table, NO Order.
 * We require an explicit marketing-consent tick (GDPR/PECR — never capture marketing
 * contacts without consent), then log the opt-in. The log is the record. If the list
 * grows or needs syncing to the CRM, persist to a `subscribers` table (email, consent,
 * ip, created_at) and push to HubSpot off that row instead.
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

        return back()->with('subscribe_status',
            'Thanks — you’re on the list. We’ll email occasional visa-rule updates, and you can unsubscribe any time.');
    }
}
