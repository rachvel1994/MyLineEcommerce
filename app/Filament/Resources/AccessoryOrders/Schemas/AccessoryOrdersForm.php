<?php

namespace App\Filament\Resources\AccessoryOrders\Schemas;

use App\Forms\Components\PriceInput;
use App\Models\Accessory;
use App\Models\AccessoryOrders;
use App\Models\Payment;
use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AccessoryOrdersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(4)->schema([
                    TextInput::make('order_id')
                        ->label(__('admin.order_id'))
                        ->unique(ignoreRecord: true)
                        ->required()
                        ->default(generateOrderId(AccessoryOrders::class))
                        ->readOnly()
                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            foreach (array_keys($get('items') ?? []) as $i) {
                                $set("items.$i.order_id", $state);
                            }
                            foreach (array_keys($get('payments') ?? []) as $i) {
                                $set("payments.$i.order_id", $state);
                            }
                        }),

                    Select::make('seller_id')
                        ->searchable()
                        ->label(__('admin.seller'))
                        ->options(fn () => User::query()->whereNot('id', 1)->whereHas('roles', function ($query) {
                            return $query->whereIn('id', [1, 5, 6, 7]);
                        })->pluck('name', 'id')->toArray())
                        ->default(4),

                    Select::make('buyer_id')
                        ->label(__('admin.buyer'))
                        ->searchable()
                        ->preload()

                        ->options(function () {
                            return User::query()
                                ->whereHas('roles', fn ($q) => $q->whereIn('id', [2, 3]))
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($user) => [
                                    $user->id => "{$user->name}",
                                ]);
                        })

                        ->getSearchResultsUsing(function (string $search) {
                            return User::query()
                                ->whereHas('roles', fn ($q) => $q->whereIn('id', [2, 3]))
                                ->where(function ($q) use ($search) {
                                    $q->where('name', 'like', "%{$search}%")
                                        ->orWhere('mobile', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                                })
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($user) => [
                                    $user->id => "{$user->name} ({$user->mobile})",
                                ]);
                        })

                        ->getOptionLabelUsing(function ($value): ?string {
                            $user = User::find($value);

                            return $user
                                ? "{$user->name} ({$user->mobile})"
                                : null;
                        }),

                    Select::make('product_id')
                        ->searchable()
                        ->disabled()
                        ->label(__('admin.product'))
                        ->relationship('product', 'model.name'),

                ])->columnSpanFull(),

                Section::make(__('admin.order'))
                    ->schema([
                        Section::make(__('admin.accessory'))
                            ->schema([
                                Repeater::make('items')
                                    ->label(__('admin.accessory'))
                                    ->relationship('items')
                                    ->schema([
                                        Hidden::make('accessory_order_id')
                                            ->default(fn (Get $get) => $get('../../order_id'))
                                            ->dehydrated(true)
                                            ->lazy()
                                            ->required(),

                                        Select::make('accessory_id')
                                            ->label(__('admin.accessory'))
                                            ->searchable()
                                            ->native(false)
                                            ->options(fn (): array => toArray(Accessory::class))
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if ($state) {
                                                    if ($acc = Accessory::query()->find($state)) {
                                                        $set('price', $acc->sale_price);
                                                        $set('total_price', $acc->sale_price);
                                                    }
                                                } else {
                                                    $set('price', 0);
                                                    $set('total_price', 0);
                                                }
                                                static::scalePayments($set, $get, '../../');
                                            }),

                                        PriceInput::make('price')
                                            ->label(__('admin.price'))
                                            ->lazy()
                                            ->readOnly(fn (Get $get) => (bool) $get('is_gift'))
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $qty = (float) ($get('quantity') ?? 1);
                                                $set('total_price', (float) $state * $qty);
                                                static::scalePayments($set, $get, '../../');
                                            }),

                                        TextInput::make('quantity')
                                            ->label(__('admin.quantity'))
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->lazy()
                                            ->minValue(1)
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $price = (float) ($get('price') ?? 0);
                                                $set('total_price', $price * (float) $state);
                                                static::scalePayments($set, $get, '../../');
                                            }),

                                        PriceInput::make('total_price')
                                            ->label(__('admin.total_price'))
                                            ->readOnly(),

                                        Toggle::make('is_gift')
                                            ->default(false)
                                            ->label(__('admin.is_gift'))
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {

                                                if ($state) {

                                                    $set('price', 0);
                                                    $set('total_price', 0);

                                                } else {

                                                    $accessoryId = $get('accessory_id');

                                                    if ($accessoryId) {

                                                        $accessory = Accessory::query()->find($accessoryId);

                                                        $price = (float) ($accessory?->sale_price ?? 0);

                                                        $quantity = (float) ($get('quantity') ?? 1);

                                                        $set('price', $price);
                                                        $set('total_price', $price * $quantity);
                                                    }
                                                }

                                                static::scalePayments($set, $get, '../../');
                                            }),
                                    ])
                                    ->defaultItems(1)
                                    ->columns(4)
                                    ->lazy()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        static::scalePayments($set, $get, '../');
                                        $parentOrderId = $get('../order_id') ?? $get('order_id');
                                        foreach (array_keys($state ?? []) as $i) {
                                            $set("items.$i.order_id", $parentOrderId);
                                        }
                                    })
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),

                Section::make(__('admin.payment'))
                    ->schema([
                        Repeater::make('payments')
                            ->label(__('admin.payment'))
                            ->relationship('payments')
                            ->schema([
                                Hidden::make('order_id')
                                    ->default(fn (Get $get) => $get('../../order_id'))
                                    ->dehydrated(true)
                                    ->lazy()
                                    ->required(),

                                Select::make('payment_id')
                                    ->native(false)
                                    ->lazy()
                                    ->label(__('admin.payment'))
                                    ->options(fn (): array => toArray(Payment::class))
                                    ->searchable()
                                    ->required(),

                                PriceInput::make('amount')
                                    ->label(__('admin.price')),
                            ])
                            ->defaultItems(1)
                            ->columns()
                            ->lazy()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                static::scalePayments($set, $get);

                                $parentOrderId = $get('order_id');
                                foreach (array_keys($state ?? []) as $i) {
                                    $set("payments.$i.order_id", $parentOrderId);
                                }
                            })
                            ->required()
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ]);
    }

    private static function scalePayments(Set $set, Get $get, string $prefix = ''): void
    {
        $items = $get($prefix.'items') ?? [];
        $payments = $get($prefix.'payments') ?? [];

        $count = count($payments);
        if ($count === 0) {
            return;
        }

        $grandTotal = round(
            collect($items)->sum(fn ($row) => (float) ($row['total_price'] ?? 0)),
            2
        );

        $firstKey = array_key_first($payments);

        $otherSum = 0.0;
        $hasCustomOthers = false;

        foreach ($payments as $key => $p) {
            if ($key === $firstKey) {
                continue;
            }

            $amount = (float) ($p['amount'] ?? 0);

            if ($amount > 0) {
                $hasCustomOthers = true;
            }

            $otherSum += $amount;
        }

        $otherSum = round($otherSum, 2);

        if ($hasCustomOthers) {
            $remainder = round($grandTotal - $otherSum, 2);
            if ($remainder < 0) {
                $remainder = 0.0;
            }

            foreach ($payments as $key => &$p) {
                if ($key === $firstKey) {
                    $p['amount'] = $remainder;
                } else {
                    $p['amount'] = round((float) ($p['amount'] ?? 0), 2);
                }
            }
            unset($p);

            $set($prefix.'payments', $payments);

            return;
        }

        foreach ($payments as $key => &$p) {
            $p['amount'] = ($key === $firstKey) ? $grandTotal : 0.0;
        }
        unset($p);

        $set($prefix.'payments', $payments);
    }
}
