<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consignment extends Model
{
    protected $fillable = [
        'customer_id', 'created_by', 'subtotal', 'advance_payment', 'debt', 'is_paid',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'subtotal' => 'decimal:2',
        'advance_payment' => 'decimal:2',
        'debt' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'consignment_product')
            ->withPivot(['qty', 'unit_price', 'line_total'])
            ->withTimestamps();
    }

    public function priceChanges(): HasMany
    {
        return $this->hasMany(ConsignmentPriceChange::class, 'consignment_id', 'id');
    }

    public function accessories()
    {
        return $this->belongsToMany(Accessory::class, 'consignment_accessory')
            ->withPivot(['qty', 'unit_price', 'line_total'])
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
        $paid = round((float)($this->advance_payment ?? 0), 2);
        $paid = min($paid, $subtotal);
        $debt = round(max(0, $subtotal - $paid), 2);
        $isPaid = $debt <= 0.0;

        $this->forceFill([
            'subtotal' => $subtotal,
            'advance_payment' => $paid,
            'debt' => $debt,
            'is_paid' => $isPaid,
        ]);

        if ($save) {
            $this->saveQuietly();
        }
    }
}
