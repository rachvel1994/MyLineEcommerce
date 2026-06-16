<?php

namespace App\Filament\Resources\CashMovements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CashMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('cash_drawer_id')
                    ->required()
                    ->numeric(),
                TextInput::make('direction')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('reason'),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                TextInput::make('related_type'),
                TextInput::make('related_id')
                    ->numeric(),
                Select::make('payment_id')
                    ->relationship('payment', 'name'),
                DateTimePicker::make('moved_at')
                    ->required(),
            ]);
    }
}
