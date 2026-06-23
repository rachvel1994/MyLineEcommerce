<?php

namespace App\Filament\Traits;

use App\Models\User;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

trait HasUserHeaderActions
{
    protected static function userAbility(string $ability): string
    {
        return "{$ability}:User";
    }

    protected static function canUser(string $ability): bool
    {
        return canAbility(static::userAbility($ability));
    }

    public static function userHeaderActions(): array
    {
        return [
            static::sendSms(),
        ];
    }

    protected static function sendSms(): Action
    {
        return Action::make('sendSms')
            ->label(__('admin.send_sms'))
            ->visible(static::canUser('CanSendSms'))
            ->disabled(! static::canUser('CanSendSms'))
            ->icon(Heroicon::ChatBubbleLeft)
            ->schema([
                Select::make('users')
                    ->label(__('admin.user'))
                    ->multiple()
                    ->searchable()
                    ->options(fn (): array => User::query()->pluck('name', 'id')->toArray())
                    ->native(false),
                Select::make('rating')
                    ->label(__('admin.rating_select'))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options([
                        1 => '⭐',
                        2 => '⭐⭐',
                        3 => '⭐⭐⭐',
                        4 => '⭐⭐⭐⭐',
                        5 => '⭐⭐⭐⭐⭐',
                    ])
                    ->rule(function ($get) {
                        return function (string $attribute, $value, Closure $fail) use ($get) {

                            $users = $get('users') ?? [];
                            $ratings = is_array($value) ? $value : [];

                            if (empty($users) && empty($ratings)) {
                                $fail(__('admin.select_user_or_rating'));
                            }
                        };
                    }),
                Textarea::make('message')
                    ->label(__('admin.message'))
                    ->required()
                    ->rows(4),
            ])

            ->action(function (array $data) {
                $manualUsers = collect();

                if (! empty($data['users'])) {
                    $manualUsers = User::query()
                        ->when(! empty($data['users']), fn ($q) => $q->whereIn('id', $data['users']))
                        ->get();
                }
                $ratingUsers = collect();

                if (! empty($data['rating'])) {

                    $ratingUsers = User::query()
                        ->whereIn('rating', array_values($data['rating']))
                        ->whereNotNull('mobile')
                        ->get();
                }

                $allPhones = collect([$manualUsers, $ratingUsers])
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
                    } catch (\Throwable $e) {
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
