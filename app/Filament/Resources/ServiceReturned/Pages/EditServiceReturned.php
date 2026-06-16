<?php

namespace App\Filament\Resources\ServiceReturned\Pages;

use App\Filament\Resources\ServiceReturned\ServiceReturnedResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\EditRecord;

class EditServiceReturned extends EditRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = ServiceReturnedResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
