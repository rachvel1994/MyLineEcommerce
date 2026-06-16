<?php

namespace App\Filament\Resources\CashDrawers\Pages;

use App\Filament\Resources\CashDrawers\CashDrawerResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditCashDrawer extends EditRecord
{
    use RedirectsBackToPreviousResourcePage;

    protected static string $resource = CashDrawerResource::class;
    
    protected Width|string|null $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
