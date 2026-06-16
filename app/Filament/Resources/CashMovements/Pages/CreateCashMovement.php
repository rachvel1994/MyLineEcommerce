<?php

namespace App\Filament\Resources\CashMovements\Pages;

use App\Filament\Resources\CashMovements\CashMovementResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateCashMovement extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = CashMovementResource::class;
}
