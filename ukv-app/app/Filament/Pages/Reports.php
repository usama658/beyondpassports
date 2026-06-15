<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reports — a lightweight orders export surface.
 *
 * Mirrors the WP Reports page (ukv-reports.php): a filterable order list with a
 * single "Export CSV" action. Here the filters are date-from / date-to (against
 * created_at) and an optional status, and the export is a Laravel streamed
 * response so a large order book never has to be buffered into memory.
 *
 * Read-only: no order is mutated. The CSV columns match the WP export
 * (ref, name, email, destination, tier, status, total, created) so downstream
 * spreadsheets/templates keep working.
 */
class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $title = 'Reports';

    protected static string $view = 'filament.pages.reports';

    /**
     * Filter form state.
     *
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        // Default window: the current calendar month to today.
        $this->form->fill([
            'from' => Carbon::now()->startOfMonth()->toDateString(),
            'to' => Carbon::now()->toDateString(),
            'status' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('from')
                    ->label('Created from')
                    ->native(false),
                DatePicker::make('to')
                    ->label('Created to')
                    ->native(false),
                Select::make('status')
                    ->label('Status')
                    ->placeholder('All statuses')
                    ->options(collect(OrderStatus::cases())
                        ->mapWithKeys(fn (OrderStatus $s): array => [$s->value => $s->name])
                        ->all()),
            ])
            ->columns(3)
            ->statePath('data');
    }

    /** Live preview count of orders matching the current filters. */
    public function matchingCount(): int
    {
        return $this->buildQuery()->count();
    }

    /**
     * Build the filtered orders query from the current form state.
     */
    private function buildQuery(): Builder
    {
        $state = $this->form->getState();

        return Order::query()
            ->with('destination')
            ->when(
                ! empty($state['from']),
                fn (Builder $q) => $q->whereDate('created_at', '>=', $state['from'])
            )
            ->when(
                ! empty($state['to']),
                fn (Builder $q) => $q->whereDate('created_at', '<=', $state['to'])
            )
            ->when(
                ! empty($state['status']),
                fn (Builder $q) => $q->where('status', $state['status'])
            )
            ->orderByDesc('created_at');
    }

    /**
     * Stream the filtered orders as a CSV download.
     *
     * Rows are streamed in chunks via fputcsv so the response never materialises
     * the whole result set in memory.
     */
    public function exportCsv(): StreamedResponse
    {
        $query = $this->buildQuery();
        $filename = 'ukv-orders-'.Carbon::now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache',
        ];

        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['ref', 'name', 'email', 'destination', 'tier', 'status', 'total', 'created']);

            $query->chunkById(500, function ($chunk) use ($out): void {
                foreach ($chunk as $order) {
                    /** @var Order $order */
                    $status = $order->status instanceof OrderStatus
                        ? $order->status->name
                        : (string) $order->status;

                    fputcsv($out, [
                        $order->order_ref,
                        $order->name,
                        $order->email,
                        $order->destination_name,
                        $order->tier?->value ?? (string) $order->tier,
                        $status,
                        $order->total,
                        $order->created_at?->format('Y-m-d') ?? '',
                    ]);
                }
            });

            fclose($out);
        }, $filename, $headers);
    }
}
