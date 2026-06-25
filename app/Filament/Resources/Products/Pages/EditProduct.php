<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Traits\RedirectsBackToPreviousResourcePage;
use App\Services\ProductPaymentCashDrawerService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use RedirectsBackToPreviousResourcePage;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                    ->label(__('admin.redirect_back'))
                ->url(fn () => url()->previous()),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->record->information()->sync($data['repair_information_id'] ?? []);

        unset($data['information'], $data['repair_information_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->load([
            'payments.payment',
            'payments.product.model',
        ]);

        app(ProductPaymentCashDrawerService::class)
            ->syncPayments(
                payments: $this->record->payments,
                productId: $this->record->id,
                orderId: $this->record->order_id,
            );
    }
}
