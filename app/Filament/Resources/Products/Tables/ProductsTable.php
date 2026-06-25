<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Traits\HasProductActions;
use App\Filament\Traits\HasProductBulkActions;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ProductsTable
{
    use HasProductActions, HasProductBulkActions;

    public static ?string $activeTab = null;

    public static ?string $tab = null;

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->with([
                    'battery',
                    'color',
                    'condition',
                    'hearAbout',
                    'information',
                    'model',
                    'payments.payment',
                    'services',
                    'status',
                    'storage',
                    'user',
                ]);

                return canAbility('ShowAllProducts:User')
                    ? $query
                    : $query->where('user_id', auth()->id());
            })
            ->columns([
                TextColumn::make('status.name')
                    ->label(__('admin.status'))
                    ->searchable()
                    ->extraAttributes(function (Product $record) {
                        $color = $record->status?->color ?: '#6b7280';

                        return ['style' => "background-color: {$color}; font-weight: bolder"];
                    })
                    ->toggleable()
                    ->visible(fn () => static::productAbility('CanViewStatus')),

                TextColumn::make('service_id')
                    ->label(__('admin.service'))
                    ->getStateUsing(function ($record) {
                        $service = $record->services->sortByDesc('created_at')->first();

                        return $service ? 'სერვისის ID #'.$service->id : null;
                    })
                    ->color(function ($record) {
                        return $record->services->isEmpty() ? 'danger' : 'success';
                    })
                    ->toggleable()
                    ->visible(fn ($livewire) => $livewire->activeTab === 'პასაჟი სერვისი'
                        && static::productAbility('CanViewOrderId')
                    )
                    ->extraAttributes([
                        'style' => 'font-weight: bolder',
                    ]),

                TextColumn::make('model.name')
                    ->label(__('admin.model'))
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->visible(fn () => static::productAbility('CanViewModel')),

                TextColumn::make('order_id')
                    ->label(__('admin.order_id'))
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->visible(fn ($livewire) => static::productAbility('CanViewOrderId') && $livewire->activeTab === 'გაყიდულია'),

                TextColumn::make('user.name')
                    ->label(__('admin.user'))
                    ->searchable()
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->toggleable()
                    ->visible(fn ($livewire) => static::productAbility('CanViewUser') && $livewire->activeTab === 'გაყიდულია'),

                TextColumn::make('created_at')
                    ->label(__('admin.date'))
                    ->date()
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->toggleable(),

                TextColumn::make('payments_list')
                    ->label(__('admin.payment'))
                    ->extraAttributes(fn () => [
                        'style' => 'font-weight: bolder',
                    ])
                    ->html()
                    ->default(function ($record) {

                        return $record->payments
                            ->map(function ($payment) {

                                $name = $payment->payment?->name ?? __('admin.unknown');
                                $amount = $payment->price;

                                return "{$name}: ({$amount})";
                            })
                            ->implode('<br>');
                    })
                    ->visible(fn ($livewire) => $livewire->activeTab == 'გაყიდულია')
                    ->color('success')
                    ->toggleable(),

                TextColumn::make('hearAbout.name')
                    ->label(__('admin.hear_about'))
                    ->searchable()
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->toggleable()
                    ->visible(fn ($livewire) => static::productAbility('CanViewHearAbout') && $livewire->activeTab === 'გაყიდულია'),

                TextColumn::make('color.name')
                    ->label(__('admin.color'))
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->visible(fn () => static::productAbility('CanViewColor')),

                TextColumn::make('storage.name')
                    ->label(__('admin.storage'))
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->visible(fn () => static::productAbility('CanViewStorage')),

                TextColumn::make('battery.name')
                    ->label(__('admin.battery'))
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->visible(fn () => static::productAbility('CanViewBattery')),

                TextColumn::make('price')
                    ->money('GEL')
                    ->label(__('admin.self_price'))
                    ->toggleable()
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->visible(fn () => canAbility('CanViewPrice:Product')),

                TextColumn::make('retail_price')
                    ->label(__('admin.retail_price'))
                    ->money('GEL')
                    ->toggleable()
                    ->hidden(fn ($livewire) => $livewire->activeTab === 'გაყიდულია'
                        && static::productAbility('CanViewRetailPrice')
                    )
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->visible(fn () => static::productAbility('CanViewRetailPrice')),

                TextColumn::make('sale_price')
                    ->label(__('admin.sale_price'))
                    ->money('GEL')
                    ->toggleable()
                    ->hidden(fn ($livewire) => $livewire->activeTab === 'გაყიდულია'
                        && static::productAbility('CanViewSalePrice')
                    )
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->visible(fn () => static::productAbility('CanViewSalePrice')),

                TextColumn::make('condition.name')
                    ->label(__('admin.condition'))
                    ->searchable()
                    ->toggleable()
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->visible(fn () => static::productAbility('CanViewCondition')),

                TextColumn::make('information_names')
                    ->label(__('admin.repair_information'))
                    ->state(fn (Product $record) => $record->information->pluck('name')->join(', '))
                    ->wrap()
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('information', fn ($q) => $q->where('name', 'like', "%{$search}%")
                        );
                    })
                    ->toggleable()
                    ->badge()
                    ->color('success')
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->visible(fn () => static::productAbility('CanViewShowRepairedInformation')),

                TextColumn::make('comment')
                    ->label(__('admin.comment'))
                    ->limit()
                    ->searchable()
                    ->toggleable()
                    ->badge()
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->visible(fn () => static::productAbility('CanViewComment')),

                TextColumn::make('sku')
                    ->label(__('admin.sku'))
                    ->searchable()
                    ->copyable()
                    ->toggleable()
                    ->extraAttributes(fn () => ['style' => 'color: red; font-weight: bolder'])
                    ->visible(fn () => static::productAbility('CanViewSku')),

                TextColumn::make('user.mobile')
                    ->label(__('admin.mobile'))
                    ->searchable()
                    ->copyable()
                    ->extraAttributes(fn () => ['style' => 'font-weight: bolder'])
                    ->toggleable()
                    ->visible(fn ($livewire) => static::productAbility('CanViewMobile') && $livewire->activeTab === 'გაყიდულია'),

                TextColumn::make('company_id')
                    ->label(__('admin.company'))
                    ->searchable()
                    ->formatStateUsing(fn ($record) => companies($record->company_id))
                    ->toggleable()
                    ->visible(fn () => static::productAbility('CanViewCompany')),
            ])
            ->filters([
                Filter::make('product')
                    ->label(__('admin.sku'))
                    ->schema([
                        TextInput::make('sku')
                            ->label(__('admin.sku')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['sku'] ?? null,
                            fn (Builder $query, $sku): Builder => $query->where('sku', 'like', "%{$sku}%")
                        );
                    }),

                Filter::make('product_name')
                    ->label(__('admin.name'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.name')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {

                        return $query->when(
                            $data['name'] ?? null,
                            function (Builder $query, $name) {

                                $query->where(function ($q) use ($name) {

                                    $q->whereHas('model', function ($mq) use ($name) {
                                        $mq->where('name', 'like', "%{$name}%");
                                    })
                                        ->orWhereHas('model.parent', function ($pq) use ($name) {
                                            $pq->where('name', 'like', "%{$name}%");
                                        });

                                });
                            }
                        );
                    }),
                Filter::make('product.order')
                    ->label(__('admin.order_id'))
                    ->schema([
                        TextInput::make('order_id')
                            ->label(__('admin.order_id')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['order_id'] ?? null,
                            fn (Builder $query, $orderId): Builder => $query->where('order_id', $orderId)
                        );
                    }),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->preload()
                    ->label(__('admin.user'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewUser')),

                SelectFilter::make('seller_id')
                    ->label(__('admin.seller'))
                    ->relationship(
                        name: 'seller',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) =>
                        $query->whereNot('id', 1)
                            ->whereHas('roles', fn ($q) =>
                            $q->whereIn('id', [1, 5, 6, 7])
                            )
                    )
                    ->searchable()
                    ->preload()
                    ->visible(fn () => static::productAbility('CanViewSeller')),

                SelectFilter::make('model_id')
                    ->relationship('model', 'name')
                    ->preload()
                    ->label(__('admin.model'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewModel')),

                SelectFilter::make('payment_id')
                    ->searchable()
                    ->label(__('admin.payment'))
                    ->relationship('payments.payment', 'name')
                    ->preload(),

                SelectFilter::make('company_id')
                    ->preload()
                    ->options(companies())
                    ->label(__('admin.company'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewCompany')),

                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->preload()
                    ->label(__('admin.category'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewCategory')),

                SelectFilter::make('condition_id')
                    ->relationship('condition', 'name')
                    ->preload()
                    ->label(__('admin.condition'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewCondition')),

                SelectFilter::make('color_id')
                    ->relationship('color', 'name')
                    ->preload()
                    ->label(__('admin.color'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewColor')),

                SelectFilter::make('status_id')
                    ->relationship('status', 'name')
                    ->preload()
                    ->label(__('admin.status'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewStatus')),

                SelectFilter::make('storage_id')
                    ->relationship('storage', 'name')
                    ->preload()
                    ->label(__('admin.storage'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewStorage')),

                SelectFilter::make('battery_id')
                    ->relationship('battery', 'name')
                    ->preload()
                    ->label(__('admin.battery'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewBattery')),

                SelectFilter::make('delivery_id')
                    ->relationship('delivery', 'name')
                    ->preload()
                    ->label(__('admin.delivery'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewDelivery')),

                SelectFilter::make('guarantee_id')
                    ->relationship('guarantee', 'name')
                    ->preload()
                    ->label(__('admin.guarantee'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewGuarantee')),

                SelectFilter::make('hear_about_id')
                    ->relationship('hearAbout', 'name')
                    ->preload()
                    ->label(__('admin.hear_about'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewHearAbout')),

                TernaryFilter::make('is_repaired')
                    ->label(__('admin.is_repaired'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewIsRepaired')),

                TernaryFilter::make('show_repair_information')
                    ->label(__('admin.show_repair_information'))
                    ->searchable()
                    ->visible(fn () => static::productAbility('CanViewShowRepairedInformation')),
                TernaryFilter::make('price')
                    ->label(__('admin.self_price'))
                    ->query(function (Builder $query, array $data): Builder {

                        if (! array_key_exists('value', $data) || $data['value'] == null) {
                            return $query;
                        }

                        return $data['value']
                            ? $query->where(function (Builder $q) {
                                $q->whereNotNull('price')
                                    ->where('price', '>', 0);
                            })
                            : $query->where(function (Builder $q) {
                                $q->whereNull('price')
                                    ->orWhere('price', 0);
                            });
                    })
                    ->visible(fn () => canAbility('CanViewPrice:Product')),
                Filter::make('created_at')
                    ->label(__('admin.created_at'))
                    ->schema([
                        DatePicker::make('created_from')
                            ->label(__('admin.from_date'))
                            ->native(false),
                        DatePicker::make('created_until')
                            ->label(__('admin.to_date'))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from = filled($data['created_from'] ?? null)
                            ? Carbon::parse($data['created_from'])->startOfDay()
                            : null;
                        $until = filled($data['created_until'] ?? null)
                            ? Carbon::parse($data['created_until'])->endOfDay()
                            : null;

                        return $query
                            ->when(
                                $from,
                                fn (Builder $query, Carbon $date): Builder => $query->where('created_at', '>=', $date),
                            )
                            ->when(
                                $until,
                                fn (Builder $query, Carbon $date): Builder => $query->where('created_at', '<=', $date),
                            );
                    }),
            ], FiltersLayout::AboveContentCollapsible)
            ->deferFilters(false)
//            ->persistFiltersInSession()
            ->recordActions([
                ...static::productActions(),
            ])
            ->extraAttributes([
                'class' => 'dual-scroll',
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ...static::productBulkActions(),
                ]),
            ]);
    }
}
