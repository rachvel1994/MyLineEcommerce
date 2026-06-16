<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductModel extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'is_active'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'model_id');
    }
}
