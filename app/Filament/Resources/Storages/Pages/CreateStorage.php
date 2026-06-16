<?php

namespace App\Filament\Resources\Storages\Pages;

use App\Filament\Resources\Storages\StorageResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateStorage extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = StorageResource::class;
}
