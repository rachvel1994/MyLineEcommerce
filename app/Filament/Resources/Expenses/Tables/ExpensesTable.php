<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Models\ExpenseType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('spent_at')
                    ->label(__('admin.date'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('type.name')
                    ->label(__('admin.type'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('description')
                    ->label(__('admin.description'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('amount')
                    ->label(__('admin.amount'))
                    ->numeric(decimalPlaces: 2)
                    ->money('GEL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('user.name')
                    ->label(__('admin.user'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('spent_at')
                    ->label(__('admin.date_range'))
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('admin.from_date')),
                        DatePicker::make('until')
                            ->label(__('admin.to_date')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $from) => $q->whereDate('spent_at', '>=', $from))
                            ->when($data['until'] ?? null, fn($q, $until) => $q->whereDate('spent_at', '<=', $until));
                    }),

                SelectFilter::make('expense_type_id')
                    ->options(toArray(ExpenseType::class))
                    ->label(__('admin.type'))
                    ->searchable()
                    ->preload(),
            ])
            ->deferFilters(false)
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
