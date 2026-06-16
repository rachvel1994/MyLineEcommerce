<?php

namespace App\Filament\Widgets;

use App\Models\LowStockByModel;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowStockByModelChart extends TableWidget
{
    use HasWidgetShield;

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(LowStockByModel::query())
            ->defaultSort('total', 'asc')
            ->columns([
                TextColumn::make('model.name')
                    ->label(__('admin.model'))
                    ->weight('bold'),

                TextColumn::make('total')
                    ->label(__('admin.quantity'))
                    ->color('danger')
                    ->weight('bold'),
            ])
            ->paginated()
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading(__('admin.low_stock_analytic'))
            ->heading(__('admin.low_stock_analytic'));
    }

    public function getTableHeading(): ?string
    {
        return __('admin.low_stock_analytic');
    }
}
