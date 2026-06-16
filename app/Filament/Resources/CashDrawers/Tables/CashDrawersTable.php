<?php

namespace App\Filament\Resources\CashDrawers\Tables;

use App\Filament\Traits\HasCashDrawerActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CashDrawersTable
{
    use HasCashDrawerActions;

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('opening_balance')
                    ->label(__('admin.opening_balance'))
                    ->money('GEL')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('current_balance')
                    ->label(__('admin.current_balance'))
                    ->money('GEL')
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('opened_at')
                    ->label(__('admin.opened_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('openedBy.name')
                    ->label(__('admin.opened_by'))
                    ->formatStateUsing(fn($state) => $state ?: '—')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('closed_at')
                    ->label(__('admin.closed_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('closedBy.name')
                    ->label(__('admin.closed_by'))
                    ->formatStateUsing(fn($state) => $state ?: '—')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ...static::cashDrawerActions(),
                EditAction::make(),
            ])
            ->persistFiltersInSession()
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
