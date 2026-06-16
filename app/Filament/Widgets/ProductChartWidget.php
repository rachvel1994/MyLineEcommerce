<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class ProductChartWidget extends ChartWidget
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
                ->default(now()->endOfMonth()->endOfDay()),
        ]);
    }

    protected function getData(): array
    {
        $startDate = $this->filters['start_date'] ?? now()->startOfMonth()->startOfDay();
        $endDate = $this->filters['end_date'] ?? now()->endOfMonth()->endOfDay();

        $data = Product::query()
            ->where('status_id', 4)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("
                DATE(created_at) as date,
                COUNT(*) as total,
                SUM(sale_price) as sales,
                SUM(sale_price - price) as profit
            ")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('admin.sold_product'),
                    'data' => $data->pluck('total'),
                    'backgroundColor' => '#19cd48',
                    'borderColor' => '#19cd48',
                ],
                [
                    'label' => __('admin.income'),
                    'data' => $data->pluck('sales'),
                    'backgroundColor' => '#F02700',
                    'borderColor' => '#F02700',
                ],
                [
                    'label' => __('admin.profit'),
                    'data' => $data->pluck('profit'),
                    'backgroundColor' => '#0C0CE8',
                    'borderColor' => '#0C0CE8',
                ],
            ],
            'labels' => $data->pluck('date'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): ?string
    {
        return __('admin.static_info');
    }

    protected ?string $maxHeight = '400px';

    protected int|string|array $columnSpan = 2;
}
