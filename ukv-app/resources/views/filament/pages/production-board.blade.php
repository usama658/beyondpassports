@php
    use App\Enums\OrderStatus;
    use App\Enums\OrderPriority;

    $stages = $this->stages();
    $ordersByStage = $this->ordersByStage();
    $counts = $this->stageCounts();

    $hasOrderResource = class_exists(\App\Filament\Resources\OrderResource::class);

    // Per-priority badge classes (Tailwind utilities shipped with Filament's theme).
    $priorityClasses = [
        OrderPriority::Urgent->value => 'bg-danger-100 text-danger-700 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-400',
        OrderPriority::High->value   => 'bg-warning-100 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400',
        OrderPriority::Normal->value => 'bg-gray-100 text-gray-600 ring-gray-500/10 dark:bg-white/5 dark:text-gray-400',
    ];

    // Accent strip per stage so columns read at a glance, terminal buckets dimmed.
    $stageAccent = [
        OrderStatus::Paid->value             => 'bg-gray-400',
        OrderStatus::AwaitingDocs->value     => 'bg-amber-400',
        OrderStatus::DocReview->value        => 'bg-yellow-400',
        OrderStatus::Submitted->value        => 'bg-blue-400',
        OrderStatus::AwaitingDecision->value => 'bg-indigo-400',
        OrderStatus::Delivered->value        => 'bg-cyan-400',
        OrderStatus::Won->value              => 'bg-success-500',
        OrderStatus::Rejected->value         => 'bg-danger-500',
        OrderStatus::Refunded->value         => 'bg-gray-500',
    ];
@endphp

<x-filament-panels::page>
    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach ($stages as $stage)
            @php
                $orders = $ordersByStage[$stage->value] ?? collect();
                $total = (int) ($counts[$stage->value] ?? 0);
                $shown = $orders->count();
                $accent = $stageAccent[$stage->value] ?? 'bg-gray-400';
            @endphp

            <div class="flex w-72 shrink-0 flex-col rounded-xl bg-gray-50 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                {{-- Column heading --}}
                <div class="flex items-center justify-between gap-2 border-b border-gray-200 px-3 py-2.5 dark:border-white/10">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="size-2.5 shrink-0 rounded-full {{ $accent }}"></span>
                        <span class="truncate text-sm font-semibold text-gray-700 dark:text-gray-200">
                            {{ str(str_replace('_', ' ', $stage->value))->title() }}
                        </span>
                    </div>
                    <span class="shrink-0 rounded-md bg-gray-200 px-1.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-300">
                        {{ $total }}
                    </span>
                </div>

                {{-- Cards --}}
                <div class="flex flex-col gap-2 overflow-y-auto p-2" style="max-height: 70vh;">
                    @forelse ($orders as $order)
                        @php
                            $priority = $order->priority instanceof OrderPriority
                                ? $order->priority->value
                                : (string) ($order->priority ?? OrderPriority::Normal->value);
                            $badgeClass = $priorityClasses[$priority] ?? $priorityClasses[OrderPriority::Normal->value];
                            $age = $order->created_at ? $order->created_at->diffForHumans(null, true) : '—';
                            $nextLabel = $this->nextStageLabel($order);
                            $editUrl = $hasOrderResource
                                ? \App\Filament\Resources\OrderResource::getUrl('edit', ['record' => $order])
                                : null;
                        @endphp

                        <div class="rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-950/5 transition hover:ring-primary-500/40 dark:bg-gray-800 dark:ring-white/10">
                            {{-- Ref + priority --}}
                            <div class="flex items-start justify-between gap-2">
                                @if ($editUrl)
                                    <a href="{{ $editUrl }}"
                                       class="truncate text-sm font-semibold text-primary-600 hover:underline dark:text-primary-400">
                                        {{ $order->order_ref }}
                                    </a>
                                @else
                                    <span class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">
                                        {{ $order->order_ref }}
                                    </span>
                                @endif

                                <span class="shrink-0 inline-flex items-center rounded-md px-1.5 py-0.5 text-[0.65rem] font-medium uppercase tracking-wide ring-1 ring-inset {{ $badgeClass }}">
                                    {{ $priority }}
                                </span>
                            </div>

                            {{-- Destination --}}
                            <div class="mt-1.5 flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300">
                                <x-filament::icon icon="heroicon-m-map-pin" class="size-3.5 shrink-0 text-gray-400" />
                                <span class="truncate">
                                    {{ $order->destination?->name ?? $order->destination_name ?? '—' }}
                                </span>
                            </div>

                            {{-- Customer --}}
                            <div class="mt-1 flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                <x-filament::icon icon="heroicon-m-user" class="size-3.5 shrink-0 text-gray-400" />
                                <span class="truncate">{{ $order->name ?? $order->applicant_name ?? '—' }}</span>
                            </div>

                            {{-- Footer: age + advance --}}
                            <div class="mt-2.5 flex items-center justify-between gap-2 border-t border-gray-100 pt-2 dark:border-white/5">
                                <span class="text-[0.65rem] text-gray-400" title="{{ $order->created_at }}">
                                    {{ $age }} old
                                </span>

                                @if ($nextLabel)
                                    <button
                                        type="button"
                                        wire:click="advance({{ $order->getKey() }})"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center gap-1 rounded-md bg-primary-600 px-2 py-1 text-[0.7rem] font-medium text-white shadow-sm transition hover:bg-primary-500 disabled:opacity-50"
                                    >
                                        <span>{{ str(str_replace('_', ' ', \Illuminate\Support\Str::snake($nextLabel)))->title() }}</span>
                                        <x-filament::icon icon="heroicon-m-arrow-right" class="size-3" />
                                    </button>
                                @else
                                    <span class="text-[0.65rem] font-medium uppercase tracking-wide text-gray-400">terminal</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-300 p-4 text-center text-xs text-gray-400 dark:border-white/10">
                            No orders
                        </div>
                    @endforelse

                    @if ($total > $shown)
                        <div class="px-1 pt-1 text-center text-[0.65rem] text-gray-400">
                            + {{ $total - $shown }} more not shown
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
