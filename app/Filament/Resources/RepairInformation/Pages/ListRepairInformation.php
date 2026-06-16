<?php

namespace App\Filament\Resources\RepairInformation\Pages;

use App\Filament\Resources\RepairInformation\RepairInformationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRepairInformation extends ListRecords
{
    protected static string $resource = RepairInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
