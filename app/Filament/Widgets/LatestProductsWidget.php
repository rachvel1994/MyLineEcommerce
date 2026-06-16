<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestProductsWidget extends TableWidget
{
    use HasWidgetShield;

    protected ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('status_id', 4)
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('status.name')
                    ->label(__('admin.status'))
                    ->searchable()
                    ->extraAttributes(function (Product $record) {
                        $color = $record->status?->color ?: '#6b7280';
                        return ['style' => "background-color: {$color}; font-weight: bolder"];
                    })
                    ->toggleable(),

                TextColumn::make('model.name')
                    ->label(__('admin.model'))
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn() => ['style' => "font-weight: bolder"]),

                TextColumn::make('order_id')
                    ->label(__('admin.order_id'))
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn() => ['style' => "font-weight: bolder"]),

                TextColumn::make('sale_price')
                    ->label(__('admin.sale_price'))
                    ->money('GEL')
                    ->toggleable()
                    ->extraAttributes(fn() => ['style' => "font-weight: bolder"]),

                TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->date()
                    ->extraAttributes(fn() => ['style' => "font-weight: bolder"])
                    ->toggleable(),

                TextColumn::make('color.name')
                    ->label(__('admin.color'))
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn() => ['style' => "font-weight: bolder"]),

                TextColumn::make('storage.name')
                    ->label(__('admin.storage'))
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn() => ['style' => "font-weight: bolder"]),

                TextColumn::make('battery.name')
                    ->label(__('admin.battery'))
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn() => ['style' => "font-weight: bolder"]),

                TextColumn::make('condition.name')
                    ->label(__('admin.condition'))
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn() => ['style' => "font-weight: bolder"]),

                TextColumn::make('comment')
                    ->label(__('admin.comment'))
                    ->limit()
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn() => ['style' => "font-weight: bolder"]),

                TextColumn::make('sku')
                    ->label(__('admin.sku'))
                    ->searchable()
                    ->copyable()
                    ->toggleable()
                    ->extraAttributes(fn() => ['style' => "color: red; font-weight: bolder"]),

                TextColumn::make('user.mobile')
                    ->label(__('admin.mobile'))
                    ->searchable()
                    ->copyable()
                    ->toggleable()
                    ->extraAttributes(fn() => ['style' => "font-weight: bolder"]),

                TextColumn::make('user.name')
                    ->label(__('admin.user'))
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn() => ['style' => "font-weight: bolder"]),
            ])
            ->deferFilters(false)
            ->paginated(false)
            ->toolbarActions([])
            ->emptyStateHeading(__('admin.product'))
            ->heading(__('admin.product'))
            ->headerActions([]);
    }

    public function getTableHeading(): ?string
    {
        return __('admin.latest_sold_products');
    }
}
