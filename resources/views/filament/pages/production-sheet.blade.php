<x-filament-panels::page>
    <div class="space-y-4">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Download customized items in confirmed and in-production orders, grouped one row per order item.
        </p>
        <x-filament::button tag="a" :href="route('admin.production-sheet')" icon="heroicon-o-arrow-down-tray">
            Download production CSV
        </x-filament::button>
    </div>
</x-filament-panels::page>
