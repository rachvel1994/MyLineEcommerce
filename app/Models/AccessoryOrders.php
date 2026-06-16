<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessoryOrders extends Model
{
    protected $fillable = [
        'mobile',
        'order_id',
        'name',
        'product_id',
        'buyer_id',
        'seller_id',
        'delivery_id',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(AccessoryOrderItem::class, 'accessory_order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AccessoryOrderPayment::class, 'accessory_order_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

