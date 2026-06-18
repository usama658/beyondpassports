{{--
    Held-slot availability surface (Wave 2 / B3) — PRESENTATIONAL.

    Shown per centre card inside partials.nearest-centre (A3). For a given supply node it
    surfaces the next bookable, Beyond Passports-held appointment slots:
        "✓ Appointments available — next: {date}"  (+ "+N more" when there's a queue)

    MUST render nothing (no empty box) when there are no slots, so it is harmless in the
    Wave-1-only state (no SlotService rows / SlotService returning an empty collection).

    Expected variable:
      $node   App\Models\SupplyNode — the centre this card represents.

    Optional variable:
      $slots  iterable<App\Models\CentreSlot> — pre-fetched available slots. When omitted,
              we ask the SlotService ourselves (acceptable: this partial IS the slot surface).

    Include with:
      @@include('partials.centre-slots', ['node' => $node])
      @@include('partials.centre-slots', ['node' => $node, 'slots' => $slots])

    Defensive: tolerates a null/absent $node, a missing SlotService, and any service throw —
    in every such case it renders nothing rather than breaking the surrounding results card.
--}}
@php
    /** @var \App\Models\SupplyNode|null $node */
    $node = $node ?? null;

    // Resolve the available slots. Prefer a pre-passed $slots; otherwise ask the SlotService.
    // Wrapped so a missing service (Wave-1-only) or any throw degrades to "no slots", never an error.
    $slotItems = collect();
    if (isset($slots)) {
        $slotItems = collect($slots);
    } elseif ($node !== null) {
        try {
            $service = app(\App\Services\SlotService::class);
            $resolved = $service->availableFor($node);
            $slotItems = collect($resolved);
        } catch (\Throwable $e) {
            $slotItems = collect();
        }
    }

    // Keep only slots that actually carry a future-ish slot_at, and order soonest-first so
    // "next" is genuinely the nearest appointment regardless of how the source was sorted.
    $slotItems = $slotItems
        ->filter(fn ($slot) => is_object($slot) && ($slot->slot_at ?? null) instanceof \Illuminate\Support\Carbon)
        ->sortBy(fn ($slot) => $slot->slot_at->getTimestamp())
        ->values();

    $next = $slotItems->first();
    $more = max(0, $slotItems->count() - 1);
@endphp

@if ($next !== null)
    <div class="ukv-slots" role="status" aria-label="Appointment availability">
        <style>
            /* centre-slots partial — self-contained palette (ink/petrol/teal, self-hosted Outfit). */
            .ukv-slots{font-family:"Outfit",system-ui,sans-serif;display:inline-flex;align-items:baseline;flex-wrap:wrap;gap:4px 8px;margin:10px 0 0;padding:7px 12px;background:#f3f8f4;border:1px solid #d6e6da;border-left:3px solid #155E7A;border-radius:8px;line-height:1.45}
            .ukv-slots .us-tick{color:#1F6E63;font-weight:700;margin-right:1px}
            .ukv-slots .us-label{font-size:13.5px;font-weight:600;color:#16222E}
            .ukv-slots .us-next{font-size:13.5px;color:#16222E}
            .ukv-slots .us-next strong{color:#16222E;font-weight:600;white-space:nowrap}
            .ukv-slots .us-more{font-family:"Outfit",system-ui,sans-serif;font-size:11px;letter-spacing:.04em;color:#9c4a26;background:#E2F1EE;border-radius:999px;padding:1px 8px}
        </style>
        <span class="us-label"><span class="us-tick" aria-hidden="true">✓</span>Appointments available</span>
        <span class="us-next">— next: <strong>{{ $next->slot_at->format('j M, g:ia') }}</strong></span>
        @if ($more > 0)
            <span class="us-more">+{{ $more }} more</span>
        @endif
    </div>
@endif
