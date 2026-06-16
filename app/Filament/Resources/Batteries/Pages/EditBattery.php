<?php

namespace App\Filament\Resources\Batteries\Pages;

use App\Filament\Resources\Batteries\BatteryResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBattery extends EditRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = BatteryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
