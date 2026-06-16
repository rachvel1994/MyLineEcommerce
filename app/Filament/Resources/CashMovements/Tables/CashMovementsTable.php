<?php

namespace App\Filament\Resources\CashMovements\Tables;

use App\Models\CashMovement;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CashMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                CashMovement::query()
                    ->orderByDesc('cash_drawer_id')
                    ->orderByDesc('moved_at')
            )
            ->columns([
                TextColumn::make('moved_at')
                    ->dateTime()
                    ->label(__('admin.date'))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('direction')
                    ->label(__('admin.direction'))
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'in' => __('admin.in'),
                        'out' => __('admin.out'),
                        'adjust' => __('admin.adjust'),
                        default => $state,
                    })
                    ->color(fn(string $state) => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('amount')
                    ->label(__('admin.amount'))
                    ->getStateUsing(function (CashMovement $record): float {
                        return match ($record->direction) {
                            'in' => abs((float) $record->amount),
                            'out' => -abs((float) $record->amount),
                            'adjust' => (float) $record->amount,
                            default => (float) $record->amount,
                        };
                    })
                    ->money('GEL')
                    ->summarize([
                        Summarizer::make('net_total')
                            ->label(__('admin.amount'))
                            ->money('GEL')
                            ->using(function (QueryBuilder $query): float {
                                return (float) (clone $query)
                                    ->cloneWithout(['columns', 'orders', 'limit', 'offset'])
                                    ->cloneWithoutBindings(['select', 'order'])
                                    ->selectRaw("
                        COALESCE(SUM(
                            CASE
                                WHEN cash_movements.direction = 'in'
                                    THEN ABS(cash_movements.amount)

                                WHEN cash_movements.direction = 'out'
                                    THEN -ABS(cash_movements.amount)

                                WHEN cash_movements.direction = 'adjust'
                                    THEN cash_movements.amount

                                ELSE cash_movements.amount
                            END
                        ), 0) as total
                    ")
                                    ->value('total');
                            }),

                        Summarizer::make('current_balance')
                            ->label(__('admin.current_balance'))
                            ->money('GEL')
                            ->using(function (QueryBuilder $query): float {
                                $drawerId = (clone $query)
                                    ->cloneWithout(['columns', 'orders', 'limit', 'offset'])
                                    ->cloneWithoutBindings(['select', 'order'])
                                    ->whereNotNull('cash_movements.cash_drawer_id')
                                    ->orderByDesc('cash_movements.moved_at')
                                    ->orderByDesc('cash_movements.id')
                                    ->value('cash_movements.cash_drawer_id');

                                if (! $drawerId) {
                                    return 0;
                                }

                                return (float) DB::table('cash_drawers')
                                    ->where('id', $drawerId)
                                    ->value('current_balance');
                            }),
                    ])
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('reason')
                    ->label(__('admin.reason'))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('user.name')
                    ->label(__('admin.user'))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('drawer.id')
                    ->label(__('admin.box_office') . ' #')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('direction')->options([
                    'in' => __('admin.in'),
                    'out' => __('admin.out'),
                ])->label(__('admin.direction')),
                Filter::make('moved_at')
                    ->schema([
                        DatePicker::make('from')->label(__('admin.from_date')),
                        DatePicker::make('until')->label(__('admin.to_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from = $data['from'] ?? null;
                        $until = $data['until'] ?? null;

                        $from = $from ? Carbon::parse($from)->format('Y-m-d') : null;
                        $until = $until ? Carbon::parse($until)->format('Y-m-d') : null;

                        return $query
                            ->when($from, fn(Builder $q) => $q->whereDate('moved_at', '>=', $from))
                            ->when($until, fn(Builder $q) => $q->whereDate('moved_at', '<=', $until));
                    })
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Group::make('created_at')
                    ->date()
                    ->label(false)
                    ->collapsible(),
            ])
            ->defaultGroup('created_at')
            ->groupingSettingsHidden()
            ->recordActions([])
            ->toolbarActions([])
            ->persistFiltersInSession()
            ->defaultPaginationPageOption(50);
    }
}
