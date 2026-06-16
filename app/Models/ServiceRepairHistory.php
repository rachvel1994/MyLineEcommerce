<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRepairHistory extends Model
{
    protected $table = 'service_repair_histories';

    protected $fillable = [
        'service_id',
        'product_id',
        'user_id',
        'old_status_id',
        'new_status_id',
        'repair_price',
        'price_delta',
        'comment',
        'is_paid',
    ];

    protected $casts = [
        'repair_price' => 'decimal:2',
        'price_delta' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ServiceProduct::class, 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function oldStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'old_status_id');
    }

    public function newStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'new_status_id');
    }
}