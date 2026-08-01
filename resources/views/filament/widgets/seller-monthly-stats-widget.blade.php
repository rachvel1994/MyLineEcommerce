@php
    $pollingInterval = $this->getPollingInterval();
@endphp

<x-filament-widgets::widget
    :attributes="
        (new \Illuminate\View\ComponentAttributeBag)
            ->merge([
                'wire:poll.' . $pollingInterval => $pollingInterval ? true : null,
            ], escape: false)
            ->class(['fi-wi-stats-overview'])
    "
>
    <div class="space-y-4">
        <div class="w-full">
            <x-filament::section compact>
                <div class="flex items-end gap-3">
                    <div class="min-w-0 w-full max-w-2xl">
                        {{ $this->getFiltersSchema() }}
                    </div>

                    <x-filament::button
                        class="shrink-0"
                        color="info"
                        icon="heroicon-o-arrow-path"
                        size="sm"
                        type="button"
                        wire:click="resetDateFilters"
                        wire:loading.attr="disabled"
                        wire:target="resetDateFilters"
                    >
                        {{ __('admin.reset_filter') }}
                    </x-filament::button>
                </div>
            </x-filament::section>
        </div>

        {{ $this->content }}
    </div>
</x-filament-widgets::widget>
