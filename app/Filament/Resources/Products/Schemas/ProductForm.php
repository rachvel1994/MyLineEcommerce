<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Traits\HasProductActions;
use App\Forms\Components\NumericInput;
use App\Forms\Components\PriceInput;
use App\Models\Accessory;
use App\Models\Battery;
use App\Models\Category;
use App\Models\Color;
use App\Models\Condition;
use App\Models\Delivery;
use App\Models\Guarantee;
use App\Models\HearAbout;
use App\Models\Payment;
use App\Models\ProductModel;
use App\Models\Status;
use App\Models\Storage;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ProductForm
{
    use HasProductActions;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('ProductFormTabs')
                    ->tabs([
                        Tabs\Tab::make(__('admin.main'))
                            ->schema([
                                Grid::make(4)->schema([
                                    TextInput::make('sku')
                                        ->label(__('admin.sku'))
                                        ->unique(ignoreRecord: true)
                                        ->required()
                                        ->maxLength(255)
                                        ->visible(fn() => canAbility('CanViewSku:Product')),

                                    Select::make('model_id')
                                        ->label(__('admin.model'))
                                        ->options(toArray(ProductModel::class))
                                        ->native(false)
                                        ->searchable()
                                        ->required(),

                                    Select::make('category_id')
                                        ->label(__('admin.category'))
                                        ->options(toArray(Category::class))
                                        ->native(false)
                                        ->searchable()
                                        ->required(),

                                    Select::make('company_id')
                                        ->label(__('admin.company'))
                                        ->options(companies())
                                        ->native(false)
                                        ->searchable()
                                        ->required(),
                                ]),
                                Grid::make(3)
                                    ->schema([
                                        PriceInput::make('price')
                                            ->default(0)
                                            ->lazy()
                                            ->label(__('admin.self_price'))
                                            ->visible(fn() => canAbility('CanViewPrice:Product'))
                                            ->dehydrated(fn() => canAbility('CanViewPrice:Product')),

                                        PriceInput::make('retail_price')
                                            ->label(__('admin.retail_price'))
                                            ->lazy()
                                            ->visible(fn() => canAbility('CanViewRetailPrice:Product'))
                                            ->dehydrated(fn() => canAbility('CanViewRetailPrice:Product')),

                                        PriceInput::make('sale_price')
                                            ->label(__('admin.sale_price'))
                                            ->lazy()
                                            ->visible(fn() => canAbility('CanViewSalePrice:Product'))
                                            ->dehydrated(fn() => canAbility('CanViewSalePrice:Product')),

                                        Select::make('battery_id')
                                            ->label(__('admin.battery'))
                                            ->options(toArray(Battery::class))
                                            ->searchable()
                                            ->native(false)
                                            ->visible(fn() => canAbility('CanViewBattery:Product')),

                                        Select::make('color_id')
                                            ->label(__('admin.color'))
                                            ->options(toArray(Color::class))
                                            ->searchable()
                                            ->native(false)
                                            ->visible(fn() => canAbility('CanViewColor:Product')),

                                        Select::make('storage_id')
                                            ->label(__('admin.storage'))
                                            ->options(toArray(Storage::class))
                                            ->searchable()
                                            ->required()
                                            ->native(false)
                                            ->visible(fn() => canAbility('CanViewStorage:Product')),

                                        Grid::make(3)->schema([
                                            Toggle::make('is_repaired')
                                                ->label(__('admin.is_repaired'))
                                                ->required()
                                                ->visible(fn() => canAbility('CanViewIsRepaired:Product'))
                                                ->dehydrated(fn() => canAbility('CanViewIsRepaired:Product')),

                                            Toggle::make('show_repair_information')
                                                ->label(__('admin.show_repair_information'))
                                                ->required()
                                                ->visible(fn() => canAbility('CanViewShowRepairedInformation:Product'))
                                                ->dehydrated(fn() => canAbility('CanViewShowRepairedInformation:Product')),

                                            Toggle::make('need_reset')
                                                ->label(__('admin.need_reset'))
                                                ->required()
                                                ->visible(fn() => canAbility('CanViewNeedReset:Product'))
                                                ->dehydrated(fn() => canAbility('CanViewNeedReset:Product')),
                                        ])->columnSpanFull(),

                                        Select::make('repair_information_id')
                                            ->label(__('admin.repair_information'))
                                            ->multiple()
                                            ->searchable()
                                            ->native(false)
                                            ->relationship('information', 'name')
                                            ->preload()
                                            ->columnSpanFull()
                                            ->dehydrated(true)
                                            ->visible(fn() => canAbility('CanViewShowRepairedInformation:Product')),

                                        Grid::make()->schema([
                                            Select::make('status_id')
                                                ->label(__('admin.status'))
                                                ->options(Status::query()->orderBy('sort_order')->pluck('name', 'id'))
                                                ->native(false)
                                                ->searchable()
                                                ->required()
                                                ->visible(fn() => canAbility('CanViewStatus:Product')),

                                            Select::make('condition_id')
                                                ->label(__('admin.condition'))
                                                ->options(toArray(Condition::class))
                                                ->native(false)
                                                ->searchable()
                                                ->required()
                                                ->visible(fn() => canAbility('CanViewCondition:Product')),
                                        ])->columnSpanFull(),

                                        FileUpload::make('images')
                                            ->label(__('admin.images'))
                                            ->image()
                                            ->multiple()
                                            ->columnSpanFull()
                                            ->directory('products/images')
                                            ->panelLayout('grid')
                                            ->previewable(),

                                        Textarea::make('comment')
                                            ->columnSpanFull()
                                            ->label(__('admin.comment'))
                                            ->rows(5)
                                            ->visible(fn() => canAbility('CanViewComment:Product')),

                                        Textarea::make('service_comment')
                                            ->columnSpanFull()
                                            ->hiddenOn('edit')
                                            ->visibleOn('create')
                                            ->label(__('admin.service_comment'))
                                            ->rows(5)
                                            ->visible(fn() => canAbility('CanViewServiceComment:Product')),


                                    ]),
                            ])
                            ->columnSpanFull(),

                        Tabs\Tab::make(__('admin.guarantee'))
                            ->hidden(fn($record) => empty($record->status_id) || $record->status_id != 4)
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Select::make('guarantee_id')
                                            ->label(__('admin.guarantee'))
                                            ->options(toArray(Guarantee::class))
                                            ->native(false)
                                            ->searchable()
                                            ->visible(fn() => canAbility('CanViewGuarantee:Product')),

                                        TextInput::make('order_id')
                                            ->label(__('admin.order_id'))
                                            ->maxLength(255)
                                            ->visible(fn() => canAbility('CanViewOrderId:Product')),

                                        Grid::make(4)->schema([
                                            Select::make('user_id')
                                                ->label(__('admin.user'))
                                                ->options(toArray(User::class))
                                                ->native(false)
                                                ->searchable()
                                                ->default(fn() => auth()->id())
                                                ->visible(fn() => canAbility('CanViewUser:Product')),

                                            Select::make('hear_about_id')
                                                ->label(__('admin.hear_about'))
                                                ->options(toArray(HearAbout::class))
                                                ->native(false)
                                                ->searchable()
                                                ->visible(fn() => canAbility('CanViewHearAbout:Product')),

                                            Select::make('delivery_id')
                                                ->label(__('admin.delivery'))
                                                ->options(toArray(Delivery::class))
                                                ->native(false)
                                                ->searchable()
                                                ->visible(fn() => canAbility('CanViewDelivery:Product')),

                                            Select::make('seller_id')
                                                ->label(__('admin.seller'))
                                                ->options(User::query()->whereNot('id', 1)->whereHas('roles', function ($query) {
                                                    return $query->whereIn('id', [1, 5, 6, 7]);
                                                })->pluck('name', 'id'))
                                                ->native(false)
                                                ->searchable(),
                                        ])->columnSpanFull(),
                                    ]),

                                Repeater::make('accessoryOrders')
                                    ->label(__('admin.accessory'))
                                    ->relationship('accessoryOrders')
                                    ->disabled(fn () => ! auth()->check() || ! auth()->user()->roles()->where('id', 1)->exists())
                                    ->schema([
                                        Repeater::make('items')
                                            ->label(__('admin.accessory'))
                                            ->relationship('items')
                                            ->schema([
                                                Select::make('accessory_id')
                                                    ->label(__('admin.accessory'))
                                                    ->options(toArray(Accessory::class))
                                                    ->required()
                                                    ->reactive()
                                                    ->searchable(),

                                                PriceInput::make('price')
                                                    ->label(__('admin.price'))
                                                    ->numeric()
                                                    ->lazy(),

                                                NumericInput::make('quantity')
                                                    ->label(__('admin.quantity'))
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->lazy(),

                                                PriceInput::make('total_price')
                                                    ->label(__('admin.total_price'))
                                                    ->readOnly()
                                                    ->numeric()
                                                    ->dehydrated(false),

                                                Toggle::make('is_gift')
                                                    ->default(false)
                                                    ->label(__('admin.is_gift'))
                                                    ->live(),
                                            ])
                                            ->columns(4),
                                    ])
                                    ->defaultItems(0)
                                    ->columnSpanFull(),

                                Repeater::make('payments')
                                    ->label(__('admin.payment'))
                                    ->relationship('payments')
                                    ->disabled(fn () => ! auth()->check() || ! auth()->user()->roles()->where('id', 1)->exists())
                                    ->schema([
                                        Grid::make()->schema([
                                            Select::make('payment_id')
                                                ->reactive()
                                                ->label(__('admin.payment'))
                                                ->options(toArray(Payment::class))
                                                ->native(false)
                                                ->searchable()
                                                ->required(),

                                            PriceInput::make('price')
                                                ->label(__('admin.price')),
                                        ])->columnSpanFull(),
                                    ])
                                    ->defaultItems(0),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
