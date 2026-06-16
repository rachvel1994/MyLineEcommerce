<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.name'))
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_cash_analytic')
                    ->default(false)
                    ->label(__('admin.is_cash_analytic'))
                    ->required(),
                Toggle::make('is_active')
                    ->default(true)
                    ->label(__('admin.is_active'))
                    ->required(),
            ]);
    }
}
