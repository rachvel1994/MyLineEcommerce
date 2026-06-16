<?php

namespace App\Filament\Resources\ProductModels\Pages;

use App\Filament\Resources\ProductModels\ProductModelResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductModel extends EditRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = ProductModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
