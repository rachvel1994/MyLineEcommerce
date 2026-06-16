<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessoryOrderPayment extends Model
{
    protected $fillable = [
        'accessory_order_id',
        'payment_id',
        'order_id',
        'amount',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(AccessoryOrders::class, 'accessory_order_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
