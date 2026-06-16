<?php

namespace App\Filament\Resources\ExpenseTypes\Pages;

use App\Filament\Resources\ExpenseTypes\ExpenseTypeResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateExpenseType extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = ExpenseTypeResource::class;
}
