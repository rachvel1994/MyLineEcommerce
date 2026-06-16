<?php

namespace App\Filament\Resources\Services\RelationManagers;

use App\Models\Service;
use App\Models\Status;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RepairRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceRepairHistories';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->defaultSort('created_at', 'desc')
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
                    ->label(__('admin.service_comment'))
                    ->getStateUsing(fn ($record) =>
                        $record->comment
                        ?? strip_tags($record->product?->service_comment)
                    )
                    ->wrap()
                    ->searchable(),
                TextColumn::make('oldStatus.name')
                    ->color('danger')
                    ->label(__('admin.old_status'))
                    ->searchable(),
                TextColumn::make('newStatus.name')
                    ->color('success')
                    ->label(__('admin.new_status'))
                    ->searchable(),
                ToggleColumn::make('is_paid')
                    ->label(__('admin.is_paid'))
                    ->visible(fn() => canAbility('CanPay:Service'))
                    ->afterStateUpdated(function ($record, bool $state, $livewire) {

                        /** @var Service $service */
                        $service = $livewire->getOwnerRecord();

                        if ($state) {
                            $service->advance_payment += $record->repair_price;
                        } else {
                            $service->advance_payment -= $record->repair_price;
                        }

                        $service->advance_payment = max(0, $service->advance_payment);

                        $service->save();

                        $service->recalculateTotals();

                        $livewire->dispatch('$refresh');
                        $livewire->dispatch('refreshService');
                    })
            ])
            ->filters([
                TernaryFilter::make('is_paid')
                    ->searchable()
                    ->label(__('admin.is_payed')),
                SelectFilter::make('old_status_id')
                    ->label(__('admin.old_status'))
                    ->searchable()
                    ->options(function (): array {
                        $user = auth()->user();

                        $ids = match (true) {
                            $user?->hasRole('ადმინისტრატორი') => [7, 6, 3, 11],
                            $user?->hasRole('სერვისშია') => [7, 6, 3],
                            default => [7, 6, 11],
                        };

                        return Status::whereIn('id', $ids)
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
                SelectFilter::make('new_status_id')
                    ->label(__('admin.new_status'))
                    ->searchable()
                    ->options(function (): array {
                        $user = auth()->user();

                        $ids = match (true) {
                            $user?->hasRole('ადმინისტრატორი') => [7, 6, 3, 11],
                            $user?->hasRole('სერვისშია') => [7, 6, 3],
                            default => [7, 6, 11],
                        };

                        return Status::whereIn('id', $ids)
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
            ])
            ->headerActions([
                Action::make('pay_all')
                    ->label(__('admin.pay_all'))
                    ->visible(fn() => canAbility('CanPayAll:Service'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($livewire) {

                        /** @var Service $service */
                        $service = $livewire->getOwnerRecord();

                        $totalToAdd = 0;

                        foreach ($service->serviceRepairHistories as $repair) {
                            if (!$repair->is_paid) {
                                $repair->is_paid = true;
                                $repair->save();

                                $totalToAdd += $repair->repair_price;
                            }
                        }
                        $service->advance_payment += $totalToAdd;
                        $service->save();

                        $service->recalculateTotals();

                        $livewire->dispatch('$refresh');
                        $livewire->dispatch('refreshService');
                    })
            ])
            ->recordActions([
                DeleteAction::make()
                    ->visible(fn() => canAbility('Delete:Service'))
                    ->disabled(fn() => !canAbility('Delete:Service'))
                    ->after(function ($livewire) {
                        $livewire->dispatch('$refresh');
                        $livewire->dispatch('refreshService');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->disabled(fn() => !canAbility('Delete:Service'))
                    ,
                ])->visible(fn() => canAbility('Delete:Service')),
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
