<?php

namespace App\Services;

use App\Models\AccessoryOrderPayment;
use App\Models\AccessoryOrders;
use App\Models\CashDrawer;
use App\Models\CashMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccessoryOrderCashDrawerService
{
    private const array CASH_PAYMENT_IDS = [8, 18];

    public function syncPaymentById(int $paymentId): void
    {
        $payment = AccessoryOrderPayment::query()
            ->with([
                'order.product.model',
                'order.items.accessory',
                'payment',
            ])
            ->find($paymentId);

        if (! $payment) {
            return;
        }

        $this->syncPayment($payment);
    }

    public function syncProductAccessoryOrders(int $productId): void
    {
        AccessoryOrders::query()
            ->with([
                'product.model',
                'items.accessory',
                'payments.payment',
            ])
            ->where('product_id', $productId)
            ->get()
            ->each(fn (AccessoryOrders $order) => $this->syncOrder($order));
    }

    public function syncOrderPayments(int $accessoryOrderId): void
    {
        $order = AccessoryOrders::query()
            ->with([
                'product.model',
                'items.accessory',
                'payments.payment',
            ])
            ->find($accessoryOrderId);

        if (! $order) {
            return;
        }

        $this->syncOrder($order);
    }

    public function syncOrder(AccessoryOrders $order): void
    {
        $order->loadMissing([
            'product.model',
            'items.accessory',
            'payments.payment',
        ]);

        $order->payments->each(function (AccessoryOrderPayment $payment) use ($order) {
            $payment->setRelation('order', $order);
        });

        $this->syncPayments(
            payments: $order->payments,
            accessoryOrderId: (int) $order->id,
            orderDisplayId: $this->getOrderDisplayIdFromOrder($order),
            productId: $order->product_id ? (int) $order->product_id : null,
            hasItems: $order->items->isNotEmpty(),
        );
    }

    public function syncPayment(AccessoryOrderPayment $payment): void
    {
        $payment->loadMissing([
            'order.product.model',
            'order.items.accessory',
            'payment',
        ]);

        $order = $payment->order;

        $this->syncPayments(
            payments: collect([$payment]),
            accessoryOrderId: $payment->accessory_order_id ? (int) $payment->accessory_order_id : null,
            orderDisplayId: $this->getOrderDisplayId($payment),
            productId: $order?->product?->id ? (int) $order->product->id : null,
            hasItems: $order?->items?->isNotEmpty(),
        );
    }

    public function syncPayments(
        Collection $payments,
        ?int $accessoryOrderId = null,
        ?string $orderDisplayId = null,
        ?int $productId = null,
        ?bool $hasItems = null,
    ): void {
        if (method_exists($payments, 'loadMissing')) {
            $payments->loadMissing([
                'order.product.model',
                'order.items.accessory',
                'payment',
            ]);
        }

        $firstPayment = $payments->first();

        if ($firstPayment instanceof AccessoryOrderPayment) {
            $accessoryOrderId ??= $firstPayment->accessory_order_id
                ? (int) $firstPayment->accessory_order_id
                : null;

            $orderDisplayId ??= $this->getOrderDisplayId($firstPayment);

            $productId ??= $firstPayment->order?->product?->id
                ? (int) $firstPayment->order->product->id
                : null;

            $hasItems ??= $firstPayment->order?->items?->isNotEmpty();
        }

        /*
         * If no payments or no accessories, remove old accessory movements
         * from open drawer only.
         */
        if ($payments->isEmpty() || $hasItems === false) {
            $this->removeObsoleteMovements(
                accessoryOrderId: $accessoryOrderId,
                orderDisplayId: $orderDisplayId,
                productId: $productId,
                keepMovementIds: [],
            );

            return;
        }

        $cashGroups = $payments
            ->filter(function (AccessoryOrderPayment $payment) {
                $isCash = in_array(
                    (int) $payment->payment_id,
                    self::CASH_PAYMENT_IDS,
                    true
                );

                $amount = $this->getAmount($payment);

                return $isCash && $amount > 0;
            })
            ->groupBy(fn (AccessoryOrderPayment $payment) => (int) $payment->payment_id);

        $keepMovementIds = [];

        foreach ($cashGroups as $paymentId => $paymentsByMethod) {
            /** @var AccessoryOrderPayment|null $samplePayment */
            $samplePayment = $paymentsByMethod->first();

            if (! $samplePayment) {
                continue;
            }

            $stableOrderId = $this->getOrderDisplayId($samplePayment, $orderDisplayId);

            $amount = round(
                $paymentsByMethod->sum(fn (AccessoryOrderPayment $payment) => $this->getAmount($payment)),
                2
            );

            $movementId = $this->syncCashGroup(
                payment: $samplePayment,
                amount: $amount,
                orderDisplayId: $stableOrderId,
            );

            if ($movementId) {
                $keepMovementIds[] = $movementId;
            }
        }

        /*
         * If payment was changed from cash to non-cash,
         * or deleted from repeater, remove old open movement.
         */
        $this->removeObsoleteMovements(
            accessoryOrderId: $accessoryOrderId,
            orderDisplayId: $orderDisplayId,
            productId: $productId,
            keepMovementIds: $keepMovementIds,
        );
    }

    private function syncCashGroup(
        AccessoryOrderPayment $payment,
        float $amount,
        ?string $orderDisplayId,
    ): ?int {
        return DB::transaction(function () use ($payment, $amount, $orderDisplayId) {
            $payment->loadMissing([
                'order.product.model',
                'order.items.accessory',
                'payment',
            ]);

            $amount = round($amount, 2);

            if ($amount <= 0) {
                return null;
            }

            $candidates = $this->findCandidateMovements($payment, $orderDisplayId);

            /*
             * If old movement exists only in closed drawer,
             * do not create a new movement in current drawer.
             */
            if ($candidates->isNotEmpty()) {
                $openCandidates = $candidates->filter(
                    fn (CashMovement $movement) => $movement->drawer?->closed_at === null
                );

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
                 * Merge duplicate open movements.
                 * Keep first, subtract duplicate amounts, delete duplicates.
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

                $reason = $this->buildReason($payment, $amount, $orderDisplayId);

                $oldAmount = round((float) $movement->amount, 2);
                $diff = round($amount - $oldAmount, 2);

                if ($diff != 0.0) {
                    $this->applyDrawerDiff($drawer, $diff);
                }

                $movement->forceFill([
                    'direction' => 'in',
                    'product_id' => $payment->order?->product?->id,
                    'amount' => $amount,
                    'reason' => $reason,
                    'payment_id' => $payment->payment_id,
                    'user_id' => auth()->id() ?? $payment->user_id ?? null,
                    'accessory_order_payment_id' => $payment->id,
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

            $reason = $this->buildReason($payment, $amount, $orderDisplayId);

            $this->applyDrawerDiff($drawer, $amount);

            $movement = $drawer->movements()->create([
                'direction' => 'in',
                'product_id' => $payment->order?->product?->id,
                'amount' => $amount,
                'reason' => $reason,
                'payment_id' => $payment->payment_id,
                'user_id' => auth()->id() ?? $payment->user_id ?? null,
                'accessory_order_payment_id' => $payment->id,
                'moved_at' => $payment->created_at ?? now(),
            ]);

            $movement->related()->associate($payment);
            $movement->save();

            return (int) $movement->id;
        });
    }

    private function findCandidateMovements(
        AccessoryOrderPayment $payment,
        ?string $orderDisplayId,
    ): Collection {
        $payment->loadMissing([
            'order.product',
        ]);

        $productId = $payment->order?->product?->id;
        $types = $this->accessoryPaymentMorphTypes();

        return CashMovement::query()
            ->with('drawer')
            ->where('direction', 'in')
            ->where('payment_id', $payment->payment_id)
            ->when(
                $productId,
                fn ($query) => $query->where('product_id', $productId)
            )
            ->where(function ($query) use ($types) {
                $query
                    ->whereNull('related_type')
                    ->orWhereIn('related_type', $types);
            })
            ->where(function ($query) use ($payment, $orderDisplayId) {
                /*
                 * Exact current payment movement.
                 */
                $query->where('accessory_order_payment_id', $payment->id);

                /*
                 * Fallback for deleted/recreated payment rows.
                 * Finds old movement by order ID inside reason.
                 */
                if ($orderDisplayId) {
                    $query->orWhere(function ($query) use ($orderDisplayId) {
                        $query
                            ->where('reason', 'like', $this->orderIdLike($orderDisplayId))
                            ->where('reason', 'like', $this->accessoriesLike());
                    });
                }
            })
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    private function removeObsoleteMovements(
        ?int $accessoryOrderId,
        ?string $orderDisplayId,
        ?int $productId,
        array $keepMovementIds,
    ): void {
        DB::transaction(function () use ($accessoryOrderId, $orderDisplayId, $productId, $keepMovementIds) {
            $types = $this->accessoryPaymentMorphTypes();

            $movements = CashMovement::query()
                ->with('drawer')
                ->where('direction', 'in')
                ->whereIn('payment_id', self::CASH_PAYMENT_IDS)
                ->when(
                    $productId,
                    fn ($query) => $query->where('product_id', $productId)
                )
                ->where(function ($query) use ($types) {
                    $query
                        ->whereNull('related_type')
                        ->orWhereIn('related_type', $types);
                })
                ->where(function ($query) use ($accessoryOrderId, $orderDisplayId) {
                    /*
                     * Start with false condition, then OR real conditions.
                     */
                    $query->whereRaw('1 = 0');

                    if ($accessoryOrderId) {
                        $query->orWhereIn(
                            'accessory_order_payment_id',
                            AccessoryOrderPayment::query()
                                ->select('id')
                                ->where('accessory_order_id', $accessoryOrderId)
                        );
                    }

                    if ($orderDisplayId) {
                        $query->orWhere(function ($query) use ($orderDisplayId) {
                            $query
                                ->where('reason', 'like', $this->orderIdLike($orderDisplayId))
                                ->where('reason', 'like', $this->accessoriesLike());
                        });
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

    public function removeOrderPayments(int $accessoryOrderId): void
    {
        $order = AccessoryOrders::query()
            ->with([
                'product',
            ])
            ->find($accessoryOrderId);

        if (! $order) {
            return;
        }

        $this->removeObsoleteMovements(
            accessoryOrderId: (int) $order->id,
            orderDisplayId: $this->getOrderDisplayIdFromOrder($order),
            productId: $order->product_id ? (int) $order->product_id : null,
            keepMovementIds: [],
        );
    }

    public function removePaymentMovementByPaymentId(int $paymentId): void
    {
        DB::transaction(function () use ($paymentId) {
            $movement = CashMovement::query()
                ->where('accessory_order_payment_id', $paymentId)
                ->lockForUpdate()
                ->first();

            if (! $movement) {
                return;
            }

            $this->deleteMovementIfDrawerOpen($movement);
        });
    }

    public function removePaymentMovement(AccessoryOrderPayment $payment): void
    {
        $this->removePaymentMovementByPaymentId((int) $payment->id);
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
        AccessoryOrderPayment $payment,
        float $amount,
        ?string $orderDisplayId,
    ): string {
        $payment->loadMissing([
            'order.product.model',
            'order.items.accessory',
            'payment',
        ]);

        $order = $payment->order;

        $productName =
            $order?->product?->model?->name
            ?? $order?->product?->name
            ?? null;

        $accessoriesNames = $order?->items?->isEmpty()
            ? null
            : $order->items
                ->pluck('accessory.name')
                ->filter()
                ->unique()
                ->implode(', ');

        $displayId =
            $orderDisplayId
            ?? $order?->order_id
            ?? $payment->order_id
            ?? $payment->accessory_order_id;

        $parts = array_filter([
            $productName ? "პროდუქცია: {$productName}" : null,
            $accessoriesNames ? "აქსესუარები: {$accessoriesNames}" : null,
            "თანხა: {$amount}",
            "შეკვეთის ID: {$displayId}",
        ]);

        $paymentName = $payment->payment?->name
            ?? __('admin.unknown');

        return $paymentName . ' — ' . implode(' | ', $parts);
    }

    private function getAmount(AccessoryOrderPayment $payment): float
    {
        return round((float) ($payment->amount ?? $payment->price ?? 0), 2);
    }

    private function getOrderDisplayId(
        AccessoryOrderPayment $payment,
        ?string $fallback = null,
    ): ?string {
        $payment->loadMissing('order');

        $orderId =
            $payment->order?->order_id
            ?? $payment->order_id
            ?? $payment->accessory_order_id
            ?? $fallback
            ?? null;

        return $orderId !== null ? (string) $orderId : null;
    }

    private function getOrderDisplayIdFromOrder(AccessoryOrders $order): ?string
    {
        $orderId =
            $order->order_id
            ?? $order->id
            ?? null;

        return $orderId !== null ? (string) $orderId : null;
    }

    private function orderIdLike(string $orderDisplayId): string
    {
        return '%შეკვეთის ID: ' . addcslashes($orderDisplayId, '\\%_') . '%';
    }

    private function accessoriesLike(): string
    {
        return '%აქსესუარები:%';
    }

    private function accessoryPaymentMorphTypes(): array
    {
        return array_values(array_unique([
            AccessoryOrderPayment::class,
            (new AccessoryOrderPayment())->getMorphClass(),
        ]));
    }
}
