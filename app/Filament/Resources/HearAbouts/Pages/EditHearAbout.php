<?php

namespace App\Filament\Resources\HearAbouts\Pages;

use App\Filament\Resources\HearAbouts\HearAboutResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHearAbout extends EditRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = HearAboutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
