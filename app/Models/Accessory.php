<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accessory extends Model
{
    protected $fillable = [
        'name',
        'quantity',
        'price',
        'sale_price',
        'retail_price',
        'category_id',
    ];

    public function orders()
    {
        return $this->belongsToMany(AccessoryOrders::class, 'accessory_order_items', 'accessory_id', 'order_id')
            ->withPivot(['quantity', 'total_price', 'order_id'])
            ->withTimestamps();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function consignments()
    {
        return $this->belongsToMany(
            Consignment::class,
            'consignment_accessory',
            'accessory_id',
            'consignment_id'
        )->withPivot(['qty', 'unit_price', 'line_total'])
            ->withTimestamps();
    }

    public function recalculateTotals(bool $save = true): void
    {
        $prodTotal = (float)$this->products()
            ->selectRaw('COALESCE(SUM(line_total),0) as t')
            ->value('t');

        $accTotal = (float)$this->accessories()
            ->selectRaw('COALESCE(SUM(line_total),0) as t')
            ->value('t');

        $subtotal = round($prodTotal + $accTotal, 2);
        $paid = round((float)($this->paid_amount ?? 0), 2);
        $paid = min($paid, $subtotal);
        $debt = round(max(0, $subtotal - $paid), 2);
        $isPaid = $debt <= 0.0;

        $this->forceFill([
            'subtotal' => $subtotal,
            'paid_amount' => $paid,
            'debt' => $debt,
            'is_paid' => $isPaid,
        ]);

        if ($save) {
            $this->saveQuietly();
        }
    }
}
