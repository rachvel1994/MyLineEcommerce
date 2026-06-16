<?php

namespace App\Filament\Widgets;

use App\Models\ServiceRepairHistory;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;

class RepairHistoryWidget extends Widget implements HasForms
{
    use InteractsWithForms, HasWidgetShield;

    protected string $view = 'filament.widgets.repair-history-widget';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public ?array $filters = [];

	protected function getListeners(): array
	{
		return [
			'refreshRepairWidget' => '$refresh',
		];
	}

    /**
     * FILTER FORM
     */
    protected function getFormSchema(): array
    {
        return [
            Grid::make()->schema([

                DatePicker::make('from_date')
                    ->label(__('admin.from_date'))
                    ->default(now()->startOfMonth())
                    ->live()
                    ->afterStateUpdated(fn () => $this->dispatch('$refresh')),

                DatePicker::make('to_date')
                    ->label(__('admin.to_date'))
                    ->default(now())
                    ->live()
                    ->afterStateUpdated(fn () => $this->dispatch('$refresh')),

            ])->columnSpanFull(),
        ];
    }

    protected function getFormStatePath(): ?string
    {
        return 'filters';
    }

    /**
     * RESET FILTERS
     */
    public function resetFilters(): void
    {
        $this->filters = [
            'from_date' => now()->startOfMonth(),
            'to_date' => now()->endOfDay(),
        ];

        $this->form->fill($this->filters);

        $this->dispatch('$refresh');
    }

    /**
     * STATS CALCULATION
     */
    public function getStats(): array
    {
        $from = $this->filters['from_date'] ?? now()->startOfMonth()->startOfDay();
        $to = $this->filters['to_date'] ?? now()->endOfMonth()->endOfDay();

        $base = ServiceRepairHistory::query()
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));

        $total = (clone $base)->sum('repair_price');

        $paid = (clone $base)
            ->whereHas('repair', fn ($q) => $q->where('is_payed', true))
            ->sum('repair_price');

        $debt = max(0, $total - $paid);

        return [
            [
                'label' => __('admin.total'),
                'value' => money($total),
                'icon' => Heroicon::Wrench,
                'color' => 'success',
            ],
            [
                'label' => __('admin.is_payed'),
                'value' => money($paid),
                'icon' => Heroicon::Banknotes,
                'color' => 'primary',
            ],
            [
                'label' => __('admin.debt'),
                'value' => money($debt),
                'icon' => Heroicon::ExclamationCircle,
                'color' => 'danger',
            ],
        ];
    }

    /**
     * HEADING
     */
    public function getHeading(): ?string
    {
        return __('admin.repair_static_info');
    }
}
