<?php

namespace App\Filament\Resources\Guarantees\Pages;

use App\Filament\Resources\Guarantees\GuaranteeResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateGuarantee extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = GuaranteeResource::class;
}
