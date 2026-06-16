<?php

namespace App\Filament\Resources\Statuses\Tables;

use App\Models\Status;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StatusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(__('admin.image'))
                    ->circular()
                    ->getStateUsing(fn(Status $record): ?string => $record->image ?? null),
                TextColumn::make('name')
                    ->label(__('admin.name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                ColorColumn::make('color')
                    ->label(__('admin.color'))
                    ->toggleable(isToggledHiddenByDefault: false),
                ToggleColumn::make('show_in_product')
                    ->label(__('admin.show_in_product'))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextInputColumn::make('sort_order')
                    ->label('admin.sort_order')
                    ->type('numeric'),
                ToggleColumn::make('is_active')
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
                    ])
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
            ->defaultSort('sort_order');
    }
}
