<?php

namespace App\Filament\Resources\HearAbouts\Pages;

use App\Filament\Resources\HearAbouts\HearAboutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHearAbouts extends ListRecords
{
    protected static string $resource = HearAboutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
