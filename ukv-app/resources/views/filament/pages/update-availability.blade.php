@php
    $prefill = $this->prefillRows();
    $preview = $this->preview;
    $canWrite = $this->canWrite();

    // Flag badge classes (Tailwind utilities shipped with Filament's theme).
    $flagClasses = [
        'missing'   => 'bg-danger-100 text-danger-700 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-400',
        'stale'     => 'bg-danger-100 text-danger-700 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-400',
        'expiring'  => 'bg-warning-100 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400',
        'attention' => 'bg-warning-100 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400',
    ];

    // The ready-to-edit block ops copy/paste/edit.
    $prefillBlock = $prefill->pluck('line')->implode("\n");
@endphp

<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Smart prefill: current centres + last-known values, flagged. --}}
        <x-filament::section>
            <x-slot name="heading">Current centres</x-slot>
            <x-slot name="description">
                Last-known values for each bookable Schengen centre. Copy this block, edit the dates/bands, and paste into the box. Flagged rows need refreshing first.
            </x-slot>

            @if ($prefill->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No bookable Schengen centres found.</p>
            @else
                <div class="space-y-2">
                    @foreach ($prefill as $row)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-white/10">
                            <div class="min-w-0">
                                <code class="font-mono text-gray-900 dark:text-gray-100">{{ $row['line'] }}</code>
                                <span class="ml-2 text-xs text-gray-400">{{ $row['name'] }}</span>
                            </div>
                            @if ($row['flag'])
                                <span
                                    title="{{ $row['note'] }}"
                                    @class([
                                        'shrink-0 inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset',
                                        $flagClasses[$row['flag']] ?? 'bg-gray-100 text-gray-600 ring-gray-500/10 dark:bg-white/5 dark:text-gray-400',
                                    ])
                                >
                                    {{ ucfirst($row['flag']) }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">Ready-to-edit block</p>
                    <textarea
                        readonly
                        rows="{{ max(3, $prefill->count()) }}"
                        onclick="this.select()"
                        class="block w-full rounded-lg border-gray-300 bg-gray-50 font-mono text-xs text-gray-700 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-gray-300"
                    >{{ $prefillBlock }}</textarea>
                </div>
            @endif
        </x-filament::section>

        {{-- Bulk paste + preview + confirm. --}}
        <x-filament::section>
            <x-slot name="heading">Bulk paste</x-slot>

            <form wire:submit="previewBulk">
                {{ $this->form }}

                <div class="mt-4 flex gap-3">
                    <x-filament::button type="submit" color="gray" icon="heroicon-o-eye">
                        Preview
                    </x-filament::button>

                    <x-filament::button
                        wire:click="applyBulk"
                        type="button"
                        icon="heroicon-o-check"
                        :disabled="! $canWrite"
                    >
                        Apply
                    </x-filament::button>
                </div>
            </form>

            @if (! $canWrite)
                <p class="mt-3 text-xs text-danger-600 dark:text-danger-400">Read-only: you cannot apply changes.</p>
            @endif

            {{-- Validation summary. --}}
            @if ($preview !== null)
                <div class="mt-6 border-t border-gray-200 pt-4 dark:border-white/10">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        Preview: {{ $preview['ok'] }} valid, {{ $preview['errors'] }} with errors
                    </p>

                    <div class="mt-3 space-y-1">
                        @foreach ($preview['rows'] as $row)
                            <div @class([
                                'flex items-start gap-2 rounded-md px-2 py-1 text-xs',
                                'bg-success-50 dark:bg-success-400/10' => $row['error'] === null,
                                'bg-danger-50 dark:bg-danger-400/10' => $row['error'] !== null,
                            ])>
                                @if ($row['error'] === null)
                                    <x-heroicon-o-check-circle class="mt-0.5 h-4 w-4 shrink-0 text-success-500" />
                                @else
                                    <x-heroicon-o-x-circle class="mt-0.5 h-4 w-4 shrink-0 text-danger-500" />
                                @endif
                                <div class="min-w-0">
                                    <span class="font-mono font-medium text-gray-900 dark:text-gray-100">{{ $row['slug'] }}</span>
                                    @if ($row['error'] !== null)
                                        <span class="text-danger-700 dark:text-danger-400"> — {{ $row['error'] }}</span>
                                    @elseif ($row['reset'])
                                        <span class="text-gray-500 dark:text-gray-400"> — reset to "ask" ({{ $row['destination'] }})</span>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400"> — {{ $row['next_available_on'] }} {{ $row['band'] }} ({{ $row['destination'] }})</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
