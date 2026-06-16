<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class SalesSourcesChartWidget extends ChartWidget
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
        $startDate = isset($this->filters['start_date'])
            ? Carbon::parse($this->filters['start_date'])->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $endDate = isset($this->filters['end_date'])
            ? Carbon::parse($this->filters['end_date'])->endOfDay()
            : now()->endOfMonth()->endOfDay();

        $data = Product::query()
            ->sold()
            ->whereBetween('products.created_at', [$startDate, $endDate])
            ->leftJoin('hear_abouts', 'products.hear_about_id', '=', 'hear_abouts.id')
            ->selectRaw('
            products.hear_about_id,
            hear_abouts.name,
            hear_abouts.color,
            COUNT(*) as total
        ')
            ->groupBy('products.hear_about_id', 'hear_abouts.name', 'hear_abouts.color')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('admin.sales_sources'),
                    'data' => $data->pluck('total')->values(),
                    'backgroundColor' => $data->map(fn ($item) => $item->color ?? '#9CA3AF')->values(),
                    'borderColor' => $data->map(fn ($item) => $item->color ?? '#9CA3AF')->values(),
                ],
            ],
            'labels' => $data->map(function ($item) {
                $name = $item->name ?? __('admin.unknown');
                return "{$name} ({$item->total})";
            })->values(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): ?string
    {
        return __('admin.sales_sources_analytic');
    }
}