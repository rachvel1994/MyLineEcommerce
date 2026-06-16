<?php

namespace App\Filament\Resources\Batteries\Pages;

use App\Filament\Resources\Batteries\BatteryResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateBattery extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = BatteryResource::class;
}
