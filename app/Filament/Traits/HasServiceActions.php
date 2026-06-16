<?php

namespace App\Filament\Traits;

use App\Models\ServiceProduct;
use App\Models\ServiceRepairHistory;
use App\Models\Status;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

trait HasServiceActions
{
    protected static function serviceAbility(string $ability): string
    {
        return "{$ability}:Service";
    }

    /**
     * Central ability check for current user.
     */
    protected static function canAbility(string $ability): bool
    {
        return canAbility(static::serviceAbility($ability));
    }

    /**
     *  Repair Action (Table action)
     */
    public static function repairAction(): Action
    {
        return Action::make('repair')
            ->label(__('admin.repair'))
           // ->disabled(fn($record) => $record->is_repaired)
        //    ->visible(fn($record) => !$record->is_repaired)
            ->icon(Heroicon::Cog8Tooth)
            ->color(fn ($record) => $record?->is_paid ? 'success' : 'info')
            ->modalHeading(__('admin.repair'))
            ->modalSubmitActionLabel(__('admin.save'))
            ->modalWidth('md')
            ->schema([
                TextInput::make('sku')
                    ->label(__('admin.sku'))
                    ->disabled()
                    ->dehydrated(false)
                    ->default(fn (ServiceProduct $record) => $record->sku),

                TextInput::make('repair_price')
                    ->label(__('admin.repair_price'))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->postfix('₾')
                    ->default( 0)
                    ->disabled(fn (ServiceProduct $record) => $record->status_id == 12),

                Textarea::make('comment')
                    ->label(__('admin.comment'))
                    ->rows(3)
                    ->nullable(),

                Select::make('status_id')
                    ->label(__('admin.status'))
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
                    })
                    ->required(),
            ])

            ->action(function (ServiceProduct $record, array $data, $livewire): void {

                DB::transaction(function () use ($record, $data) {

                    $userId = auth()->id();

                    $oldStatusId = (int) $record->status_id;
                    $newStatusId = (int) $data['status_id'];

                    $oldRepairPrice = (float) ($record->repair_price ?? 0);
                    $newRepairPrice = (float) ($data['repair_price'] ?? 0);
                    $delta = $newRepairPrice - $oldRepairPrice;

                    $comment = $data['comment'] ?? null;

                    $record->update([
                        'repair_price' => $newRepairPrice,
                        'status_id' => $newStatusId,
                    ]);

                    if ($newStatusId === 7 && $oldStatusId !== 7) {
                        $record->update(['is_repaired' => true]);
                    }

                    $service = $record->services()->latest()->first();

                    $service->products()->updateExistingPivot($record->id, [
                        'unit_price' => DB::raw('unit_price + '.$newRepairPrice)
                    ]);

                    ServiceRepairHistory::create([
                        'service_id' => $service?->id,
                        'product_id' => $record->id,
                        'user_id' => $userId,
                        'old_status_id' => $oldStatusId,
                        'new_status_id' => $newStatusId,
                        'repair_price' => $newRepairPrice,
                        'price_delta' => $delta,
                        'comment' => $comment,
                    ]);

                    if (!empty($comment)) {
                        $record->comments()->create([
                            'body' => $comment,
                            'author_id' => auth()->id(),
                            'author_type' => User::class,
                        ]);
                    }

                    $record->increment('price', $newRepairPrice);

                    if ($service) {
                        $service->recalculateTotals();
                    }
                });

                Notification::make()
                    ->title(__('admin.saved_successfully'))
                    ->success()
                    ->send();

                $livewire->dispatch('$refresh');
                $livewire->dispatch('refreshService');
            });
    }

    /**
     * All actions in one place
     */
    protected static function serviceActions(): array
    {
        return [
//            EditAction::make()
//                ->visible(fn() => canAbility('UpdateServiceProducts:Product'))
//                ->modalWidth('7xl')
//                ->schema(function ($record): array {
//                    if ($record instanceof ServiceProduct) {
//                        $record->loadMissing(['model', 'battery', 'color', 'condition']);
//                    }
//
//                    return [
//                        Grid::make(3)->schema([
//                            Select::make('model_id')
//                                ->label(__('admin.model'))
//                                ->options(toArray(ProductModel::class))
//                                ->default($record->model_id ?? null)
//                                ->disabled()
//                                ->native(false)
//                                ->dehydrated(false),
//
//                            TextInput::make('sku')
//                                ->label(__('admin.sku'))
//                                ->default($record->sku ?? null)
//                                ->disabled()
//                                ->dehydrated(false),
//
//                            Select::make('battery_id')
//                                ->label(__('admin.battery'))
//                                ->options(toArray(Battery::class))
//                                ->default($record->battery_id ?? null)
//                                ->disabled()
//                                ->native(false)
//                                ->dehydrated(false),
//
//                            Select::make('color_id')
//                                ->label(__('admin.color'))
//                                ->options(toArray(Color::class))
//                                ->default($record->color_id ?? null)
//                                ->disabled()
//                                ->native(false)
//                                ->dehydrated(false),
//
//                            Select::make('condition_id')
//                                ->label(__('admin.condition'))
//                                ->options(toArray(Condition::class))
//                                ->default($record->condition_id ?? null)
//                                ->disabled()
//                                ->native(false)
//                                ->dehydrated(false),
//
//                            PriceInput::make('unit_price')
//                                ->label(__('admin.self_price'))
//                                ->default($record->price)
//                                ->required()
//                                ->live(),
//                        ]),
//                    ];
//                })
//                ->mutateDataUsing(function (array $data): array {
//                    $price = (float)($data['unit_price'] ?? 0);
//
//                    $data['unit_price'] = $price;
//
//                    return $data;
//                })
//                ->using(function ($record, array $data) {
//                    $allowed = array_intersect_key($data, array_flip(['unit_price', 'qty']));
//
//                    if (!empty($allowed)) {
//                        $record->pivot->update($allowed);
//                        $record->update([
//                            'price' => $data['unit_price']
//                        ]);
//                    }
//
//                    return $record;
//                })
//                ->after(function (RelationManager $livewire) {
//                    $livewire->getOwnerRecord()->recalculateTotals();
//                    $livewire->getOwnerRecord()->refresh();
//                    $livewire->dispatch('$refresh');
//                    $livewire->dispatch('refreshService');
//                }),
            CommentsAction::make()
                ->label(fn($record) => __('admin.service_comment') . ' (' . count($record->comments ?? []) . ')')
                ->mentionables(
                    User::query()
                        ->whereHas('roles', function ($query) {
                            $query->where('id', 1);
                        })->get()
                )
                ->poll('5s'),
            static::repairAction(),
            DetachAction::make()
                ->label(__('admin.remove_service_product'))
                ->visible(fn() => canAbility('DetachServiceProducts:Product'))
                ->after(function (RelationManager $livewire) {
                    $livewire->getOwnerRecord()->recalculateTotals();
                    $livewire->getOwnerRecord()->refresh();
                    $livewire->dispatch('$refresh');
                    $livewire->dispatch('refreshService');
                }),
        ];
    }
}
