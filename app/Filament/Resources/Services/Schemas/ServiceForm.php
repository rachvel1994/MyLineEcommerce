<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Forms\Components\PriceInput;
use App\Models\Service;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    Select::make('technic_id')
                        ->label(__('admin.technic'))
                        ->preload()
                        ->options(User::query()
                            ->whereHas('roles', fn($q) => $q->where('id', 4))
                            ->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->native(false)
                        ->required()
                        ->visible(fn() => canAbility('ViewCustomer:Service'))
                        ->disabled(fn() => !canAbility('ViewCustomer:Service')),

                    Select::make('created_by')
                        ->label(__('admin.added_by'))
                        ->relationship('creator', 'name')
                        ->default(fn() => auth()->id())
                        ->disabled()
                        ->native(false)
                        ->visible(fn() => canAbility('ViewCreator:Service'))
                        ->dehydrated(),

                    DatePicker::make('created_at')
                        ->disabled()
                        ->label(__('admin.created_at')),
                ])->columnSpanFull(),
                Grid::make(12)->schema([
                    PriceInput::make('advance_payment')
                        ->label(__('admin.payed_amount'))
                        ->numeric()
                        ->disabled()
                        ->minValue(0)
                        ->default(0)
                        ->reactive()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $subtotal = (float)($get('subtotal') ?? 0);
                            $debt = max(0, round($subtotal - (float)$state, 2));
                            $set('debt', $debt);
                            $set('is_paid', $debt <= 0);
                        })
                        ->debounce(300)
                        ->visible(fn() => canAbility('ViewPaidAmount:Service'))
                        ->columnSpan(4),

                    PriceInput::make('debt')
                        ->label(__('admin.debt'))
                        ->numeric()
                        ->readOnly()
                        ->reactive()
                        ->visible(fn() => canAbility('ViewDebt:Service'))
                        ->columnSpan(3),

                    PriceInput::make('subtotal')
                        ->label(__('admin.total'))
                        ->numeric()
                        ->reactive()
                        ->readOnly()
                        ->visible(fn() => canAbility('ViewSubtotal:Service'))
                        ->columnSpan(4),

                    Toggle::make('is_paid')
                        ->label(__('admin.is_paid'))
                        ->reactive()
                        ->disabled(fn() => !canAbility('PayAll:Service'))
                        ->afterStateUpdated(function (
                            bool $state,
                            Set $set,
                            Get $get,
                            ?Service $record,
                            $livewire
                        ) {

                            $subtotal = (float) ($get('subtotal') ?? 0);

                            if ($state) {
                                $set('advance_payment', $subtotal);
                                $set('debt', 0);
                            } else {
                                $paid = $record?->advance_payment ?? 0;
                                $set('advance_payment', $paid);
                                $set('debt', max(0, $subtotal - $paid));
                            }

                            if ($record) {
                                DB::transaction(function () use ($record, $state, $set) {

                                    if ($state) {
                                        $record->serviceRepairHistories()
                                            ->update(['is_paid' => true]);

                                        $total = $record->serviceRepairHistories()
                                            ->sum('repair_price');

                                        $record->update([
                                            'advance_payment' => $total
                                        ]);

                                        $set('advance_payment', $total);
                                        $set('debt', 0);

                                    } else {
                                        $record->serviceRepairHistories()
                                            ->update(['is_paid' => false]);

                                        $record->update([
                                            'advance_payment' => 0
                                        ]);

                                        $set('advance_payment', 0);

                                        $subtotal = $record->subtotal ?? 0;
                                        $set('debt', $subtotal);
                                    }

                                    $record->refresh();
                                    $record->recalculateTotals();
                                });

                                $livewire->dispatch('$refresh');
                                $livewire->dispatch('refreshService');
                            }
                        })
                ])->columnSpanFull(),
            ])
            ->columns(12);
    }
}
