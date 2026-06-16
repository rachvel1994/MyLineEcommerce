<?php

namespace App\Filament\Resources\ServiceReturned\Tables;

use App\Models\ServiceProduct;
use App\Models\Status;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

class ServiceReturnedTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query
                    ->with(['model', 'battery', 'color', 'condition'])
                    ->where('status_id', 12)
                    ->when(!canAbility('ShowAllProducts:User'), function ($q) {
                        $q->whereHas('services', function ($serviceQuery) {
                            $serviceQuery->where('technic_id', auth()->id());
                        });
                    })
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('model.name')
                    ->label(__('admin.model'))
                    ->searchable(),

                TextColumn::make('sku')
                    ->label(__('admin.sku'))
                    ->copyable()
                    ->searchable(),

                TextColumn::make('battery.name')
                    ->label(__('admin.battery'))
                    ->searchable(),

                TextColumn::make('color.name')
                    ->label(__('admin.color'))
                    ->searchable(),

                TextColumn::make('condition.name')
                    ->label(__('admin.condition'))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->persistFiltersInSession()
            ->recordActions([
                CommentsAction::make()
                    ->label(fn($record) => __('admin.comment') . ' (' . count($record->comments) . ')')
                    ->mentionables(
                        User::query()
                            ->whereHas('roles', function ($query) {
                                $query->where('id', 1);
                            })->get()
                    )
                    ->poll('5s'),
                Action::make('repair')
                    ->label(__('admin.repair'))
                    ->icon(Heroicon::Cog8Tooth)
                    ->color(fn($record) => $record?->is_paid ? 'success' : 'info')
                    ->modalHeading(__('admin.repair'))
                    ->modalSubmitActionLabel(__('admin.save'))
                    ->modalWidth('md')
                    ->schema([
                        TextInput::make('sku')
                            ->label(__('admin.sku'))
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn(ServiceProduct $record) => $record->sku),


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
                                    $user?->hasRole('ადმინისტრატორი') => [7, 6, 3, 11, 12],
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
                            $comment = $data['comment'] ?? null;

                            $record->update(['status_id' => $data['status_id']]);

                            if (!empty($comment)) {
                                $record->comments()->create([
                                    'body' => $comment,
                                    'author_id' => $userId,
                                    'author_type' => User::class,
                                ]);
                            }
                        });

                        Notification::make()
                            ->title(__('admin.saved_successfully'))
                            ->success()
                            ->send();

                        $livewire->dispatch('$refresh');
                        $livewire->dispatch('refreshService');
                    }),
            ])
            ->toolbarActions([
            ]);
    }
}
