<?php

namespace App\Filament\Resources\Guarantees\Pages;

use App\Filament\Resources\Guarantees\GuaranteeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGuarantees extends ListRecords
{
    protected static string $resource = GuaranteeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
