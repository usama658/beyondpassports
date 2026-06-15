<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Trip-group / linked-orders logic (L2.7 / #177).
 *
 * Policy (ported from WP ukv-order-groups.php): every traveller on the same trip gets their
 * OWN order (per-person docs/fees, clean tracking). This service LINKS those orders via a
 * shared, deterministic group id stored on the existing Order.group_id column (string(16),
 * "GRP-XXXXXXXX", a self-group token — NOT a foreign key), and exposes helpers to query and
 * create members.
 *
 * Determinism: the group id is derived from the sorted, de-duplicated member order ids (md5,
 * NOT random / time) so linking the same travellers always yields the same id regardless of
 * call order — exact parity with WP ukv_group_id_for().
 */
final class GroupService
{
    public function __construct(
        private readonly OrderService $orders,
    ) {}

    /**
     * Deterministic group id for a set of order ids.
     * GRP-<8 hex> from md5 of the sorted, unique, positive ids. '' for an empty set.
     */
    public function groupIdFor(array $orderIds): string
    {
        $ids = $this->normaliseIds($orderIds);
        if ($ids === []) {
            return '';
        }

        $hash = substr(md5(implode('-', $ids)), 0, 8);

        return 'GRP-'.strtoupper($hash);
    }

    /**
     * Link a set of orders into one trip group.
     *
     * Derives the deterministic id, writes it to each order's group_id, and records an
     * internal OrderEvent on each. Returns the group id ('' when no valid orders supplied).
     *
     * @param  array<int|Order>  $orders  order ids or models
     */
    public function link(array $orders): string
    {
        $ids = $this->normaliseIds(array_map(
            static fn ($o) => $o instanceof Order ? $o->getKey() : $o,
            $orders,
        ));
        if ($ids === []) {
            return '';
        }

        $gid = $this->groupIdFor($ids);

        DB::transaction(function () use ($ids, $gid): void {
            foreach (Order::query()->whereIn('id', $ids)->get() as $order) {
                if ($order->group_id === $gid) {
                    continue; // already linked — keep it idempotent, no duplicate note
                }

                $order->group_id = $gid;
                $order->save();

                $this->orders->recordEvent(
                    $order,
                    EventType::System,
                    "Linked to trip group {$gid}.",
                    channel: EventChannel::Internal,
                    meta: ['group_id' => $gid],
                );
            }
        });

        return $gid;
    }

    /**
     * Create a new order from intake AND link it into a group with the given existing orders
     * (a family/party booking: add the next traveller to an established trip).
     *
     * @param  array<string, mixed>  $intake
     * @param  array<int|Order>  $linkWith  existing orders to group with the new one
     */
    public function createLinkedOrder(array $intake, array $linkWith = []): Order
    {
        $order = $this->orders->createFromIntake($intake);

        $members = array_merge([$order], $linkWith);
        $this->link($members);

        return $order->refresh();
    }

    /** The group id for an order ('' when solo / unlinked). */
    public function groupId(Order $order): string
    {
        return (string) ($order->group_id ?? '');
    }

    /**
     * All orders in a group, ordered by id ascending. Empty for an empty/blank group id.
     *
     * @return Collection<int, Order>
     */
    public function members(string $groupId): Collection
    {
        $groupId = trim($groupId);
        if ($groupId === '') {
            return new Collection;
        }

        return Order::query()
            ->where('group_id', $groupId)
            ->orderBy('id')
            ->get();
    }

    /**
     * Other orders in the same group as the given order (excludes it). Empty when solo.
     *
     * @return Collection<int, Order>
     */
    public function siblings(Order $order): Collection
    {
        $gid = $this->groupId($order);
        if ($gid === '') {
            return new Collection;
        }

        return $this->members($gid)->reject(
            fn (Order $o): bool => $o->getKey() === $order->getKey(),
        )->values();
    }

    /**
     * Normalise to sorted, unique, positive int ids.
     *
     * @param  array<mixed>  $ids
     * @return list<int>
     */
    private function normaliseIds(array $ids): array
    {
        $clean = array_values(array_unique(array_filter(
            array_map(static fn ($i): int => (int) $i, $ids),
            static fn (int $i): bool => $i > 0,
        )));

        sort($clean, SORT_NUMERIC);

        return $clean;
    }
}
