<?php

namespace App\Filament\Resources\ExpenseTypes\Pages;

use App\Filament\Resources\ExpenseTypes\ExpenseTypeResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExpenseType extends EditRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = ExpenseTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
