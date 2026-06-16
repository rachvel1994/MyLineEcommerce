<?php

namespace App\Filament\Resources\RepairInformation\Pages;

use App\Filament\Resources\RepairInformation\RepairInformationResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateRepairInformation extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = RepairInformationResource::class;
}
