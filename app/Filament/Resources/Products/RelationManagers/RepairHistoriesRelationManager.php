<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RepairHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'repairHistories';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['newStatus', 'oldStatus', 'product.model']))
            ->recordTitleAttribute('product_id')
            ->columns([
                TextColumn::make('product.model.name')
                    ->label(__('admin.name'))
                    ->searchable(),
                TextColumn::make('product.sku')
                    ->label(__('admin.product'))
                    ->copyable()
                    ->searchable(),
                TextColumn::make('repair_price')
                    ->label(__('admin.price'))
                    ->money('GEL'),
                TextColumn::make('comment')
                    ->label(__('admin.comment'))
                    ->searchable(), TextColumn::make('product.service_comment')
                    ->label(__('admin.service_comment'))
                    ->searchable(),

                TextColumn::make('oldStatus.name')
                    ->color('danger')
                    ->label(__('admin.old_status'))
                    ->searchable(),
                TextColumn::make('newStatus.name')
                    ->color('success')
                    ->label(__('admin.new_status'))
                    ->searchable(),
                IconColumn::make('is_paid')
                    ->boolean()
                    ->label(__('admin.is_paid')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->recordActions([
            ])
            ->toolbarActions([
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.repair_history');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __('admin.repair_history');
    }
}
