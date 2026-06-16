<?php

namespace App\Filament\Resources\Consignments\Pages;

use App\Filament\Resources\Consignments\ConsignmentResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateConsignment extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = ConsignmentResource::class;
}
