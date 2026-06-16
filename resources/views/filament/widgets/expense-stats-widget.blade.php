<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('admin.expense_stats') }}
        </x-slot>

        <style>
            /* FORM */
            .expense-form {
                display: grid;
                grid-template-columns: 1fr auto auto;
                gap: 10px;
                align-items: end;
                margin-bottom: 16px;
            }

            @media (max-width: 768px) {
                .expense-form {
                    grid-template-columns: 1fr;
                }
            }

            /* GRID (no slider anymore → cleaner UX) */
            .expense-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
                gap: 14px;
            }

            /* CARD */
            .expense-card {
                border-radius: 14px;
                padding: 14px;
                background: rgba(255, 255, 255, 0.8);
                border: 1px solid rgb(229 231 235);
                transition: all 0.2s ease;
            }

            .dark .expense-card {
                background: rgba(31, 41, 55, 0.5);
                border-color: rgb(55 65 81);
            }

            .expense-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            }

            /* CONTENT */
            .expense-top {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .expense-label {
                font-size: 12px;
                color: rgb(107 114 128);
            }

            .dark .expense-label {
                color: rgb(156 163 175);
            }

            .expense-value {
                font-size: 20px;
                font-weight: 700;
                margin-top: 4px;
                color: rgb(17 24 39);
            }

            .dark .expense-value {
                color: white;
            }

            /* ICON */
            .expense-icon {
                width: 40px;
                height: 40px;
                padding: 8px;
                border-radius: 12px;
                background: rgb(239 246 255);
                color: rgb(37 99 235);
            }

            .dark .expense-icon {
                background: rgba(59, 130, 246, 0.15);
                color: rgb(96 165 250);
            }
        </style>

        <!-- FILTER FORM -->
        <form wire:submit.prevent="applyFilters" class="expense-form">
            {{ $this->form }}

            <x-filament::button type="submit" size="sm">
                {{ __('admin.filter') }}
            </x-filament::button>

            <x-filament::button type="button" size="sm" color="gray" wire:click="resetFilters">
                {{ __('admin.reset') }}
            </x-filament::button>
        </form>

        <!-- STATS GRID -->
        <div class="expense-grid">
            @foreach ($this->getStats() as $stat)
                <div class="expense-card" wire:key="expense-stat-{{ $loop->index }}">

                    <div>
                        <div class="expense-label">
                            {{ $stat['label'] }}
                        </div>

                        <div class="expense-value">
                            {{ $stat['value'] }}
                        </div>
                    </div>

                    <x-filament::icon
                            :icon="$stat['icon'] ?? 'heroicon-o-banknotes'"
                            class="expense-icon"
                    />

                </div>
            @endforeach
        </div>

    </x-filament::section>
</x-filament-widgets::widget>