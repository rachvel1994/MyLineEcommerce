<?php

namespace App\Filament\Resources\AccessoryOrders\Pages;

use App\Filament\Resources\AccessoryOrders\AccessoryOrdersResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use App\Services\AccessoryOrderCashDrawerService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditAccessoryOrders extends EditRecord
{
    use RedirectsBackToPreviousResourcePage;

    protected static string $resource = AccessoryOrdersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function () {
                    app(AccessoryOrderCashDrawerService::class)
                        ->removeOrderPayments($this->record->id);
                }),
        ];
    }

    protected function afterSave(): void
    {
        $accessoryOrderId = $this->record->id;

        DB::afterCommit(function () use ($accessoryOrderId) {
            app(AccessoryOrderCashDrawerService::class)
                ->syncOrderPayments($accessoryOrderId);
        });
    }
}
