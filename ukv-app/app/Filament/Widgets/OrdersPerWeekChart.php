<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Orders created per week over the trailing 12 weeks (a simple throughput trend).
 *
 * Read-only. Buckets orders by the Monday of their created_at week so the
 * x-axis reads as week-commencing dates. Weeks with no orders still appear
 * (zero-filled) so the trend line never silently collapses gaps.
 */
class OrdersPerWeekChart extends ChartWidget
{
    protected static ?string $heading = 'Orders per week (last 12 weeks)';

    protected static ?int $sort = 1;

    /** Span half the dashboard width alongside other widgets. */
    protected int|string|array $columnSpan = 'full';

    private const WEEKS = 12;

    protected function getData(): array
    {
        $start = Carbon::now()->startOfWeek()->subWeeks(self::WEEKS - 1);

        // Seed every week bucket to zero so empty weeks render.
        $buckets = [];
        for ($i = 0; $i < self::WEEKS; $i++) {
            $key = $start->copy()->addWeeks($i)->format('Y-m-d');
            $buckets[$key] = 0;
        }

        Order::query()
            ->where('created_at', '>=', $start)
            ->select(['id', 'created_at']) // id required: chunkById paginates by it
            ->chunkById(1000, function ($chunk) use (&$buckets): void {
                foreach ($chunk as $order) {
                    if ($order->created_at === null) {
                        continue;
                    }
                    $key = $order->created_at->copy()->startOfWeek()->format('Y-m-d');
                    if (array_key_exists($key, $buckets)) {
                        $buckets[$key]++;
                    }
                }
            });

        $labels = array_map(
            static fn (string $d): string => Carbon::parse($d)->format('d M'),
            array_keys($buckets)
        );

        return [
            'datasets' => [
                [
                    'label' => 'Orders created',
                    'data' => array_values($buckets),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }
}
