<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseType extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
