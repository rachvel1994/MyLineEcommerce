<?php

namespace App\Filament\Resources\Consignments\Pages;

use App\Filament\Resources\Consignments\ConsignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListConsignments extends ListRecords
{
    protected static string $resource = ConsignmentResource::class;

    protected Width|string|null $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
