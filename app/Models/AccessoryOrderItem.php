<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessoryOrderItem extends Model
{
    protected $fillable = [
        'accessory_order_id',
        'accessory_id',
        'quantity',
        'order_id',
        'price',
        'total_price',
        'is_gift',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(AccessoryOrders::class, 'accessory_order_id');
    }

    public function accessory(): BelongsTo
    {
        return $this->belongsTo(Accessory::class);
    }
}
