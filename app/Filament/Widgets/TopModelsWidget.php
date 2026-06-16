<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use JsonException;

class TopModelsWidget extends ChartWidget
{
    use HasFiltersSchema, HasWidgetShield;

    protected ?string $pollingInterval = '10s';

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('start_date')
                ->label(__('admin.start_date'))
                ->native(false)
                ->default(now()->startOfMonth()->startOfDay()),

            DatePicker::make('end_date')
                ->label(__('admin.end_date'))
                ->native(false)
                ->default(now()->endOfDay()),
        ]);
    }

    protected function getData(): array
    {
        $startDate = $this->filters['start_date'] ?? now()->startOfMonth();
        $endDate = $this->filters['end_date'] ?? now()->endOfDay();

        $data = Product::query()
            ->with('model')
            ->where('status_id', 4)
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfMonth()->startOfDay(),
                Carbon::parse($endDate)->endOfMonth()->endOfDay(),
            ])
            ->selectRaw('
                model_id,
                COUNT(*) as total_sales,
                SUM(COALESCE(sale_price, 0) - COALESCE(price, 0)) as total_profit
            ')
            ->groupBy('model_id')
            ->orderByDesc('total_profit')
            ->limit(10)
            ->get();

        $colors = [
            '#6366F1', '#10B981', '#F59E0B', '#EF4444', '#3B82F6',
            '#8B5CF6', '#14B8A6', '#F43F5E', '#22C55E', '#EAB308',
        ];

        return [
            'datasets' => [
                [
                    'label' => __('admin.total_profit'),
                    'data' => $data->pluck('total_profit')
                        ->map(fn($value) => round($value, 2))
                        ->values(),

                    'backgroundColor' => array_slice($colors, 0, $data->count()),
                    'borderColor' => array_slice($colors, 0, $data->count()),
                ],
            ],

            'labels' => $data->map(function ($item) {
                $name = $item->model->name ?? __('admin.unknown');
                $profit = money($item->total_profit);
                $sales = $item->total_sales;


                return "{$name} ({$profit}) ({$sales})";
            })->values(),
        ];
    }

    public function getHeading(): ?string
    {
        return __('admin.top_sale_models');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @throws JsonException
     */

}