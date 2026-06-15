<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactEnquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Public contact / callback endpoint (resources/views/public/contact.blade.php).
 *
 * Lightweight lead capture mirroring a callback request: there is NO Order and NO DB table.
 * We queue an internal email to the owner and log the enquiry. That is intentionally minimal —
 * if volume grows or leads need triage/SLA tracking, persist to a `contact_messages` table
 * (name, phone, best_time, message, ip, created_at) and dispatch the mail off that row instead.
 *
 * Progressive enhancement: the blade form posts a normal POST when JS is off (redirect back with
 * a `status` flash); the page's fetch path sends `Accept: application/json` / X-Requested-With
 * and gets a JSON `{ ok, message }` so the existing vanilla-JS success state still works.
 */
class ContactController extends Controller
{
    public function store(ContactRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        $recipient = config('ukv.owner_email') ?: config('mail.from.address');

        Log::info('Contact callback request', [
            'name' => $data['name'],
            'phone' => $data['phone'],
            'best_time' => $data['best_time'] ?? null,
            'has_message' => ! empty($data['message']),
            'ip' => $request->ip(),
        ]);

        if (! empty($recipient)) {
            Mail::to($recipient)->queue(new ContactEnquiry(
                name: $data['name'],
                phone: $data['phone'],
                bestTime: $data['best_time'] ?? null,
                message: $data['message'] ?? null,
            ));
        } else {
            // No owner/from address configured — the log above is the only record. Flag it so it
            // is obvious in ops why no email arrived, without failing the traveller's request.
            Log::warning('Contact callback request not emailed: no ukv.owner_email or mail.from.address configured.');
        }

        $first = trim(explode(' ', trim($data['name']))[0]);
        $message = "Thanks, {$first} — your callback is booked. A real, UK-based person will call you "
            .'on the number you gave us, in your chosen time slot.';

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }
}
