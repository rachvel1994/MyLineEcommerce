<?php

namespace App\Filament\Resources\Accessories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class AccessoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('price')
                    ->label(__('admin.self_price'))
                    ->money('GEL')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('sale_price')
                    ->label(__('admin.sale_price'))
                    ->money('GEL')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('retail_price')
                    ->label(__('admin.retail_price'))
                    ->money('GEL')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('quantity')
                    ->label(__('admin.quantity'))
                    ->toggleable(isToggledHiddenByDefault: false),
                ToggleColumn::make('is_active')
                    ->visible(canAbility('Edit:Accessory'))
                    ->disabled(!canAbility('Edit:Accessory'))
                    ->label(__('admin.is_active'))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label(__('admin.updated_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->persistFiltersInSession()
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->visible(canAbility('Delete:Accessory')),
            ])
            ->defaultPaginationPageOption(50)
            ->defaultSort('id', 'desc');
    }
}
