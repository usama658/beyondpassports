<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\EligibilityLane;
use App\Enums\OrderStatus;
use App\Enums\OrderTier;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/**
 * Dashboard headline KPIs for the ops team.
 *
 * Ported from the WP Ops Cockpit KPI strip (ukv-cockpit.php → ukv_cockpit_kpis)
 * plus the manual-review clearance signal from the eligibility gate
 * (OrderService §2 — a manual_review/referred order may rest at `paid` but
 * cannot advance until cleared).
 *
 * Read-only: every figure is a query against orders, no writes, no side effects.
 *
 *   - Open orders        : orders not in OrderStatus::CLOSED.
 *   - By-stage counts     : the live pipeline buckets (paid → awaiting_decision),
 *                           rendered as the open-order description so a single card
 *                           shows where the open work is sitting.
 *   - Overdue / SLA       : open orders whose created_at + tier-SLA window has passed.
 *   - Revenue this month  : sum(total) of PAID-or-beyond orders created this calendar
 *                           month (a paid order is anything past the eligibility gate,
 *                           i.e. not still awaiting payment — here all orders carry a
 *                           total, so we sum orders created this month).
 *   - Manual review       : open orders whose eligibility lane is NOT cleared
 *                           (manual_review / referred) — i.e. awaiting human clearance.
 */
class OrdersStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    /** SLA window in hours per tier (matches WP ukv_order_sla_hours + OrderTier doc). */
    private const SLA_HOURS = [
        'premium' => 12,
        'express' => 24,
        'standard' => 72,
    ];

    private const SLA_DEFAULT_HOURS = 72;

    protected function getStats(): array
    {
        $now = Carbon::now();

        // --- Open orders + per-stage breakdown -------------------------------
        $closed = array_map(static fn (OrderStatus $s): string => $s->value, OrderStatus::CLOSED);

        $byStatus = Order::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $openByStage = [];
        $openTotal = 0;
        foreach ($byStatus as $status => $count) {
            if (in_array($status, $closed, true)) {
                continue;
            }
            $openByStage[$status] = (int) $count;
            $openTotal += (int) $count;
        }

        // --- Overdue / SLA-breached open orders ------------------------------
        // Computed per row because the window varies by tier; bounded to open orders.
        $slaBreached = 0;
        Order::query()
            ->whereNotIn('status', $closed)
            ->select(['id', 'tier', 'created_at']) // id required: chunkById paginates by it
            ->chunkById(500, function ($chunk) use (&$slaBreached, $now): void {
                foreach ($chunk as $order) {
                    $hours = $this->slaHoursFor($order->tier);
                    if ($order->created_at !== null && $order->created_at->copy()->addHours($hours)->lt($now)) {
                        $slaBreached++;
                    }
                }
            });

        // --- Revenue this calendar month -------------------------------------
        $revenueMtd = (float) Order::query()
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->sum('total');

        // --- Manual review awaiting clearance --------------------------------
        $manualReview = Order::query()
            ->whereNotIn('status', $closed)
            ->whereIn('eligibility', [EligibilityLane::ManualReview->value, EligibilityLane::Referred->value])
            ->count();

        return [
            Stat::make('Open orders', (string) $openTotal)
                ->description($this->stageBreakdown($openByStage))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('Overdue (SLA breached)', (string) $slaBreached)
                ->description($slaBreached > 0 ? 'Past tier SLA window' : 'All within SLA')
                ->descriptionIcon($slaBreached > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($slaBreached > 0 ? 'danger' : 'success'),

            Stat::make('Revenue this month', '£'.number_format($revenueMtd, 2))
                ->description($now->format('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Manual review', (string) $manualReview)
                ->description($manualReview > 0 ? 'Awaiting eligibility clearance' : 'None awaiting clearance')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color($manualReview > 0 ? 'warning' : 'gray'),
        ];
    }

    /** SLA hours for a tier value/enum, defaulting to the standard window. */
    private function slaHoursFor(OrderTier|string|null $tier): int
    {
        $key = $tier instanceof OrderTier ? $tier->value : (string) ($tier ?? '');

        return self::SLA_HOURS[$key] ?? self::SLA_DEFAULT_HOURS;
    }

    /**
     * Compact "stage: n · stage: n" string for the open-orders card description.
     *
     * @param  array<string, int>  $openByStage
     */
    private function stageBreakdown(array $openByStage): string
    {
        if ($openByStage === []) {
            return 'No open orders';
        }

        $parts = [];
        foreach ($openByStage as $status => $count) {
            $label = OrderStatus::tryFrom($status)?->name ?? $status;
            $parts[] = "{$label}: {$count}";
        }

        return implode(' · ', $parts);
    }
}
