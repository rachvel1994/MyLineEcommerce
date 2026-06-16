<?php

namespace App\Observers;

use App\Models\AccessoryOrders;
use App\Models\Product;
use App\Models\ProductPayment;
use App\Models\User;
use App\Services\ProductPaymentCashDrawerService;
use Illuminate\Support\Facades\DB;

class ProductObserver
{
    public function created(Product $product): void
    {
        if (! empty($product->service_comment)) {
            $product->comments()->create([
                'body' => $product->service_comment,
                'author_type' => User::class,
                'author_id' => auth()->id(),
            ]);
        }
    }

    public function updated(Product $product): void
    {
        /*
         * Only when leaving SOLD status.
         */
        if (
            ! $product->wasChanged('status_id')
            || (int) $product->getOriginal('status_id') !== 4
        ) {
            return;
        }

        DB::transaction(function () use ($product) {

            /*
             * Remove/open movements correctly.
             * Closed drawer history stays untouched.
             */
            app(ProductPaymentCashDrawerService::class)
                ->syncPayments(
                    payments: collect(),
                    productId: $product->id,
                    orderId: $product->order_id,
                );

            /*
             * Delete accessory orders linked to this order.
             */
            if ($product->order_id) {
                AccessoryOrders::query()
                    ->where('order_id', $product->order_id)
                    ->delete();
            }

            /*
             * Delete product payments.
             */
            ProductPayment::query()
                ->where('product_id', $product->id)
                ->delete();
        });
    }
}
