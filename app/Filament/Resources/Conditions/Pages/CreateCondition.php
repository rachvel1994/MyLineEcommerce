<?php

namespace App\Filament\Resources\Conditions\Pages;

use App\Filament\Resources\Conditions\ConditionResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateCondition extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = ConditionResource::class;
}
