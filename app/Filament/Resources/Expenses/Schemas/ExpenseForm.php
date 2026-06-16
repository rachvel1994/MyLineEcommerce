<?php

namespace App\Filament\Resources\Expenses\Schemas;


use App\Forms\Components\PriceInput;
use App\Models\ExpenseType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)->schema([
                    Select::make('expense_type_id')
                        ->label(__('admin.type'))
                        ->options(toArray(ExpenseType::class))
                        ->searchable()
                        ->native(false)
                        ->preload()
                        ->required(),

                    DateTimePicker::make('spent_at')
                        ->label(__('admin.date'))
                        ->native(false)
                        ->default(now())
                        ->required(),

                    PriceInput::make('amount')
                        ->label(__('admin.amount')),

                    Select::make('user_id')
                        ->label(__('admin.user'))
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->relationship('user', 'name'),
                ])->columnSpanFull(),
                Textarea::make('description')
                    ->label(__('admin.description'))
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }
}
