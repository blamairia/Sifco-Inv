<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Widgets --}}
        <div class="grid gap-6 lg:grid-cols-3">
            @foreach ($this->getHeaderWidgets() as $widget)
                @livewire($widget)
            @endforeach
        </div>

        {{-- Main Table --}}
        <div class="filament-page-section">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
