<?php

namespace App\Observers;

use App\Models\ConsignmentPriceChange;

class ConsignmentPriceChangeObserver
{
    public function created(ConsignmentPriceChange $priceChange): void
    {
//        $this->recalculate($priceChange);
    }

    public function updated(ConsignmentPriceChange $priceChange): void
    {
        $this->recalculate($priceChange);
    }

    public function deleted(ConsignmentPriceChange $priceChange): void
    {
        $this->recalculate($priceChange);
    }

    protected function recalculate(ConsignmentPriceChange $priceChange): void
    {
        $consignment = $priceChange->consignment;

        if (! $consignment) {
            return;
        }

        // TOTAL paid ONLY from price changes
        $paid = $consignment->priceChanges()->sum('paid_amount');

        $subtotal = (float) $consignment->subtotal;

        $paid = min($paid, $subtotal);
        $debt = max($subtotal - $paid, 0);

        $consignment->update([
            'advance_payment' => $paid,
            'debt'            => $debt,
            'is_paid'         => $debt === 0,
        ]);
    }
}