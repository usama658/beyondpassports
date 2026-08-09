<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Bold LP hero case-form lead capture (WhatsApp-only funnel, /schengen-visa-consultancy).
 *
 * Emails the lead to the owner inbox BEFORE the client hands off to WhatsApp, so a lead is
 * never lost if the traveller closes the chat. No Order and no DB table — mirrors
 * ContactController's minimal approach (log + inline email, wrapped so SMTP hiccups never
 * break the paid-ad flow). CSRF-exempt (see VerifyCsrfToken $except): a public lead endpoint
 * that must not fail on a stale token; guarded instead by a honeypot + rate throttle.
 */
class LpLeadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Honeypot: real users never fill this hidden field. Silently accept + drop bot posts.
        if (filled($request->input('website'))) {
            return response()->json(['ok' => true]);
        }

        $data = $request->validate([
            'name' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:40',
            'dest' => 'nullable|string|max:80',
            'intent' => 'nullable|string|max:40',
        ]);

        $recipient = config('ukv.owner_email') ?: config('mail.from.address');
        $intent = $data['intent'] ?? 'case';

        Log::info('LP lead', [
            'intent' => $intent,
            'has_phone' => ! empty($data['phone']),
            'dest' => $data['dest'] ?? null,
            'ip' => $request->ip(),
        ]);

        if (! empty($recipient)) {
            $body = "New Schengen LP lead\n"
                ."Intent: {$intent}\n"
                .'Name: '.($data['name'] ?: '—')."\n"
                .'Phone: '.($data['phone'] ?: '—')."\n"
                .'Destination: '.($data['dest'] ?: '—')."\n"
                ."Source: /schengen-visa-consultancy\n"
                .'IP: '.$request->ip()."\n"
                .'Time: '.now()->toDayDateTimeString();
            try {
                Mail::raw($body, function ($m) use ($recipient, $data, $intent) {
                    $who = $data['name'] ?: 'website visitor';
                    $m->to($recipient)->subject("New case-check lead ({$intent}) — {$who}");
                });
                Log::info('LP lead emailed', ['to' => $recipient]);
            } catch (\Throwable $e) {
                Log::error('LP lead email failed', ['to' => $recipient, 'error' => $e->getMessage()]);
            }
        } else {
            Log::warning('LP lead not emailed: no ukv.owner_email or mail.from.address configured.');
        }

        return response()->json(['ok' => true]);
    }
}
