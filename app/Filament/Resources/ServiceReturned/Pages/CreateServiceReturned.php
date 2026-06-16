<?php

namespace App\Filament\Resources\ServiceReturned\Pages;

use App\Filament\Resources\ServiceReturned\ServiceReturnedResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceReturned extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = ServiceReturnedResource::class;
}
