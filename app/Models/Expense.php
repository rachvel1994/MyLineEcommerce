<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'expense_type_id',
        'user_id',
        'spent_at',
        'amount',
        'description',
    ];

    protected $casts = [
        'spent_at' => 'date',
        'meta' => 'array',
        'amount' => 'decimal:2',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class, 'expense_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Scope for date range */
    public function scopeBetweenDates($query, ?string $from, ?string $to)
    {
        return $query
            ->when($from, fn($q) => $q->whereDate('spent_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('spent_at', '<=', $to));
    }
}
