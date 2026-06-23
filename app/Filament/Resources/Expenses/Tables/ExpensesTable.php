<?php

namespace App\Filament\Resources\Expenses\Tables;

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
use Illuminate\Support\Carbon;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['type', 'user']))
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
                        $from = filled($data['from'] ?? null) ? Carbon::parse($data['from'])->startOfDay() : null;
                        $until = filled($data['until'] ?? null) ? Carbon::parse($data['until'])->endOfDay() : null;

                        return $query
                            ->when($from, fn (Builder $query, Carbon $date) => $query->where('spent_at', '>=', $date))
                            ->when($until, fn (Builder $query, Carbon $date) => $query->where('spent_at', '<=', $date));
                    }),

                SelectFilter::make('expense_type_id')
                    ->relationship('type', 'name')
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
