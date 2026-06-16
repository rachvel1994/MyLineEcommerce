<?php

namespace App\Filament\Resources\Statuses\Pages;

use App\Filament\Resources\Statuses\StatusResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateStatus extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = StatusResource::class;
}
