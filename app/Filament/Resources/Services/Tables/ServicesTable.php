<?php

namespace App\Filament\Resources\Services\Tables;

use App\Filament\Traits\HasServiceActions;
use App\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServicesTable
{
    use HasServiceActions;

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->with(['creator', 'technic']);

                return canAbility('ShowAllProducts:User')
                    ? $query
                    : $query->where('technic_id', auth()->id());
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->date()
                    ->extraAttributes(function (Service $record) {
                        return $record->is_paid ? ['style' => 'background-color: green'] : ['style' => 'background-color: red'];
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('technic.name')
                    ->label(__('admin.technic'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('creator.name')
                    ->label(__('admin.added_by'))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('advance_payment')
                    ->label(__('admin.payed'))
                    ->money('GEL', true)
                    ->color('success')
                    ->sortable()
                    ->visible(fn () => canAbility('ViewPaidAmount:Service'))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('debt')
                    ->label(__('admin.debt'))
                    ->money('GEL', true)
                    ->sortable()
                    ->color('danger')
                    ->visible(fn () => canAbility('ViewDebt:Service'))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('subtotal')
                    ->label(__('admin.total'))
                    ->money('GEL', true)
                    ->color('primary')
                    ->sortable()
                    ->visible(fn () => canAbility('ViewSubtotal:Service'))
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('is_paid')
                    ->label(__('admin.is_payed'))
                    ->boolean()
                    ->visible(fn () => canAbility('ViewIsPaid:Service'))
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                TernaryFilter::make('is_paid')
                    ->label(__('admin.is_payed')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->persistFiltersInSession()
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
