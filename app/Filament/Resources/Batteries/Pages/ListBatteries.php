<?php

namespace App\Filament\Resources\Batteries\Pages;

use App\Filament\Resources\Batteries\BatteryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBatteries extends ListRecords
{
    protected static string $resource = BatteryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
