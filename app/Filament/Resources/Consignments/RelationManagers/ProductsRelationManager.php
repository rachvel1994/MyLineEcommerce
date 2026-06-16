<?php

namespace App\Filament\Resources\Consignments\RelationManagers;

use App\Forms\Components\PriceInput;
use App\Models\Battery;
use App\Models\Color;
use App\Models\Condition;
use App\Models\ConsignmentPriceChange;
use App\Models\Payment;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static string $model = Product::class;

    protected static ?string $recordTitleAttribute = 'sku';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['model', 'battery', 'color', 'condition']))
            ->defaultSort('pivot_created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('model.name')
                    ->label(__('admin.model'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label(__('admin.sku'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('battery.name')
                    ->label(__('admin.battery'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('color.name')
                    ->label(__('admin.color'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('condition.name')
                    ->label(__('admin.condition'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('line_total')
                    ->label(__('admin.retail_price'))
                    ->money('GEL', true),
            ])
            ->headerActions([
                static::Pay()
                    ->after(function (RelationManager $livewire) {
                        static::refreshOwnerRecord($livewire);
                    }),

                AttachAction::make()
                    ->label(__('admin.add_consignment_product'))
                    ->preloadRecordSelect()
                    ->authorize(fn () => true)
                    ->modalWidth('7xl')
                    ->schema(function (AttachAction $action): array {
                        return [
                            $action->getRecordSelect()
                                ->label(__('admin.product'))
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->getOptionLabelFromRecordUsing(function (Product $record): string {
                                    $record->loadMissing('model');

                                    $sku = trim((string) $record->sku);
                                    $modelName = trim((string) $record->model?->name);

                                    return trim($modelName . ($sku !== '' ? " — {$sku}" : ''));
                                })
                                ->getSearchResultsUsing(function (string $search): array {
                                    $consignmentId = $this->getOwnerRecord()->getKey();

                                    return Product::query()
                                        ->with('model')

                                        /*
                                         * Optional but recommended:
                                         * only show available products.
                                         * Because detach sets status_id = 1.
                                         */
                                        ->where('status_id', 1)

                                        ->whereDoesntHave('consignments', function ($q) use ($consignmentId) {
                                            $q->where('consignment_id', $consignmentId);
                                        })
                                        ->when($search !== '', function ($q) use ($search) {
                                            $q->where(function ($q) use ($search) {
                                                $q->whereHas('model', function ($q) use ($search) {
                                                    $q->where('name', 'like', "%{$search}%");
                                                })
                                                    ->orWhere('sku', 'like', "%{$search}%");
                                            });
                                        })
                                        ->orderByDesc('id')
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(function (Product $product) {
                                            $sku = trim((string) $product->sku);
                                            $modelName = trim((string) $product->model?->name);

                                            $label = trim($modelName . ($sku !== '' ? " — {$sku}" : ''));

                                            return [$product->getKey() => $label];
                                        })
                                        ->toArray();
                                })
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $product = Product::with(['model', 'battery', 'color', 'condition'])->find($state);

                                    if (!$product) {
                                        $set('model_name', null);
                                        $set('sku', null);
                                        $set('battery_id', null);
                                        $set('color_id', null);
                                        $set('condition_id', null);
                                        $set('retail_price', null);
                                        $set('unit_price', null);
                                        $set('line_total', null);

                                        return;
                                    }

                                    $set('model_name', $product->model?->name);
                                    $set('sku', $product->sku);
                                    $set('battery_id', $product->battery_id);
                                    $set('color_id', $product->color_id);
                                    $set('condition_id', $product->condition_id);
                                    $set('retail_price', $product->retail_price);

                                    $price = (float) ($product->retail_price ?? $product->sale_price ?? 0);

                                    $set('unit_price', $price);
                                    $set('line_total', $price);
                                }),

                            Grid::make(6)->schema([
                                TextInput::make('model_name')
                                    ->label(__('admin.product'))
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('sku')
                                    ->label(__('admin.sku'))
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('battery_id')
                                    ->label(__('admin.battery'))
                                    ->options(toArray(Battery::class))
                                    ->searchable()
                                    ->native(false)
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('color_id')
                                    ->label(__('admin.color'))
                                    ->searchable()
                                    ->native(false)
                                    ->options(toArray(Color::class))
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('condition_id')
                                    ->label(__('admin.condition'))
                                    ->searchable()
                                    ->native(false)
                                    ->options(toArray(Condition::class))
                                    ->disabled()
                                    ->dehydrated(false),

                                PriceInput::make('retail_price')
                                    ->label(__('admin.retail_price')),

                                Hidden::make('unit_price')
                                    ->label(__('admin.price'))
                                    ->required()
                                    ->live(),
                            ]),
                        ];
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $price = (float) ($data['retail_price'] ?? $data['unit_price'] ?? 0);

                        $data['unit_price'] = $price;
                        $data['line_total'] = $price;

                        return $data;
                    })
                    ->after(function (RelationManager $livewire, array $data, ?Product $record = null) {
                        $productIds = static::getAttachedProductIds($data, $record);

                        if (!empty($productIds)) {
                            Product::query()
                                ->whereIn('id', $productIds)
                                ->update([
                                    'status_id' => 4,
                                    'user_id' => $livewire->getOwnerRecord()->customer_id,
                                    'seller_id' => auth()->id(),
                                    'created_at' => now()
                                ]);
                        }

                        static::refreshOwnerRecord($livewire);
                    })
                    ->visible(fn () => canAbility('AttachConsignmentProducts:Product')),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => canAbility('UpdateConsignmentProducts:Products'))
                    ->modalWidth('7xl')
                    ->schema(function ($record): array {
                        if ($record instanceof Product) {
                            $record->loadMissing(['model', 'battery', 'color', 'condition']);
                        }

                        return [
                            Grid::make(6)->schema([
                                TextInput::make('model_name')
                                    ->label(__('admin.product'))
                                    ->default($record->model?->name ?? null)
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('sku')
                                    ->label(__('admin.sku'))
                                    ->default($record->sku ?? null)
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('battery_id')
                                    ->label(__('admin.battery'))
                                    ->options(toArray(Battery::class))
                                    ->default($record->battery_id ?? null)
                                    ->disabled()
                                    ->native(false)
                                    ->dehydrated(false),

                                Select::make('color_id')
                                    ->label(__('admin.color'))
                                    ->options(toArray(Color::class))
                                    ->default($record->color_id ?? null)
                                    ->disabled()
                                    ->native(false)
                                    ->dehydrated(false),

                                Select::make('condition_id')
                                    ->label(__('admin.condition'))
                                    ->options(toArray(Condition::class))
                                    ->default($record->condition_id ?? null)
                                    ->disabled()
                                    ->native(false)
                                    ->dehydrated(false),

                                PriceInput::make('line_total')
                                    ->label(__('admin.retail_price'))
                                    ->default($record->pivot->line_total ?? $record->line_total ?? null),

                                Hidden::make('unit_price')
                                    ->label(__('admin.price'))
                                    ->default($record->pivot->unit_price ?? 0)
                                    ->required()
                                    ->live(),
                            ]),
                        ];
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $price = (float) ($data['line_total'] ?? $data['unit_price'] ?? 0);

                        $data['unit_price'] = $price;
                        $data['line_total'] = $price;

                        return $data;
                    })
                    ->using(function ($record, array $data) {
                        $allowed = array_intersect_key(
                            $data,
                            array_flip(['unit_price', 'qty', 'line_total'])
                        );

                        if (!empty($allowed)) {
                            $record->pivot->update($allowed);
                        }

                        return $record;
                    })
                    ->after(function (RelationManager $livewire) {
                        static::refreshOwnerRecord($livewire);
                    }),

                DetachAction::make()
                    ->label(__('admin.remove_consignment_product'))
                    ->visible(fn () => canAbility('DetachConsignmentProducts:Products'))
                    ->after(function (RelationManager $livewire, Product $record) {
                        /*
                         * Detached product becomes available again.
                         */
                        $record->status_id = 1;
                        $record->save();

                        static::refreshOwnerRecord($livewire);
                    }),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.product');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __('admin.product');
    }

    private static function Pay(): Action
    {
        return Action::make('pay')
            ->label(__('admin.pay'))
            ->icon(Heroicon::CurrencyDollar)
            ->color('info')
            ->modalHeading(
                fn (RelationManager $livewire) => __('admin.consignment') . ' #' . ($livewire->getOwnerRecord()->id ?? '')
            )
            ->modalDescription(
                fn (RelationManager $livewire) => __('admin.current_debt') . ': ' . money($livewire->getOwnerRecord()->debt ?? 0)
            )
            ->schema([
                PriceInput::make('amount')
                    ->label(__('admin.amount'))
                    ->numeric()
                    ->minValue(0.01)
                    ->required()
                    ->maxValue(fn (RelationManager $livewire) => $livewire->getOwnerRecord()->debt ?? 0)
                    ->helperText(
                        fn (RelationManager $livewire) => __('admin.max') . ': ' .
                            number_format($livewire->getOwnerRecord()->debt ?? 0, 2) . ' ₾'
                    ),

                Select::make('payment_id')
                    ->label(__('admin.payment'))
                    ->options(toArray(Payment::class))
                    ->searchable()
                    ->required(),
            ])
            ->action(function (RelationManager $livewire, array $data) {
                $record = $livewire->getOwnerRecord();

                if (!$record) {
                    return;
                }

                DB::transaction(function () use ($record, $data) {
                    $amount = (float) ($data['amount'] ?? 0);
                    $debt = (float) ($record->debt ?? 0);

                    /*
                     * Prevent overpay.
                     */
                    if ($amount > $debt) {
                        $amount = $debt;
                    }

                    if ($amount <= 0) {
                        return;
                    }

                    ConsignmentPriceChange::query()->create([
                        'consignment_id' => $record->id,
                        'paid_amount' => $amount,
                        'payment_id' => $data['payment_id'] ?? null,
                        'debt' => max(0, $debt - $amount),
                        'total' => $record->subtotal,
                    ]);

                    $record->advance_payment = round(
                        (float) ($record->advance_payment ?? 0) + $amount,
                        2
                    );

                    $record->debt = max(
                        0,
                        round((float) ($record->subtotal ?? 0) - $record->advance_payment, 2)
                    );

                    $record->is_paid = $record->debt <= 0
                        || $record->advance_payment >= (float) ($record->subtotal ?? 0);

                    /*
                     * Fully paid consignment.
                     */
                    if ($record->is_paid) {
                        $record->status_id = 4;
                    }

                    $record->save();

                    /*
                     * Fully paid consignment products become sold/paid.
                     */
                    if ($record->is_paid) {
                        $productIds = $record
                            ->products()
                            ->pluck('products.id')
                            ->toArray();

                        if (!empty($productIds)) {
                            Product::query()
                                ->whereIn('id', $productIds)
                                ->update([
                                    'status_id' => 4,
                                ]);
                        }
                    }
                });
            })
            ->visible(fn () => canAbility('Update:Consignment'));
    }

    private static function refreshOwnerRecord(RelationManager $livewire): void
    {
        $ownerRecord = $livewire->getOwnerRecord();

        if (!$ownerRecord) {
            return;
        }

        $ownerRecord->recalculateTotals();
        $ownerRecord->refresh();

        $livewire->dispatch('$refresh');
        $livewire->dispatch('refreshConsignment');
    }

    private static function getAttachedProductIds(array $data, ?Product $record = null): array
    {
        $ids = [];

        if ($record instanceof Product) {
            $ids[] = $record->getKey();
        }

        $selectedIds = $data['recordId']
            ?? $data['record_id']
            ?? $data['recordIds']
            ?? $data['record_ids']
            ?? null;

        if ($selectedIds !== null) {
            $selectedIds = is_array($selectedIds)
                ? $selectedIds
                : [$selectedIds];

            foreach ($selectedIds as $selectedId) {
                if ($selectedId !== null && $selectedId !== '') {
                    $ids[] = $selectedId;
                }
            }
        }

        return array_values(array_unique($ids));
    }
}