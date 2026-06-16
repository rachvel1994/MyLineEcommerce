<?php

namespace App\Filament\Resources\Consignments\RelationManagers;

use App\Models\ConsignmentPriceChange;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PriceChangesRelationManager extends RelationManager
{
    protected static string $relationship = 'priceChanges';
    
    protected static string $model = ConsignmentPriceChange::class;

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->date()
                    ->searchable(),
                TextColumn::make('paid_amount')
                    ->label(__('admin.payed_amount'))
                    ->money('GEL')
                    ->searchable(),

                TextColumn::make('payment.name')
                    ->label(__('admin.payment'))
                    ->searchable(),

                TextColumn::make('debt')
                    ->label(__('admin.debt'))
                    ->money('GEL', true),

                TextColumn::make('total')
                    ->label(__('admin.total'))
                    ->money('GEL', true),
            ])->recordActions([
                DeleteAction::make()
                    ->label(__('admin.remove_consignment_price_change'))
                    ->after(function ($record, RelationManager $livewire) {
                        $livewire->getOwnerRecord()->recalculateTotals();
                        $livewire->getOwnerRecord()->refresh();

                        $livewire->dispatch('$refresh');
                        $livewire->dispatch('refreshConsignment');
                    }),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.price_history');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __('admin.price_history');
    }
}
