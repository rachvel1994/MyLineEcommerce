<?php

namespace App\Filament\Resources\HearAbouts\Pages;

use App\Filament\Resources\HearAbouts\HearAboutResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateHearAbout extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = HearAboutResource::class;
}
