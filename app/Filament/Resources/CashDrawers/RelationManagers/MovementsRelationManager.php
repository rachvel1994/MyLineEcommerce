<?php

namespace App\Filament\Resources\CashDrawers\RelationManagers;

use App\Filament\Resources\CashMovements\CashMovementResource;
use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';
    protected static ?string $recordTitleAttribute = 'reason';

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return CashMovementResource::table($table)
            ->recordTitleAttribute('reason');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.cash_history');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __('admin.cash_history');
    }
}
