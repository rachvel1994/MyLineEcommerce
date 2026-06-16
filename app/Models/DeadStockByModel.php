<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeadStockByModel extends Model
{
    protected $table = 'dead_stock_by_model';

    public $timestamps = false;

    protected $primaryKey = 'model_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public function model(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'model_id');
    }
}
