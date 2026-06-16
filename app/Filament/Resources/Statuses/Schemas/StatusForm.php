<?php

namespace App\Filament\Resources\Statuses\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class StatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    TextInput::make('name')
                        ->label(__('admin.name'))
                        ->unique(ignoreRecord: true)
                        ->required()
                        ->maxLength(255),

                    ColorPicker::make('color')
                        ->label(__('admin.color'))
                        ->default('#f59e0b')
                        ->required(),

                    TextInput::make('sort_order')
                        ->label(__('admin.sort_order'))
                        ->numeric()
                        ->default(0)
                        ->required(),

                    FileUpload::make('image')
                        ->label(__('admin.image'))
                        ->image()
                        ->directory('status/images')
                        ->panelLayout('grid')
                        ->previewable(),

                    Toggle::make('is_active')
                        ->default(true)
                        ->label(__('admin.is_active'))
                        ->required(),

                    Toggle::make('show_in_product')
                        ->default(true)
                        ->label(__('admin.show_in_product'))
                        ->required(),
                ])->columnSpanFull()
            ]);
    }
}