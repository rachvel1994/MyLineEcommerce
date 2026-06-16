<?php

namespace App\Filament\Resources\Consignments\Tables;

use App\Forms\Components\PriceInput;
use App\Models\Consignment;
use App\Models\ConsignmentPriceChange;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ConsignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->date()
                    ->extraAttributes(function (Consignment $record) {
                        return $record->is_paid ? ['style' => "background-color: green"] : ['style' => "background-color: red"];
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('customer.name')
                    ->label(__('admin.client'))
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
                    ->visible(fn() => canAbility('ViewPaidAmount:Consignment'))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('debt')
                    ->label(__('admin.debt'))
                    ->money('GEL', true)
                    ->sortable()
                    ->color('danger')
                    ->visible(fn() => canAbility('ViewDebt:Consignment'))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('subtotal')
                    ->label(__('admin.total'))
                    ->money('GEL', true)
                    ->color('primary')
                    ->sortable()
                    ->visible(fn() => canAbility('ViewSubtotal:Consignment'))
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('is_paid')
                    ->label(__('admin.is_payed'))
                    ->boolean()
                    ->visible(fn() => canAbility('ViewIsPaid:Consignment'))
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                TernaryFilter::make('is_paid')
                    ->label(__('admin.is_payed')),
            ])
            ->recordActions([
                static::Pay(),
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

    private static function Pay(): Action
    {
        return Action::make('pay')
            ->label(__('admin.pay'))
            ->icon(Heroicon::CurrencyDollar)
            ->color('info')
            ->modalHeading(fn (Consignment $record) => __('admin.consignment') . ' #' . $record->id)
            ->modalDescription(
                fn (Consignment $record) => __('admin.current_debt') . ': ' . money($record->debt)
            )
            ->schema([
                PriceInput::make('amount')
                    ->label(__('admin.amount'))
                    ->numeric()
                    ->minValue(0.01)
                    ->required()
                    ->maxValue(fn (Consignment $record) => $record->debt)
                    ->helperText(fn (Consignment $record) => __('admin.max') . ': ' . money($record->debt)),

                Select::make('payment_id')
                    ->label(__('admin.payment'))
                    ->options(toArray(Payment::class))
                    ->searchable()
                    ->required(),
            ])
            ->action(function (Consignment $record, array $data) {
                $amount = (float) $data['amount'];

                if ($amount > $record->debt) {
                    $amount = $record->debt;
                }

                ConsignmentPriceChange::query()->create([
                    'consignment_id' => $record->id,
                    'paid_amount' => $amount,
                    'payment_id' => $data['payment_id'],
                    'debt' => max(0, $record->debt - $amount),
                    'total' => $record->subtotal,
                ]);

                $record->advance_payment += $amount;

                $record->debt = max(
                    0,
                    round($record->subtotal - $record->advance_payment, 2)
                );

                $record->is_paid = $record->advance_payment >= $record->subtotal;

                if ($record->is_paid) {
                    $record->status_id = 4;
                }

                $record->save();
            })
            ->visible(fn () => canAbility('Update:Consignment'));
    }
}
