<?php

namespace App\Filament\Resources\Accessories\Schemas;

use App\Forms\Components\NumericInput;
use App\Forms\Components\PriceInput;
use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class AccessoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    TextInput::make('name')
                        ->label(__('admin.name'))
                        ->required()
                        ->maxLength(255),
                    NumericInput::make('quantity')
                        ->label(__('admin.quantity')),
                    Select::make('category_id')
                        ->label(__('admin.category'))
                        ->searchable()
                        ->native(false)
                        ->options(toArray(Category::class)),
                    PriceInput::make('price')
                        ->default(null)
                        ->label(__('admin.self_price')),

                    PriceInput::make('sale_price')
                        ->label(__('admin.sale_price')),

                    PriceInput::make('retail_price')
                        ->label(__('admin.retail_price')),
                ])->columnSpanFull(),
                Toggle::make('is_active')
                    ->label(__('admin.is_active'))
                    ->default(true)
                    ->required(),
            ]);
    }
}
