<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\ExpenseType;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class ExpenseStatsWidget extends Widget implements HasForms
{
    use HasWidgetShield, InteractsWithForms;

    protected string $view = 'filament.widgets.expense-stats-widget';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = true;

    public ?array $filters = [];

    protected function getFormSchema(): array
    {
        return [
            Grid::make()->schema([
                DatePicker::make('from_date')
                    ->label(__('admin.from_date'))
                    ->default(now()->startOfMonth()),

                DatePicker::make('to_date')
                    ->label(__('admin.to_date'))
                    ->default(now()),
            ])->columnSpanFull(),
        ];
    }

    protected function getFormStatePath(): ?string
    {
        return 'filters';
    }

    public function applyFilters(): void {}

    public function resetFilters(): void
    {
        $this->filters = [];

        $this->form->fill($this->filters);

        $this->dispatch('$refresh');
    }

    public function getStats(): array
    {
        $from = filled($this->filters['from_date'] ?? null)
            ? Carbon::parse($this->filters['from_date'])->startOfDay()
            : now()->startOfMonth()->startOfDay();
        $to = filled($this->filters['to_date'] ?? null)
            ? Carbon::parse($this->filters['to_date'])->endOfDay()
            : now()->endOfMonth()->endOfDay();

        $base = Expense::query()
            ->whereBetween('spent_at', [$from, $to]);

        $total = (clone $base)->sum('amount') ?? 0;

        $byType = ExpenseType::query()
            ->withSum(['expenses as sum_amount' => function ($q) use ($from, $to) {
                $q->whereBetween('spent_at', [$from, $to]);
            }], 'amount')
            ->orderByDesc('sum_amount')
            ->get()
            ->map(fn ($t) => [
                'label' => $t->name,
                'value' => money($t->sum_amount ?? 0),
                'icon' => Heroicon::Tag,
            ])
            ->toArray();

        return array_merge([
            [
                'label' => __('admin.total_price'),
                'value' => money($total),
                'icon' => Heroicon::Banknotes,
            ],
        ], $byType);
    }

    public function getHeading(): ?string
    {
        return __('admin.expense_static_info');
    }
}
