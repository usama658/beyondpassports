<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\CentreAvailability;
use App\Models\SupplyNode;
use App\Services\AvailabilityService;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Update availability — bulk paste ops surface for centre availability snapshots.
 *
 * Ops paste lines like "spain: 2026-06-12 good" or "france: ask" into the textarea.
 * parseBulk() validates without writing; the page renders an ok/error preview, and on
 * confirm calls AvailabilityService::setSnapshot() per valid row (reset rows clear the
 * snapshot with null date/band). A smart-prefill block above the textarea lists the
 * current bookable Schengen centres and their last-known values — flagged stale/expiring —
 * so ops copy, edit and paste back. Writes flow only through the service (manual-wins,
 * freshness windows enforced there); this page never touches the model directly.
 */
class UpdateAvailability extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Update availability';

    protected static ?string $title = 'Update availability';

    protected static string $view = 'filament.pages.update-availability';

    /**
     * Form state (the bulk paste textarea).
     *
     * @var array<string, mixed>
     */
    public ?array $data = [];

    /**
     * The last parseBulk() result, held for the confirm step / preview render.
     *
     * @var array{rows:array<int, array<string, mixed>>, ok:int, errors:int}|null
     */
    public ?array $preview = null;

    public function mount(): void
    {
        $this->form->fill(['input' => '']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('input')
                    ->label('Bulk paste')
                    ->helperText('One line per centre: "spain: 2026-06-12 good", "france: limited", or "italy: ask" to reset. Slugs are the destination slugs.')
                    ->rows(10)
                    ->autosize(),
            ])
            ->statePath('data');
    }

    /**
     * Pre-rendered, ready-to-edit block of the current bookable Schengen centres and
     * their last-known values. Ops copy this, tweak and paste back. Stale/expiring centres
     * are flagged so they get refreshed first.
     *
     * @return Collection<int, array{slug:string, name:string, line:string, flag:?string, note:?string}>
     */
    public function prefillRows(): Collection
    {
        $service = app(AvailabilityService::class);

        // staleCentres() returns bookable Schengen centres needing attention; union with
        // every bookable Schengen centre so ops see the full, ready-to-edit board.
        $stale = $service->staleCentres();

        $all = SupplyNode::query()
            ->where('we_book_here', true)
            ->whereHas('destinations', fn ($q) => $q->where('visa_type', 'Schengen'))
            ->with(['availability', 'destinations' => fn ($q) => $q->where('visa_type', 'Schengen')])
            ->get();

        $staleIds = $stale->pluck('id')->all();

        return $all
            ->map(function (SupplyNode $node) use ($staleIds): ?array {
                /** @var \App\Models\Destination|null $destination */
                $destination = $node->destinations->first();

                if ($destination === null) {
                    return null;
                }

                $slug = $destination->slug;
                $availability = $node->availability;

                $flag = null;
                $note = null;

                if ($availability === null) {
                    $line = "{$slug}: ask";
                    $flag = 'missing';
                    $note = 'No snapshot yet';
                } else {
                    if ($availability->next_available_on !== null && $availability->band !== null) {
                        $line = sprintf(
                            '%s: %s %s',
                            $slug,
                            $availability->next_available_on->format('Y-m-d'),
                            $availability->band,
                        );
                    } else {
                        $line = "{$slug}: ask";
                    }

                    if ($availability->isStale()) {
                        $flag = 'stale';
                        $note = 'Snapshot expired — refresh';
                    } elseif ($availability->isExpiring(2)) {
                        $flag = 'expiring';
                        $note = 'Expires within 2 days';
                    } elseif (in_array($node->getKey(), $staleIds, true)) {
                        $flag = 'attention';
                        $note = 'Needs attention';
                    }
                }

                return [
                    'slug' => $slug,
                    'name' => $node->name,
                    'line' => $line,
                    'flag' => $flag,
                    'note' => $note,
                ];
            })
            ->filter()
            ->sortBy('slug')
            ->values();
    }

    /** Validate the pasted block (writes nothing); stash the preview for confirm. */
    public function previewBulk(): void
    {
        $state = $this->form->getState();
        $input = (string) ($state['input'] ?? '');

        if (trim($input) === '') {
            Notification::make()
                ->title('Nothing to preview')
                ->body('Paste at least one line first.')
                ->warning()
                ->send();

            $this->preview = null;

            return;
        }

        $this->preview = app(AvailabilityService::class)->parseBulk($input);

        Notification::make()
            ->title('Preview ready')
            ->body("{$this->preview['ok']} valid, {$this->preview['errors']} with errors.")
            ->info()
            ->send();
    }

    /**
     * Commit every valid row through setSnapshot(). Reset rows clear the snapshot
     * (null date + null band). Error rows are skipped. Re-parses fresh so a stale
     * preview can never write.
     */
    public function applyBulk(): void
    {
        if (! $this->canWrite()) {
            Notification::make()
                ->title('Read-only')
                ->body('You do not have permission to update availability.')
                ->danger()
                ->send();

            return;
        }

        $state = $this->form->getState();
        $input = (string) ($state['input'] ?? '');

        $result = app(AvailabilityService::class)->parseBulk($input);

        if ($result['ok'] === 0) {
            Notification::make()
                ->title('Nothing applied')
                ->body('No valid rows to write.')
                ->warning()
                ->send();

            $this->preview = $result;

            return;
        }

        $service = app(AvailabilityService::class);
        $applied = 0;

        foreach ($result['rows'] as $row) {
            if ($row['error'] !== null || $row['node_id'] === null) {
                continue;
            }

            if ($row['reset']) {
                $service->setSnapshot((int) $row['node_id'], null, null);
            } else {
                $date = $row['next_available_on'] !== null
                    ? Carbon::createFromFormat('Y-m-d', $row['next_available_on'])->startOfDay()
                    : null;

                $service->setSnapshot((int) $row['node_id'], $date, $row['band']);
            }

            $applied++;
        }

        Notification::make()
            ->title('Availability updated')
            ->body("{$applied} centre(s) updated, {$result['errors']} skipped with errors.")
            ->success()
            ->send();

        // Refresh the preview to reflect what was applied, and clear the textarea.
        $this->preview = $result;
        $this->form->fill(['input' => '']);
    }

    /** Whether the current user may write (Viewers are read-only). */
    public function canWrite(): bool
    {
        return auth()->user()?->role !== \App\Enums\UserRole::Viewer;
    }
}
