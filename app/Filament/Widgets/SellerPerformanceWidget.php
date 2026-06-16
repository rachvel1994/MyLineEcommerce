<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Illuminate\Contracts\Support\Htmlable;

class SellerPerformanceWidget extends ChartWidget
{
    use HasFiltersSchema;

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
                ->default(now()->endOfDay()->endOfDay()),
        ]);
    }

    protected function getData(): array
    {
        $start = isset($this->filters['start_date'])
            ? Carbon::parse($this->filters['start_date'])->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $end = isset($this->filters['end_date'])
            ? Carbon::parse($this->filters['end_date'])->endOfDay()
            : now()->endOfMonth()->endOfDay();

        $data = Product::query()
            ->sold()
            ->whereNotNull('seller_id')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('seller_id, COUNT(*) as total')
            ->groupBy('seller_id')
            ->orderByDesc('total')
            ->with('seller')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('admin.sales_by_seller'),
                    'data' => $data->pluck('total'),
                ],
            ],
            'labels' => $data->map(fn($item) => $item->seller->name ?? __('admin.unknown')),
        ];
    }

    public function getHeading(): string|Htmlable|null
    {
        return  __('admin.sales_by_seller_analytic');
    }

    protected function getType(): string
    {
        return 'bar';
    }
}