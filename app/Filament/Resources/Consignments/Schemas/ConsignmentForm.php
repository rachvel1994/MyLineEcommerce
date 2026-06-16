<?php

namespace App\Filament\Resources\Consignments\Schemas;

use App\Forms\Components\PriceInput;
use App\Models\Consignment;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ConsignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    Select::make('customer_id')
                        ->label(__('admin.client'))
                        ->preload()
                        ->options(User::query()
                            ->whereHas('roles', fn($q) => $q->where('id', 2))
                            ->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->native(false)
                        ->required()
                        ->visible(fn() => canAbility('ViewCustomer:Consignment')),

                    Select::make('created_by')
                        ->label(__('admin.added_by'))
                        ->relationship('creator', 'name')
                        ->default(fn() => auth()->id())
                        ->disabled()
                        ->native(false)
                        ->visible(fn() => canAbility('ViewCreator:Consignment'))
                        ->dehydrated(),

                    DatePicker::make('created_at')
                        ->disabled()
                        ->label(__('admin.created_at')),
                ])->columnSpanFull(),
                Grid::make(12)->schema([
                    PriceInput::make('advance_payment')
                        ->label(__('admin.payed_amount'))
                        ->numeric()
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
                        ->visible(fn() => canAbility('ViewPaidAmount:Consignment'))
                        ->columnSpan(4),

                    PriceInput::make('debt')
                        ->label(__('admin.debt'))
                        ->numeric()
                        ->readOnly()
                        ->visible(fn() => canAbility('ViewDebt:Consignment'))
                        ->columnSpan(3),

                    PriceInput::make('subtotal')
                        ->label(__('admin.total'))
                        ->numeric()
                        ->readOnly()
                        ->visible(fn() => canAbility('ViewSubtotal:Consignment'))
                        ->columnSpan(4),

                    Toggle::make('is_paid')
                        ->label(__('admin.is_paid'))
                        ->inline(false)
                        ->afterStateUpdated(function (
                            bool         $state,
                            Set          $set,
                            Get          $get,
                            ?Consignment $record
                        ) {
                            if ($state) {
                                $set('advance_payment', $get('subtotal'));
                                $set('debt', 0);
                            } else {
                                $paid = $record?->advance_payment ?? 0;
                                $subtotal = $get('subtotal') ?? 0;

                                $set('advance_payment', $paid);
                                $set('debt', max(0, $subtotal - $paid));
                            }
                        })
                        ->reactive()
                        ->visible(fn() => canAbility('ViewIsPaid:Consignment'))
                        ->columnSpan(1),
                ])->columnSpanFull(),
            ])
            ->columns(12);
    }
}
