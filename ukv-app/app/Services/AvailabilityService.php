<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CentreAvailability;
use App\Models\Destination;
use App\Models\SupplyNode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Marketing-side appointment availability for the public /schengen-visa board.
 *
 * Reads published CentreAvailability snapshots (NOT real held inventory — that is CentreSlot/
 * SlotService). The snapshot's own status()/isStale() are the single source of truth: a stale or
 * dateless snapshot reports "ask" so the board never shows a fabricated date. Ops keep snapshots
 * fresh (manually or derived); the board self-corrects as they expire.
 */
final class AvailabilityService
{
    /**
     * Per-destination availability for the public board.
     *
     * For each destination of the given visa type, gather its own centres' snapshots plus the
     * global bookable centres' snapshots (which apply to every destination). Consider ONLY
     * non-stale snapshots whose status() is not 'ask'. From those: next_available_on = soonest
     * date; status = ok if any band good else lim; confirmed_at = latest confirmed_at among the
     * contributing snapshots. If none qualify => 'ask' with nulls.
     *
     * @return array<int, array{status:string, next_available_on:?Carbon, confirmed_at:?Carbon}>
     */
    public function byDestination(string $visaType = 'Schengen'): array
    {
        $destinations = Destination::query()
            ->where('visa_type', $visaType)
            ->with('supplyNodes.availability')
            ->get();

        // Global bookable centres apply to every destination.
        $globalAvailability = SupplyNode::query()
            ->where('we_book_here', true)
            ->where('is_global', true)
            ->with('availability')
            ->get()
            ->map(fn (SupplyNode $node) => $node->availability)
            ->filter()
            ->values();

        $out = [];

        foreach ($destinations as $destination) {
            /** @var Collection<int, CentreAvailability> $snapshots */
            $snapshots = $destination->supplyNodes
                ->map(fn (SupplyNode $node) => $node->availability)
                ->filter()
                ->concat($globalAvailability)
                ->filter(fn (CentreAvailability $a) => ! $a->isStale() && $a->status() !== 'ask')
                ->values();

            if ($snapshots->isEmpty()) {
                $out[$destination->getKey()] = [
                    'status' => 'ask',
                    'next_available_on' => null,
                    'confirmed_at' => null,
                ];

                continue;
            }

            $nextAvailableOn = $snapshots
                ->map(fn (CentreAvailability $a) => $a->next_available_on)
                ->filter()
                ->sort()
                ->first();

            $confirmedAt = $snapshots
                ->map(fn (CentreAvailability $a) => $a->confirmed_at)
                ->filter()
                ->sortDesc()
                ->first();

            $statuses = $snapshots->map(fn (CentreAvailability $a) => $a->status());
            $status = $statuses->contains('ok') ? 'ok' : ($statuses->contains('lim') ? 'lim' : 'ask');

            $out[$destination->getKey()] = [
                'status' => $status,
                'next_available_on' => $nextAvailableOn,
                'confirmed_at' => $confirmedAt,
            ];
        }

        return $out;
    }

    /**
     * Upsert a snapshot for one supply node (keyed on supply_node_id).
     *
     * confirmed_at = now(); expires_at = now()+freshness. A null date forces a null band. The band
     * must be a valid CentreAvailability::BANDS member or null. MANUAL-WINS: a 'derived' write does
     * not overwrite an existing fresh 'manual' snapshot — the existing one is returned untouched.
     */
    public function setSnapshot(
        int $supplyNodeId,
        ?Carbon $nextAvailableOn,
        ?string $band,
        string $source = 'manual',
        ?int $freshnessDays = null,
    ): CentreAvailability {
        // A snapshot with no date cannot carry a band.
        if ($nextAvailableOn === null) {
            $band = null;
        }

        if ($band !== null && ! in_array($band, CentreAvailability::BANDS, true)) {
            throw new InvalidArgumentException("Invalid availability band: {$band}");
        }

        // MANUAL-WINS: a derived write never clobbers a fresh manual snapshot.
        if ($source === 'derived') {
            $existing = CentreAvailability::query()->where('supply_node_id', $supplyNodeId)->first();
            if ($existing && $existing->source === 'manual' && ! $existing->isStale()) {
                return $existing;
            }
        }

        $now = Carbon::now();

        return CentreAvailability::updateOrCreate(
            ['supply_node_id' => $supplyNodeId],
            [
                'next_available_on' => $nextAvailableOn,
                'band' => $band,
                'source' => $source,
                'confirmed_at' => $now,
                'expires_at' => $now->copy()->addDays($freshnessDays ?? CentreAvailability::FRESHNESS_DAYS),
            ],
        );
    }

