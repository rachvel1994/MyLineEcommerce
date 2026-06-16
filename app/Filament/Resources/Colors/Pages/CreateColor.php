<?php

namespace App\Filament\Resources\Colors\Pages;

use App\Filament\Resources\Colors\ColorResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateColor extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = ColorResource::class;
}
