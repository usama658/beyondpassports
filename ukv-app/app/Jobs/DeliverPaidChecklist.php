<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\PaidChecklistMail;
use App\Models\ChecklistRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Post-pay delivery for the instant checklist.
 *
 * - Standard: reveal-only (no delivery here).
 * - Express/Premium: email the saved checklist (with PDF/.ics links) to the buyer if an
 *   email is on file.
 * - Premium: additionally flag a team-notify for the 1:1 WhatsApp review.
 */
final class DeliverPaidChecklist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $checklistRequestId) {}

    public function handle(): void
    {
        $request = ChecklistRequest::query()->with('destination')->find($this->checklistRequestId);
        if ($request === null || ! $request->isPaid()) {
            return;
        }

        $tier = (string) $request->tier;

        if (in_array($tier, ['express', 'premium'], true) && ! empty($request->email)) {
            Mail::to($request->email)->send(new PaidChecklistMail($request));
        }

        if ($tier === 'premium') {
            Log::info('Checklist Premium: queue 1:1 WhatsApp review.', [
                'token' => $request->token,
                'email' => $request->email,
                'destination' => $request->destination?->name,
            ]);
        }
    }
}