    /**
     * Parse a bulk ops paste into validated rows. WRITES NOTHING.
     *
     * Each non-empty line: "<slug>: <YYYY-MM-DD> <good|limited>" or "<slug>: ask" (blank after the
     * colon also resets). The slug is resolved to its bookable Schengen centre. Each row carries an
     * error string when the slug is unknown, the centre is missing, the date is bad, or the band is
     * bad. A reset row clears the date and band.
     *
     * @return array{rows:array<int, array{slug:string, destination:?string, node_id:?int, next_available_on:?string, band:?string, reset:bool, error:?string}>, ok:int, errors:int}
     */
    public function parseBulk(string $input): array
    {
        $rows = [];
        $ok = 0;
        $errors = 0;

        $lines = preg_split('/\r\n|\r|\n/', $input) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $row = [
                'slug' => '',
                'destination' => null,
                'node_id' => null,
                'next_available_on' => null,
                'band' => null,
                'reset' => false,
                'error' => null,
            ];

            // Split on the first colon only.
            $colon = strpos($line, ':');
            if ($colon === false) {
                $row['slug'] = $line;
                $row['error'] = 'Line must be "<slug>: <YYYY-MM-DD> <good|limited>" or "<slug>: ask".';
                $rows[] = $row;
                $errors++;

                continue;
            }

            $slug = trim(substr($line, 0, $colon));
            $rest = trim(substr($line, $colon + 1));
            $row['slug'] = $slug;

            // Resolve slug -> destination -> bookable Schengen centre.
            $destination = Destination::query()
                ->where('slug', $slug)
                ->where('visa_type', 'Schengen')
                ->first();

            if (! $destination) {
                $row['error'] = 'Unknown Schengen slug.';
                $rows[] = $row;
                $errors++;

                continue;
            }

            $row['destination'] = $destination->name;

            $node = $destination->supplyNodes()->where('we_book_here', true)->first();
            if (! $node) {
                $row['error'] = 'No bookable centre for this destination.';
                $rows[] = $row;
                $errors++;

                continue;
            }

            $row['node_id'] = $node->getKey();

            // Reset: "ask" or blank clears the snapshot.
            if ($rest === '' || strtolower($rest) === 'ask') {
                $row['reset'] = true;
                $rows[] = $row;
                $ok++;

                continue;
            }

            // Expect: "<YYYY-MM-DD> <good|limited>".
            $parts = preg_split('/\s+/', $rest) ?: [];
            $dateStr = $parts[0] ?? '';
            $bandStr = isset($parts[1]) ? strtolower($parts[1]) : '';

            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
                $row['error'] = 'Bad date, expected YYYY-MM-DD.';
                $rows[] = $row;
                $errors++;

                continue;
            }

            try {
                $date = Carbon::createFromFormat('Y-m-d', $dateStr);
                if ($date === false || $date->format('Y-m-d') !== $dateStr) {
                    throw new InvalidArgumentException('bad date');
                }
            } catch (\Throwable) {
                $row['error'] = 'Bad date, expected YYYY-MM-DD.';
                $rows[] = $row;
                $errors++;

                continue;
            }

            if (! in_array($bandStr, CentreAvailability::BANDS, true)) {
                $row['error'] = 'Bad band, expected good or limited.';
                $rows[] = $row;
                $errors++;

                continue;
            }

            $row['next_available_on'] = $dateStr;
            $row['band'] = $bandStr;
            $rows[] = $row;
            $ok++;
        }

        return ['rows' => $rows, 'ok' => $ok, 'errors' => $errors];
    }

    /**
     * Bookable Schengen centre nodes whose availability needs attention: missing, stale, or expiring
     * within 2 days. Drives ops prefill flags and the owner digest. Availability is eager loaded.
     *
     * @return Collection<int, SupplyNode>
     */
    public function staleCentres(): Collection
    {
        return SupplyNode::query()
            ->where('we_book_here', true)
            ->whereHas('destinations', fn ($q) => $q->where('visa_type', 'Schengen'))
            ->with('availability')
            ->get()
            ->filter(function (SupplyNode $node): bool {
                $a = $node->availability;

                return $a === null || $a->isStale() || $a->isExpiring(2);
            })
            ->values();
    }
}
