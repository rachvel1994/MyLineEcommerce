<?php

namespace App\Filament\Resources\CashDrawers\Pages;

use App\Filament\Resources\CashDrawers\CashDrawerResource;
use App\Filament\Widgets\CashDrawerWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListCashDrawers extends ListRecords
{
    protected static string $resource = CashDrawerResource::class;
	
    protected Width|string|null $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label(__('admin.new_day_opening')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CashDrawerWidget::class,
        ];
    }
}
