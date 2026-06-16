<x-filament-widgets::widget>
    <x-filament::section :heading="__('admin.expense_stats')">

        <style>
            .expense-stats-form {
                display: flex;
                flex-wrap: wrap;
                align-items: flex-end;
                gap: 12px;
            }

            .expense-stats-form-fields {
                flex: 1 1 auto;
                min-width: 250px;
            }

            .expense-stats-form-actions {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
            }

            .expense-stats-grid {
                margin-top: 16px;
                display: grid;
                grid-template-columns: repeat(1, minmax(0, 1fr));
                gap: 16px;
            }

            .expense-stat-card-body {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }

            .expense-stat-label {
                font-size: 0.875rem;
                color: rgb(107 114 128);
            }

            .expense-stat-value {
                margin-top: 4px;
                font-size: 1.25rem;
                font-weight: 600;
            }

            .expense-stat-icon {
                width: 24px;
                height: 24px;
                flex-shrink: 0;
            }

            @media (min-width: 768px) {
                .expense-stats-grid {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
            }
        </style>

        {{-- FILTER FORM --}}
        <form wire:submit.prevent="applyFilters" class="expense-stats-form">
            <div class="expense-stats-form-fields">
                {{ $this->form }}
            </div>

            <div class="expense-stats-form-actions">
                <x-filament::button type="submit">
                    {{ __('admin.filter') }}
                </x-filament::button>

                <x-filament::button
                    type="button"
                    color="gray"
                    wire:click="resetFilters"
                >
                    {{ __('admin.reset') }}
                </x-filament::button>
            </div>
        </form>

        {{-- STATS --}}
        <div class="expense-stats-grid">
            @forelse ($this->getStats() as $stat)
                <x-filament::card :wire:key="'expense-stat-'.$loop->index">
                    <div class="expense-stat-card-body">
                        <div>
                            <div class="expense-stat-label">
                                {{ $stat['label'] }}
                            </div>

                            <div class="expense-stat-value">
                                {{ $stat['value'] }}
                            </div>
                        </div>

                        <x-filament::icon
                            :icon="$stat['icon'] ?? 'heroicon-o-chart-bar'"
                            @class([
                                'expense-stat-icon',
                                'text-success-600' => ($stat['color'] ?? '') === 'success',
                                'text-danger-600' => ($stat['color'] ?? '') === 'danger',
                                'text-warning-600' => ($stat['color'] ?? '') === 'warning',
                                'text-primary-600' => ($stat['color'] ?? '') === 'primary',
                            ])
                        />
                    </div>
                </x-filament::card>
            @empty
                <x-filament::card>
                    {{ __('admin.no_data') }}
                </x-filament::card>
            @endforelse
        </div>

    </x-filament::section>
</x-filament-widgets::widget>
