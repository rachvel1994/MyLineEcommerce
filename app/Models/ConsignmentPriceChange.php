<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentPriceChange extends Model
{
    protected $table = 'consignment_price_changes';

    protected $fillable = [
        'consignment_id',
        'paid_amount',
        'payment_id',
        'debt',
        'total',
    ];

    public function consignment(): BelongsTo
    {
        return $this->belongsTo(Consignment::class, 'consignment_id', 'id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
