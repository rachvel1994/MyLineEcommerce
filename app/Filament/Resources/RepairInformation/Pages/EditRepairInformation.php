<?php

namespace App\Filament\Resources\RepairInformation\Pages;

use App\Filament\Resources\RepairInformation\RepairInformationResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRepairInformation extends EditRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = RepairInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
