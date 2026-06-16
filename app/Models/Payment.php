<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'name',
        'is_cash_analytic',
        'is_active',
    ];

    public function accessoryOrderPayments(): HasMany
    {
        return $this->hasMany(AccessoryOrderPayment::class, 'payment_id');
    }
}
