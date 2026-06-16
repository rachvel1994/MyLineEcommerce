<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use App\Services\ProductPaymentCashDrawerService;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use RedirectsBackToPreviousResourcePage;

    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['repair_information_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getState();

        if (!empty($data['repair_information_id'])) {
            $this->record->information()->sync($data['repair_information_id']);
        }

        $this->record->load([
            'payments.payment',
            'payments.product.model',
        ]);

        app(ProductPaymentCashDrawerService::class)
            ->syncPayments($this->record->payments);
    }
}
