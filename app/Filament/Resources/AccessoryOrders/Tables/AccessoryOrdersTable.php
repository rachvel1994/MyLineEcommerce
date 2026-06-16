<?php

namespace App\Filament\Resources\AccessoryOrders\Tables;

use App\Models\AccessoryOrders;
use App\Models\Payment;
use App\Models\User;
use App\Services\AccessoryOrderCashDrawerService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AccessoryOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereHas('items');
            })
            ->columns([
                TextColumn::make('order_id')
                    ->label(__('admin.order_id'))
                    ->copyable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('seller.name')
                    ->label(__('admin.seller'))
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('product.model.name')
                    ->label(__('admin.product'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('buyer.name')
                    ->label(__('admin.buyer'))
                    ->toggleable(isToggledHiddenByDefault: false),
                
                TextColumn::make('items_count')
                    ->label(__('admin.quantity'))
                    ->counts('items')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('payments_sum_amount')
                    ->sum('payments', 'amount')
                    ->getStateUsing(
                        fn(AccessoryOrders $record) => $record->payments->sum('amount') - optional($record->product)->sale_price ?? 0
                    )
                    ->money('GEL')
                    ->label(__('admin.price'))
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('payment_methods')
                    ->label(__('admin.payment'))
                    ->getStateUsing(
                        fn(AccessoryOrders $record) => $record->payments->pluck('payment.name')->values()->all()
                    )
                    ->listWithLineBreaks()
                    ->limitList(5)
                    ->tooltip(fn($state) => is_array($state) ? implode(', ', $state) : null)
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('accessories_list')
                    ->label(__('admin.accessory'))
                    ->getStateUsing(
                        fn(AccessoryOrders $record) => $record->items->pluck('accessory.name')->filter()->unique()->values()->all()
                    )
                    ->listWithLineBreaks()
                    ->limitList(6)
                    ->tooltip(fn($state) => is_array($state) ? implode(', ', $state) : null)
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

                SelectFilter::make('seller_id')
                    ->label(__('admin.seller'))
                    ->options(User::query()->whereNot('id', 1)->whereHas('roles', function ($query) {
                        return $query->whereIn('id', [1, 5, 6, 7]);
                    })->pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('buyer_id')
                    ->label(__('admin.buyer'))
                    ->preload()
                    ->searchable()
                    ->relationship('buyer', 'name'),

                Filter::make('payment_id')
                    ->label(__('admin.payment'))
                    ->schema([
                        Select::make('payment_id')
                            ->label(__('admin.payment'))
                            ->options(Payment::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['payment_id'] ?? null,
                            fn (Builder $query, $paymentId) => $query->whereHas(
                                'payments',
                                fn (Builder $query) => $query->where('payment_id', $paymentId)
                            )
                        );
                    }),

                Filter::make('created_at')
                    ->label(__('admin.created_at'))
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('admin.from_date')),

                        DatePicker::make('until')
                            ->label(__('admin.to_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date)
                            );
                    }),

                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')->label(__('admin.from_date')),
                        DatePicker::make('until')->label(__('admin.to_date')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (AccessoryOrders $record): void {
                        app(AccessoryOrderCashDrawerService::class)
                            ->removeOrderPayments((int) $record->id);
                    }),
            ])
            ->persistFiltersInSession()
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->deferFilters(false)
            ->defaultPaginationPageOption(50)
            ->defaultSort('id', 'desc');
    }
}
