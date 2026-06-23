<?php

namespace App\Filament\Resources\ProductModels\Tables;

use App\Models\ProductModel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                SelectColumn::make('parent_id')
                    ->searchableOptions()
                    ->label(__('admin.parent'))
                    ->options(fn (): array => toArray(ProductModel::class)),
                ToggleColumn::make('is_active')
                    ->default(true)
                    ->label(__('admin.is_active'))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.updated_at'))
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('admin.is_active'))
                    ->searchable()
                    ->options([
                        1 => __('admin.active'),
                        0 => __('admin.inactive'),
                    ]),
                SelectFilter::make('parent_id')
                    ->label(__('admin.parent'))
                    ->searchable()
                    ->relationship('parent', 'name', fn ($query) => $query->whereNull('parent_id'))
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->persistFiltersInSession()
            ->defaultPaginationPageOption(50)
            ->defaultSort('id', 'desc');
    }
}
