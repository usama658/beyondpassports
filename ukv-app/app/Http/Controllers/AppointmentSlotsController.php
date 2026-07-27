<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CentreSlot;
use App\Models\Destination;
use App\Services\SlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public per-centre appointment slots for the /schengen-visa slot picker.
 *
 * Booking is centre-specific: each application centre (supply node) has its own slots. This
 * returns, for a chosen Schengen country, its bookable centres and each centre's real available
 * slots (CentreSlot via SlotService) — the SAME inventory the find-a-centre finder uses, so the
 * two surfaces stay in sync. A centre with no published slots is still listed (empty slots array)
 * so the picker can offer an honest "ask us to check live" for it.
 */
class AppointmentSlotsController extends Controller
{
    public function index(Request $request, SlotService $slots): JsonResponse
    {
        $country = trim((string) $request->query('country', ''));

        $destination = Destination::query()
            ->where('visa_type', 'Schengen')
            ->where(fn ($q) => $q->where('name', $country)->orWhere('slug', $country))
            ->with(['supplyNodes' => fn ($q) => $q->where('we_book_here', true)])
            ->first();

        if ($destination === null) {
            return response()->json(['country' => $country, 'centres' => []]);
        }

        // Same 30-day window the lp-bold board totals over, so per-centre "open" counts
        // sum to the board's country total (no board-vs-modal mismatch).
        $windowEnd = now()->addDays(30);

        $centres = $destination->supplyNodes
            ->map(function ($node) use ($slots, $windowEnd) {
                // Pull a wider window, then show up to 6 unique DATES (a centre can have several
                // times on one day — the customer picks a day; we confirm the exact time live).
                $available = $slots->availableFor($node, 40);

                // Real available-slot count in the board window (the true "N open", not the
                // capped 6-day preview below).
                $openCount = CentreSlot::query()
                    ->where('supply_node_id', $node->getKey())
                    ->available()
                    ->where('slot_at', '<=', $windowEnd)
                    ->count();

                return [
                    'name' => $node->name,
                    'city' => $this->cityFrom($node->name),
                    'postcode' => $node->postcode,
                    'open' => $openCount,
                    'slots' => $available
                        ->map(fn ($s) => [
                            'iso' => $s->slot_at->toDateString(),
                            'label' => $s->slot_at->format('D j M'),
                        ])
                        ->unique('iso')
                        ->take(6)
                        ->values()
                        ->all(),
                ];
            })
            // Soonest-slot centres first; centres with no slots sink to the bottom.
            ->sortBy(fn ($c) => $c['slots'][0]['iso'] ?? '9999-12-31')
            ->values()
            ->all();

        return response()->json(['country' => $destination->name, 'centres' => $centres]);
    }

    /** Pull a readable city from a "{Country} visa application centre – {City}" node name. */
    private function cityFrom(string $name): string
    {
        foreach (['–', ' - ', '—'] as $sep) {
            if (str_contains($name, $sep)) {
                $parts = explode($sep, $name);

                return trim((string) end($parts));
            }
        }

        return '';
    }
}
