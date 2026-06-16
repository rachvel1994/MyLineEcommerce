<?php

namespace App\Services;

use App\Models\AccessoryOrders;
use App\Models\CashDrawer;
use App\Models\CashMovement;
use App\Models\Product;
use App\Models\ProductPayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductPaymentCashDrawerService
{
    private const array CASH_PAYMENT_IDS = [8, 18];

    public function syncProductPayments(int $productId): void
    {
        $product = Product::query()
            ->with([
                'model',
                'payments.payment',
                'payments.product.model',
            ])
            ->find($productId);

        if (! $product) {
            return;
        }

        $this->syncPayments(
            payments: $product->payments,
            productId: $product->id,
            orderId: $product->order_id,
        );
    }

    public function syncPayments(
        Collection $payments,
        ?int $productId = null,
        ?string $orderId = null,
    ): void {
        if (method_exists($payments, 'loadMissing')) {
            $payments->loadMissing([
                'payment',
                'product.model',
            ]);
        }

        if ($payments->isEmpty()) {
            if ($productId) {
                $this->removeObsoleteMovements(
                    productId: $productId,
                    orderId: $orderId,
                    keepMovementIds: [],
                );
            }

            return;
        }

        $payments
            ->groupBy('product_id')
            ->each(function (Collection $productPayments, $groupProductId) use ($orderId): void {
                /** @var ProductPayment|null $firstPayment */
                $firstPayment = $productPayments->first();

                if (! $firstPayment) {
                    return;
                }

                $productId = (int) $groupProductId;

                $stableOrderId = $this->getOrderId(
                    payment: $firstPayment,
                    fallbackOrderId: $orderId,
                );

                $cashGroups = $productPayments
                    ->filter(function (ProductPayment $payment): bool {
                        $isCash = in_array(
                            (int) $payment->payment_id,
                            self::CASH_PAYMENT_IDS,
                            true
                        );

                        $amount = round((float) ($payment->price ?? 0), 2);

                        return $isCash && $amount > 0;
                    })
                    ->groupBy(fn (ProductPayment $payment): int => (int) $payment->payment_id);

                $keepMovementIds = [];

                foreach ($cashGroups as $paymentsByMethod) {
                    /** @var ProductPayment|null $samplePayment */
                    $samplePayment = $paymentsByMethod->first();

                    if (! $samplePayment) {
                        continue;
                    }

                    $amount = round(
                        $paymentsByMethod->sum(
                            fn (ProductPayment $payment): float => (float) ($payment->price ?? 0)
                        ),
                        2
                    );

                    $movementId = $this->syncCashGroup(
                        payment: $samplePayment,
                        amount: $amount,
                        orderId: $stableOrderId,
                    );

                    if ($movementId) {
                        $keepMovementIds[] = $movementId;
                    }
                }

                $this->removeObsoleteMovements(
                    productId: $productId,
                    orderId: $stableOrderId,
                    keepMovementIds: $keepMovementIds,
                );
            });
    }

    public function syncPayment(ProductPayment $payment): void
    {
        $this->syncProductPayments((int) $payment->product_id);
    }

    private function syncCashGroup(
        ProductPayment $payment,
        float $amount,
        ?string $orderId,
    ): ?int {
        return DB::transaction(function () use ($payment, $amount, $orderId): ?int {
            $payment->loadMissing([
                'payment',
                'product.model',
            ]);

            $amount = round($amount, 2);

            if ($amount <= 0) {
                return null;
            }

            $candidates = $this->findCandidateMovements($payment, $orderId);

            if ($candidates->isNotEmpty()) {
                $openCandidates = $candidates->filter(
                    fn (CashMovement $movement): bool => $movement->drawer?->closed_at === null
                );

                /*
                 * Existing movement exists, but only in closed drawer.
                 * Do not create a new one in current drawer.
                 */
                if ($openCandidates->isEmpty()) {
                    return (int) $candidates->first()->id;
                }

                /** @var CashMovement $movement */
                $movement = $openCandidates->sortBy('id')->first();

                $drawer = CashDrawer::query()
                    ->whereKey($movement->cash_drawer_id)
                    ->lockForUpdate()
                    ->first();

                if (! $drawer || $drawer->closed_at !== null) {
                    return (int) $movement->id;
                }

                /*
                 * Merge duplicate open movements for same product/order/payment.
                 * Keep first movement, delete duplicate movements and subtract their amounts.
                 */
                $duplicates = $openCandidates
                    ->where('id', '!=', $movement->id)
                    ->values();

                foreach ($duplicates as $duplicate) {
                    $this->deleteMovementIfDrawerOpen($duplicate);
                }

                $drawer = CashDrawer::query()
                    ->whereKey($movement->cash_drawer_id)
                    ->lockForUpdate()
                    ->first();

                if (! $drawer || $drawer->closed_at !== null) {
                    return (int) $movement->id;
                }

                $reason = $this->buildReason(
                    payment: $payment,
                    amount: $amount,
                    orderId: $orderId,
                );

                $oldAmount = round((float) $movement->amount, 2);
                $diff = round($amount - $oldAmount, 2);

                if ($diff != 0.0) {
                    $this->applyDrawerDiff($drawer, $diff);
                }

                $movement->forceFill([
                    'direction' => 'in',
                    'product_id' => $payment->product_id,
                    'order_id' => $orderId,
                    'amount' => $amount,
                    'reason' => $reason,
                    'payment_id' => $payment->payment_id,
                    'user_id' => auth()->id(),
                    'moved_at' => $payment->created_at ?? now(),
                ]);

                $movement->related()->associate($payment);
                $movement->save();

                return (int) $movement->id;
            }

            /*
             * No old movement found. Create only in open drawer.
             */
            $drawer = CashDrawer::query()
                ->whereNull('closed_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $drawer) {
                return null;
            }

            $reason = $this->buildReason(
                payment: $payment,
                amount: $amount,
                orderId: $orderId,
            );

            $this->applyDrawerDiff($drawer, $amount);

            $movement = $drawer->movements()->create([
                'direction' => 'in',
                'product_id' => $payment->product_id,
                'order_id' => $orderId,
                'amount' => $amount,
                'reason' => $reason,
                'payment_id' => $payment->payment_id,
                'user_id' => auth()->id(),
                'moved_at' => $payment->created_at ?? now(),
            ]);

            $movement->related()->associate($payment);
            $movement->save();

            return (int) $movement->id;
        });
    }

    private function findCandidateMovements(ProductPayment $payment, ?string $orderId): Collection
    {
        $types = $this->productPaymentMorphTypes();

        return CashMovement::query()
            ->with('drawer')
            ->where('direction', 'in')
            ->where('product_id', $payment->product_id)
            ->where('payment_id', $payment->payment_id)
            ->whereNull('accessory_order_payment_id')
            ->where(function ($query) use ($payment, $orderId, $types) {
                /*
                 * Current relation.
                 */
                $query->where(function ($query) use ($payment, $types) {
                    $query
                        ->whereIn('related_type', $types)
                        ->where('related_id', $payment->id);
                });

                /*
                 * Stable search by order_id column.
                 */
                if ($orderId) {
                    $query->orWhere(function ($query) use ($orderId, $types) {
                        $query
                            ->whereIn('related_type', $types)
                            ->where('order_id', $orderId);
                    });
                }

                /*
                 * Fallback for old movements where order_id column was empty,
                 * but order ID exists inside reason text.
                 */
                if ($orderId) {
                    $query->orWhere(function ($query) use ($orderId) {
                        $query
                            ->whereNull('order_id')
                            ->where('reason', 'like', $this->orderIdLike($orderId));
                    });
                }
            })
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    private function removeObsoleteMovements(
        int $productId,
        ?string $orderId,
        array $keepMovementIds,
    ): void {
        DB::transaction(function () use ($productId, $orderId, $keepMovementIds): void {
            $types = $this->productPaymentMorphTypes();

            $movements = CashMovement::query()
                ->with('drawer')
                ->where('direction', 'in')
                ->where('product_id', $productId)
                ->whereIn('payment_id', self::CASH_PAYMENT_IDS)
                ->whereNull('accessory_order_payment_id')
                ->where(function ($query) use ($types, $orderId) {
                    $query->whereIn('related_type', $types);

                    if ($orderId) {
                        $query->orWhere('order_id', $orderId);
                        $query->orWhere('reason', 'like', $this->orderIdLike($orderId));
                    }
                })
                ->lockForUpdate()
                ->get();

            foreach ($movements as $movement) {
                if (in_array((int) $movement->id, array_map('intval', $keepMovementIds), true)) {
                    continue;
                }

                $this->deleteMovementIfDrawerOpen($movement);
            }
        });
    }

    private function deleteMovementIfDrawerOpen(CashMovement $movement): void
    {
        $drawer = CashDrawer::query()
            ->whereKey($movement->cash_drawer_id)
            ->lockForUpdate()
            ->first();

        if (! $drawer || $drawer->closed_at !== null) {
            return;
        }

        $this->applyDrawerDiff($drawer, -1 * (float) $movement->amount);

        $movement->delete();
    }

    private function applyDrawerDiff(CashDrawer $drawer, float $diff): void
    {
        $drawer->current_balance = round(
            (float) $drawer->current_balance + $diff,
            2
        );

        if ((float) $drawer->current_balance < 0) {
            $drawer->current_balance = 0;
        }

        $drawer->save();
    }

    private function buildReason(
        ProductPayment $payment,
        float $amount,
        ?string $orderId,
    ): string {
        $payment->loadMissing([
            'payment',
            'product.model',
        ]);

        $product = $payment->product;

        $productName =
            $product?->model?->name
            ?? $product?->name
            ?? null;

        $orderDisplayId =
            $orderId
            ?? $product?->order_id
            ?? $payment->product_id;

        $accessoriesNames = $this->getAccessoriesNames(
            productId: $payment->product_id ? (int) $payment->product_id : null,
            orderId: $orderDisplayId ? (string) $orderDisplayId : null,
        );

        $parts = array_filter([
            $productName
                ? "პროდუქცია: {$productName}"
                : null,

            $accessoriesNames
                ? "აქსესუარები: {$accessoriesNames}"
                : null,

            "თანხა: {$amount}",

            "შეკვეთის ID: {$orderDisplayId}",
        ]);

        $paymentName = $payment->payment?->name
            ?? __('admin.unknown');

        return $paymentName . ' — ' . implode(' | ', $parts);
    }

    private function getAccessoriesNames(?int $productId, ?string $orderId): ?string
    {
        if (! $productId && ! $orderId) {
            return null;
        }

        $orders = AccessoryOrders::query()
            ->with([
                'items.accessory',
            ])
            ->when(
                $productId,
                fn ($query) => $query->where('product_id', $productId)
            )
            ->when(
                $orderId,
                fn ($query) => $query->where('order_id', $orderId)
            )
            ->get();

        $names = $orders
            ->flatMap(fn (AccessoryOrders $order) => $order->items)
            ->pluck('accessory.name')
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        return $names !== '' ? $names : null;
    }

    private function getOrderId(
        ProductPayment $payment,
        ?string $fallbackOrderId = null,
    ): ?string {
        $payment->loadMissing('product');

        $orderId =
            $payment->order_id
            ?? $payment->product?->order_id
            ?? $fallbackOrderId
            ?? null;

        return $orderId !== null ? (string) $orderId : null;
    }

    private function orderIdLike(string $orderId): string
    {
        return '%შეკვეთის ID: ' . addcslashes($orderId, '\\%_') . '%';
    }

    private function productPaymentMorphTypes(): array
    {
        return array_values(array_unique([
            ProductPayment::class,
            (new ProductPayment())->getMorphClass(),
        ]));
    }
}