<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = ExpenseResource::class;
}
