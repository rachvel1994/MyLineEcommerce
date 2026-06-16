<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(5)->schema([
                    TextInput::make('name')
                        ->required()
                        ->label(__('admin.full_name'))
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->label(__('admin.email'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('mobile')
                        ->label(__('admin.mobile'))
                        ->numeric()
                        ->maxLength(12),
                    TextInput::make('id_number')
                        ->label(__('admin.id_number'))
                        ->numeric()
                        ->maxLength(12),
                    Select::make('rating')
                        ->label(__('admin.rating'))
                        ->searchable()
                        ->options([
                            1 => '⭐',
                            2 => '⭐⭐',
                            3 => '⭐⭐⭐',
                            4 => '⭐⭐⭐⭐',
                            5 => '⭐⭐⭐⭐⭐',
                        ])
                ])->columnSpanFull(),
                Grid::make(3)->schema([
                    Select::make('roles')
                        ->preload()
                        ->relationship('roles', 'name')
                        ->required()
                        ->disabled(fn() => !auth()->user()->roles->contains('id', 1))
                        ->native(false)
                        ->label(__('admin.role'))
                        ->searchable(),
                    TextInput::make('password')
                        ->label(__('admin.password'))
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->required(fn($record) => !$record)
                        ->rule(Password::default())
                        ->dehydrated(fn($state) => filled($state))
                        ->dehydrateStateUsing(fn($state) => Hash::make($state))
                        ->same('passwordConfirmation'),
                    TextInput::make('passwordConfirmation')
                        ->label(__('admin.password_confirmation'))
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->required(fn($record) => !$record)
                        ->dehydrated(false),
                ])->columnSpanFull(),
                Textarea::make('comment')
                    ->label(__('admin.comment'))
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
