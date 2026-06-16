<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $fillable = [
        'name',
        'color',
        'sort_order',
        'image',
        'sort_order',
        'show_in_product',
        'is_active',
    ];


    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function newStatusHistories(): HasMany
    {
        return $this->hasMany(ServiceRepairHistory::class, 'new_status_id');
    }
}
