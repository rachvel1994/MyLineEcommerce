<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class TopUsersChartWidget extends ChartWidget
{
    use HasFiltersSchema, HasWidgetShield;

    protected ?string $pollingInterval = '10s';

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('start_date')
                ->label(__('admin.start_date'))
                ->native(false)
                ->default(now()->subDays(30)->startOfDay()),

            DatePicker::make('end_date')
                ->label(__('admin.end_date'))
                ->native(false)
                ->default(now()->endOfDay()),
        ]);
    }

    protected function getData(): array
    {
        $startDate = $this->filters['start_date'] ?? now()->startOfMonth()->startOfDay();
        $endDate = $this->filters['end_date'] ?? now()->endOfMonth()->endOfDay();

        $data = Product::query()
            ->where('status_id', 4)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user')
            ->limit(10)
            ->get();

        $colors = [
            '#6366F1','#10B981','#F59E0B','#EF4444','#3B82F6',
            '#8B5CF6','#14B8A6','#F43F5E','#22C55E','#EAB308',
        ];

        return [
            'datasets' => [
                [
                    'label' => __('admin.top_buyers'),
                    'data' => $data->pluck('total')->values(),
                    'backgroundColor' => array_slice($colors, 0, $data->count()),
                    'borderColor' => array_slice($colors, 0, $data->count()),
                ],
            ],

            'labels' => $data->map(function ($item) {
                $name = $item->user->name ?? __('admin.unknown');

                return $name . ' (' . $item->total . ')';
            })->values(),
        ];
    }

    public function getHeading(): ?string
    {
        return __('admin.top_buyers_analytic');
    }

    protected function getType(): string
    {
        return 'bar';
    }
}