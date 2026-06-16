<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = ServiceResource::class;
}
