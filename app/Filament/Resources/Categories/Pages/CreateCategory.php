<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = CategoryResource::class;
}
