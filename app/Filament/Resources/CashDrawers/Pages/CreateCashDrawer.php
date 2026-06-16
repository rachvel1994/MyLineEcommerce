<?php

namespace App\Filament\Resources\CashDrawers\Pages;

use App\Filament\Resources\CashDrawers\CashDrawerResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Resources\Pages\CreateRecord;

class CreateCashDrawer extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;
    
    protected static string $resource = CashDrawerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_at'] = now();

        return $data;
    }
}
