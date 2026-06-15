<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

/**
 * Production Line — a kanban board of orders grouped by pipeline stage.
 *
 * Columns are the OrderStatus pipeline stages. Each column lists the orders
 * currently resting at that status (destination eager-loaded, capped per column
 * so a busy stage cannot blow up the page). A per-card "Advance" action moves an
 * order to its next linear stage through OrderService::transition(), surfacing any
 * DomainException (stage gate / eligibility / QA block) as a Filament notification.
 */
class ProductionBoard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Production Line';

    protected static ?string $title = 'Production Line';

    protected static string $view = 'filament.pages.production-board';

    /** Max orders rendered per column (newest-due first). */
    public const PER_COLUMN = 25;

    /**
     * The linear "next" stage for the Advance action. Terminal off-ramps
     * (refunded / rejected) are deliberately excluded — advancing only ever
     * moves an order one step down the production line, never to a refund/reject.
     *
     * @var array<string, OrderStatus|null>
     */
    private const NEXT_STAGE = [
        'paid' => OrderStatus::AwaitingDocs,
        'awaiting_docs' => OrderStatus::DocReview,
        'doc_review' => OrderStatus::Submitted,
        'submitted' => OrderStatus::AwaitingDecision,
        'awaiting_decision' => OrderStatus::Delivered,
        'delivered' => OrderStatus::Won,
        'won' => null,
        'rejected' => null,
        'refunded' => null,
    ];

    /**
     * Stages rendered as columns, left → right along the production line.
     * Every OrderStatus case is shown so nothing is hidden, including the
     * terminal won / rejected / refunded buckets.
     *
     * @return list<OrderStatus>
     */
    public function stages(): array
    {
        return OrderStatus::cases();
    }

    /**
     * Orders grouped by status, keyed by the status value, each capped at
     * PER_COLUMN and ordered by due date then most-recently-touched.
     *
     * @return array<string, \Illuminate\Support\Collection<int, \App\Models\Order>>
     */
    public function ordersByStage(): array
    {
        $grouped = [];

        foreach ($this->stages() as $stage) {
            $grouped[$stage->value] = Order::query()
                ->where('status', $stage->value)
                ->with('destination')
                ->orderByRaw('next_due is null, next_due asc')
                ->orderByDesc('updated_at')
                ->limit(self::PER_COLUMN)
                ->get();
        }

        return $grouped;
    }

    /**
     * Total count per stage (independent of the per-column display cap), so a
     * column heading can read e.g. "Doc Review (42)" even when only 25 cards show.
     *
     * @return array<string, int>
     */
    public function stageCounts(): array
    {
        return Order::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();
    }

    /** The next linear stage label for a card's Advance button, or null at a terminal stage. */
    public function nextStageLabel(Order $order): ?string
    {
        $next = $this->nextStageFor($order);

        return $next?->name;
    }

    /** Resolve the linear next stage for an order, or null if terminal. */
    private function nextStageFor(Order $order): ?OrderStatus
    {
        $current = $order->status instanceof OrderStatus
            ? $order->status->value
            : (string) $order->status;

        return self::NEXT_STAGE[$current] ?? null;
    }

    /**
     * Livewire action: advance an order one stage down the line.
     * Delegates the gate enforcement to OrderService::transition(); a blocked
     * move throws DomainException, which we turn into a danger notification.
     */
    public function advance(int $orderId): void
    {
        $order = Order::query()->find($orderId);

        if ($order === null) {
            Notification::make()
                ->title('Order not found')
                ->danger()
                ->send();

            return;
        }

        $next = $this->nextStageFor($order);

        if ($next === null) {
            Notification::make()
                ->title('Cannot advance')
                ->body("Order {$order->order_ref} is already at a terminal stage.")
                ->warning()
                ->send();

            return;
        }

        try {
            app(OrderService::class)->transition($order, $next);

            Notification::make()
                ->title('Stage advanced')
                ->body("{$order->order_ref} moved to {$next->name}.")
                ->success()
                ->send();
        } catch (\DomainException $e) {
            Notification::make()
                ->title('Move blocked')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
