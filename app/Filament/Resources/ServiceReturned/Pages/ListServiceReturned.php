<?php

namespace App\Filament\Resources\ServiceReturned\Pages;

use App\Filament\Resources\ServiceReturned\ServiceReturnedResource;
use Filament\Resources\Pages\ListRecords;

class ListServiceReturned extends ListRecords
{
    protected static string $resource = ServiceReturnedResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
