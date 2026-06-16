<?php

namespace App\Filament\Resources\CashDrawers\Schemas;

use App\Forms\Components\PriceInput;
use App\Models\CashDrawer;
use Filament\Schemas\Schema;

class CashDrawerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                PriceInput::make('opening_balance')
                    ->label(__('admin.opening_balance'))
                    ->readOnly()
                    ->dehydrated(true)
                    ->default(function () {
                        $previousDrawer = CashDrawer::query()
                            ->latest('id')
                            ->first();
                        return $previousDrawer?->current_balance ?? 0;
                    }),
                PriceInput::make('current_balance')
                    ->label(__('admin.current_balance'))
                    ->readOnly()
                    ->dehydrated(true)
                    ->default(function ($record) {
                        if ($record) {
                            return $record->current_balance;
                        }
                        $previousDrawer = CashDrawer::query()
                            ->latest('id')
                            ->first();
                        return $previousDrawer?->current_balance ?? 0;
                    }),
            ]);
    }
}