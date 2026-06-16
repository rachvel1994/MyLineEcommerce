<?php

namespace App\Filament\Resources\Accessories\Pages;

use App\Filament\Resources\Accessories\AccessoryResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateAccessory extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = AccessoryResource::class;
}
