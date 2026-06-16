<?php

namespace App\Filament\Resources\AccessoryOrders\Pages;

use App\Filament\Resources\AccessoryOrders\AccessoryOrdersResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use App\Services\AccessoryOrderCashDrawerService;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateAccessoryOrders extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;

    protected static string $resource = AccessoryOrdersResource::class;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('admin.sale'));
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label(__('admin.sale_and_sale_new'));
    }

    protected function afterCreate(): void
    {
        $accessoryOrderId = $this->record->id;

        DB::afterCommit(function () use ($accessoryOrderId) {
            app(AccessoryOrderCashDrawerService::class)
                ->syncOrderPayments($accessoryOrderId);
        });
    }
}
