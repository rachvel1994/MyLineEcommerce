<?php

namespace App\Filament\Widgets;

use App\Models\DeadStockByModel;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DeadStockWidget extends TableWidget
{
    use HasWidgetShield;

    protected int|string|array $columnSpan = 2;

    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DeadStockByModel::query()->with('model')
            )
            ->columns([
                TextColumn::make('model.name')
                    ->label(__('admin.model'))
                    ->placeholder('—')
                    ->weight('bold'),

                TextColumn::make('real_stock')
                    ->label(__('admin.quantity'))
                    ->color('success')
                    ->weight('bold'),

                TextColumn::make('dead_stock')
                    ->label(__('admin.dead_stock'))
                    ->color('danger')
                    ->weight('bold'),
            ])

            ->defaultSort('dead_stock', 'desc')

            ->paginated()
            ->defaultPaginationPageOption(10)
            ->heading(__('admin.dead_stock_analytic'));
    }
}
