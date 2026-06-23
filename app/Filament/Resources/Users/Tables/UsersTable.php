<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->withCount([
                    'products as paid_products_count' => function ($q) {
                        $q->where('status_id', 4);
                    },
                ]);
            })
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.full_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('mobile')
                    ->label(__('admin.mobile'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('email')
                    ->label(__('admin.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('id_number')
                    ->label(__('admin.id_number'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('rating')
                    ->label(__('admin.rating'))
                    ->formatStateUsing(fn ($record) => [
                        1 => '⭐',
                        2 => '⭐⭐',
                        3 => '⭐⭐⭐',
                        4 => '⭐⭐⭐⭐',
                        5 => '⭐⭐⭐⭐⭐',
                    ][$record->rating])
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('comment')
                    ->label(__('admin.comment'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('paid_products_count')
                    ->label(__('admin.products_count'))
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable()
                    ->label(__('admin.role')),

                Filter::make('paid_products_count')
                    ->schema([
                        TextInput::make('from')
                            ->label(__('admin.min_from_quantity'))
                            ->numeric(),
                        TextInput::make('to')
                            ->label(__('admin.max_to_quantity'))
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {

                        return $query
                            ->when($data['from'] ?? null, function ($query, $from) {
                                $query->whereHas('products', function ($q) {
                                    $q->where('status_id', 4);
                                }, '>=', $from);
                            })
                            ->when($data['to'] ?? null, function ($query, $to) {
                                $query->whereHas('products', function ($q) {
                                    $q->where('status_id', 4);
                                }, '<=', $to);
                            });
                    }),

                SelectFilter::make('rating')
                    ->preload()
                    ->searchable()
                    ->options([
                        1 => '⭐',
                        2 => '⭐⭐',
                        3 => '⭐⭐⭐',
                        4 => '⭐⭐⭐⭐',
                        5 => '⭐⭐⭐⭐⭐',
                    ])
                    ->label(__('admin.rating')),

                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')->label(__('admin.from_date')),
                        DatePicker::make('until')->label(__('admin.to_date')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $from = filled($data['from'] ?? null) ? Carbon::parse($data['from'])->startOfDay() : null;
                        $until = filled($data['until'] ?? null) ? Carbon::parse($data['until'])->endOfDay() : null;

                        return $query
                            ->when($from, fn (Builder $query, Carbon $date) => $query->where('created_at', '>=', $date))
                            ->when($until, fn (Builder $query, Carbon $date) => $query->where('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::sendSmsBulk(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->persistFiltersInSession()
            ->defaultPaginationPageOption(50)
            ->defaultSort('id', 'desc');
    }

    protected static function sendSmsBulk(): BulkAction
    {
        return BulkAction::make('sendSms')
            ->label(__('admin.send_sms'))
            ->icon(Heroicon::ChatBubbleLeft)
            ->visible(canAbility('CanSendSms:User'))
            ->disabled(! canAbility('CanSendSms:User'))
            ->schema([
                Select::make('rating')
                    ->label(__('admin.rating_select'))
                    ->multiple()
                    ->options([
                        1 => '⭐',
                        2 => '⭐⭐',
                        3 => '⭐⭐⭐',
                        4 => '⭐⭐⭐⭐',
                        5 => '⭐⭐⭐⭐⭐',
                    ]),

                Textarea::make('message')
                    ->label(__('admin.message'))
                    ->required()
                    ->rows(4),
            ])
            ->action(function (Collection $records, array $data) {
                $selectedUsers = $records;

                $ratingUsers = collect();
                if (! empty($data['rating'])) {
                    $ratingUsers = User::query()
                        ->whereIn('rating', $data['rating'])
                        ->whereNotNull('mobile')
                        ->get();
                }

                $allPhones = collect([$selectedUsers, $ratingUsers])
                    ->flatten()
                    ->filter(fn ($user) => ! empty($user->mobile))
                    ->map(fn ($user) => [
                        'number' => $user->mobile,
                        'name' => $user->name,
                    ])
                    ->unique('number')
                    ->values();

                foreach ($allPhones as $entry) {
                    try {
                        send_sms(
                            mobile: $entry['number'],
                            template: $data['message'],
                        );
                    } catch (Throwable $e) {
                        report($e);
                    }
                }

                Notification::make()
                    ->title(__('admin.sms_sent_success', [
                        'count' => $allPhones->count(),
                    ]))
                    ->success()
                    ->send();
            })
            ->modalHeading(__('admin.send_sms'))
            ->modalSubmitActionLabel(__('admin.send'))
            ->color('info');
    }
}
