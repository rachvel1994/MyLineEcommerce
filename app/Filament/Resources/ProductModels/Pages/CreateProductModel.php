<?php

namespace App\Filament\Resources\ProductModels\Pages;

use App\Filament\Resources\ProductModels\ProductModelResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateProductModel extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = ProductModelResource::class;
}
