<?php

namespace App\Filament\Resources\ProductModels\Schemas;

use App\Models\ProductModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductModelForm
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
                Select::make('parent_id')
                    ->options(toArray(ProductModel::class))
                    ->searchable()
                    ->label(__('admin.parent')),
                Toggle::make('is_active')
                    ->default(true)
                    ->label(__('admin.is_active'))
                    ->required(),
            ]);
    }
}
