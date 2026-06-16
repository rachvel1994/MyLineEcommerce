<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};

class Service extends Model
{
    protected $fillable = [
        'technic_id',
        'created_by',
        'subtotal',
        'advance_payment',
        'debt',
        'is_paid',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'subtotal' => 'decimal:2',
        'advance_payment' => 'decimal:2',
        'debt' => 'decimal:2',
    ];

    public function technic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technic_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            ServiceProduct::class,
            'service_product',
            'service_id',
            'product_id'
        )
            ->withPivot(['qty', 'unit_price'])
            ->withTimestamps();
    }

    public function serviceRepairHistories(): HasMany
    {
        return $this->hasMany(ServiceRepairHistory::class);
    }

    public function recalculateTotals(bool $save = true): void
    {
        $repairTotal = (float) $this->serviceRepairHistories()->sum('repair_price');

        $subtotal = round($repairTotal, 2);

        $paid = min((float) $this->advance_payment, $subtotal);

        $debt = round(max(0, $subtotal - $paid), 2);

        $this->forceFill([
            'subtotal' => $subtotal,
            'advance_payment' => $paid,
            'debt' => $debt,
            'is_paid' => $debt <= 0,
        ]);

        if ($save) {
            $this->saveQuietly();
        }
    }
}
