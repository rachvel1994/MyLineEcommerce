<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CashMovement extends Model
{
    protected $fillable = [
        'cash_drawer_id',
        'product_id',
        'order_id',
        'direction',
        'amount',
        'reason',
        'user_id',
        'payment_id',
        'accessory_order_payment_id',
        'related_type',
        'related_id',
        'moved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'moved_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function drawer(): BelongsTo
    {
        return $this->belongsTo(CashDrawer::class, 'cash_drawer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function accessoryOrderPayment(): BelongsTo
    {
        return $this->belongsTo(AccessoryOrderPayment::class, 'accessory_order_payment_id');
    }
}
