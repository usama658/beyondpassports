<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            <span class="font-semibold text-gray-950 dark:text-white">{{ number_format($this->matchingCount()) }}</span>
            order(s) match the current filters.
        </p>

        <x-filament::button
            wire:click="exportCsv"
            icon="heroicon-m-arrow-down-tray"
            color="primary"
        >
            Export CSV
        </x-filament::button>
    </div>
</x-filament-panels::page>
