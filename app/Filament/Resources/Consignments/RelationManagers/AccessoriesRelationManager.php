<?php

namespace App\Filament\Resources\Consignments\RelationManagers;

use App\Forms\Components\PriceInput;
use App\Models\Accessory;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AccessoriesRelationManager extends RelationManager
{

    protected static string $relationship = 'accessories';

    protected static string $model = Accessory::class;
    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('pivot_created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.accessory'))
                    ->searchable(),

                TextColumn::make('pivot.qty')
                    ->label(__('admin.quantity')),

                TextColumn::make('pivot.unit_price')
                    ->label(__('admin.unit'))
                    ->money('GEL', true),

                TextColumn::make('pivot.line_total')
                    ->label(__('admin.total_price'))
                    ->money('GEL', true),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label(__('admin.add_consignment_product'))
                    ->preloadRecordSelect()
                    ->authorize(fn() => true)
                    ->modalWidth('7xl')
                    ->schema(function (AttachAction $action): array {
                        return [
                            $action->getRecordSelect()
                                ->label(__('admin.accessory'))
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->getOptionLabelFromRecordUsing(function (Accessory $record): string {
                                    return trim($record->name);
                                })
                                ->getSearchResultsUsing(function (string $search): array {
                                    $consignmentId = $this->getOwnerRecord()->getKey();

                                    return Accessory::query()
                                        ->whereDoesntHave('consignments', fn($q) => $q->where('consignment_id', $consignmentId)
                                        )
                                        ->when($search !== '', function ($q) use ($search) {
                                            $q->where('name', 'like', "%{$search}%");
                                        })
                                        ->orderByDesc('id')
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(function (Accessory $a) {
                                            return [$a->getKey() => trim($a->name)];
                                        })
                                        ->toArray();
                                })
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $accessory = Accessory::query()->find($state);

                                    if (!$accessory) {
                                        $set('name', null);
                                        $set('qty', 1);
                                        $set('unit_price', null);
                                        $set('line_total', null);
                                        return;
                                    }

                                    $set('name', $accessory->name);

                                    $price = (float)($accessory->sale_price ?? $accessory->retail_price ?? 0);
                                    $set('unit_price', $price);

                                    $qty = (float)($get('qty') ?: 1);
                                    $set('qty', $qty);
                                    $set('line_total', $qty * $price);
                                }),

                            Grid::make()->schema([
                                TextInput::make('name')
                                    ->label(__('admin.accessory'))
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('qty')
                                    ->label(__('admin.quantity'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $qty = (float)($state ?? 0);
                                        $price = (float)($get('unit_price') ?? 0);
                                        $set('line_total', $qty * $price);
                                    }),
                            ]),

                            Grid::make()->schema([
                                PriceInput::make('unit_price')
                                    ->label(__('admin.unit'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $price = (float)($state ?? 0);
                                        $qty = (float)($get('qty') ?? 0);
                                        $set('line_total', $qty * $price);
                                    }),

                                PriceInput::make('line_total')
                                    ->label(__('admin.total_price'))
                                    ->disabled()
                                    ->dehydrated(true),
                            ]),
                        ];
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $qty = (float)($data['qty'] ?? 0);
                        $price = (float)($data['unit_price'] ?? 0);

                        $data['line_total'] = $qty * $price;

                        return $data;
                    })
                    ->after(function (RelationManager $livewire) {
                        $livewire->getOwnerRecord()->recalculateTotals();
                        $livewire->getOwnerRecord()->refresh();

                        $livewire->dispatch('$refresh');
                        $livewire->dispatch('refreshConsignment');
                    })
                    ->visible(fn() => canAbility('AttachConsignmentAccessories:Accessory')),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn() => canAbility('UpdateConsignmentAccessories:Accessory'))
                    ->modalWidth('7xl')
                    ->schema(function ($record): array {
                        /** @var Accessory $record */
                        return [
                            Grid::make()->schema([
                                Select::make('accessory_id')
                                    ->label(__('admin.accessory'))
                                    ->options(
                                        Accessory::query()
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray()
                                    )
                                    ->default($record->id)
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('qty')
                                    ->label(__('admin.quantity'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $qty = (float)($state ?? 0);
                                        $price = (float)($get('unit_price') ?? 0);
                                        $set('line_total', $qty * $price);
                                    }),
                            ]),

                            Grid::make()->schema([
                                PriceInput::make('unit_price')
                                    ->label(__('admin.unit'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->step('0.01')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $price = (float)($state ?? 0);
                                        $qty = (float)($get('qty') ?? 0);
                                        $set('line_total', $qty * $price);
                                    }),

                                PriceInput::make('line_total')
                                    ->label(__('admin.total_price'))
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->afterStateHydrated(function ($component, $state, Get $get) {
                                        $qty = (float)($get('qty') ?? 0);
                                        $price = (float)($get('unit_price') ?? 0);
                                        $component->state($qty * $price);
                                    }),
                            ]),
                        ];
                    })
                    ->mutateDataUsing(function (array $data): array {
                        $qty = (float)($data['qty'] ?? 0);
                        $price = (float)($data['unit_price'] ?? 0);

                        $data['line_total'] = $qty * $price;

                        return $data;
                    })
                    ->using(function ($record, array $data) {
                        $record->pivot->update($data);

                        return $record;
                    })
                    ->after(function (RelationManager $livewire) {
                        $livewire->getOwnerRecord()->recalculateTotals();
                        $livewire->getOwnerRecord()->refresh();

                        $livewire->dispatch('$refresh');
                        $livewire->dispatch('refreshConsignment');
                    }),

                DetachAction::make()
                    ->label(__('admin.remove_consignment_product'))
                    ->visible(fn() => canAbility('DetachConsignmentAccessories:Accessory'))
                    ->after(function (RelationManager $livewire) {
                        $livewire->getOwnerRecord()->recalculateTotals();
                        $livewire->getOwnerRecord()->refresh();

                        $livewire->dispatch('$refresh');
                        $livewire->dispatch('refreshConsignment');
                    }),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.accessory');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __('admin.accessory');
    }
}
