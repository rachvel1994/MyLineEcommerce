<?php

namespace App\Filament\Resources\Services\RelationManagers;

use App\Filament\Traits\HasServiceActions;
use App\Models\Product;
use App\Models\ServiceProduct;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductsRelationManager extends RelationManager
{
    use HasServiceActions;

    protected static string $relationship = 'products';

    protected static string $model = ServiceProduct::class;
    protected static ?string $recordTitleAttribute = 'sku';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['model', 'battery', 'color', 'condition', 'firstComment'])->whereIn('status_id', [3, 12]))
            ->defaultSort('pivot_created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('model.name')
                    ->label(__('admin.model'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label(__('admin.sku'))
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('battery.name')
                    ->label(__('admin.battery'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('color.name')
                    ->label(__('admin.color'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('condition.name')
                    ->label(__('admin.condition'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('service_comment')
                    ->label(__('admin.service_comment'))
                    ->getStateUsing(fn ($record) =>
                        $record->service_comment
                        ?? strip_tags($record->firstComment?->body)
                    )
                    ->wrap()
                    ->searchable(),
            ])
            ->headerActions([
            ])
            ->recordActions(static::serviceActions());
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.product');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __('admin.product');
    }
}
