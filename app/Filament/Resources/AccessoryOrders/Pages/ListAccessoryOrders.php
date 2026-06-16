<?php

namespace App\Filament\Resources\AccessoryOrders\Pages;

use App\Filament\Resources\AccessoryOrders\AccessoryOrdersResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\SellerMonthlyStatsWidget;
use Filament\Support\Enums\Width;

class ListAccessoryOrders extends ListRecords
{
    protected static string $resource = AccessoryOrdersResource::class;

    protected Width | string | null $maxContentWidth = 'full';
    
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('admin.accessory_order')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SellerMonthlyStatsWidget::class,
        ];
    }
}
