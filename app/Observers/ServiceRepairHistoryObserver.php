<?php

namespace App\Observers;

use App\Models\ServiceRepairHistory;
use Illuminate\Support\Facades\DB;

class ServiceRepairHistoryObserver
{
    public function created(ServiceRepairHistory $history): void
    {
        $history->service?->recalculateTotals();
    }

    public function updated(ServiceRepairHistory $history): void
    {
        $history->service?->recalculateTotals();
    }

    public function deleted(ServiceRepairHistory $history): void
    {
        $service = $history->service;
        $product = $history->product;

        if ($service) {
            $service->recalculateTotals();
        }

        if ($product && $service) {
            $product->decrement('price', $history->repair_price);
            $service->products()->updateExistingPivot($product->id, [
                'unit_price' => DB::raw('unit_price - ' . $history->repair_price)
            ]);
        }
    }
}
