<?php

namespace App\Filament\Resources\HearAbouts\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HearAboutForm
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
                ColorPicker::make('color')
                    ->label(__('admin.color'))
                    ->default('#f59e0b')
                    ->required(),
                Toggle::make('is_active')
                    ->default(true)
                    ->label(__('admin.is_active'))
                    ->required(),
            ]);
    }
}
