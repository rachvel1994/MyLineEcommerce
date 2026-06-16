<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateBranch extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = BranchResource::class;
}
