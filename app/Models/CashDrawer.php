<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashDrawer extends Model
{
    protected $fillable = [
        'opening_balance',
        'current_balance',
        'opened_at',
        'opened_by',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'opened_at'        => 'datetime',
        'closed_at'        => 'datetime',
        'opening_balance'  => 'decimal:2',
        'current_balance'  => 'decimal:2',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Active/current drawer helper:
     * - Always returns latest drawer
     * - If none exists, creates one with 0 balances (observer will still handle carry/closing on "real" create)
     */
    public static function current(): self
    {
        return static::query()->latest('id')->firstOrCreate([], [
            'opening_balance' => 0,
            'current_balance' => 0,
            'opened_at'       => now(),
            'opened_by'       => Auth::id() ?? 4,
        ]);
    }

    /**
     * Apply a movement (and update current_balance) safely:
     * - Uses transaction + row lock to avoid race conditions.
     * - For 'out': amount cannot exceed current_balance (caps to max available).
     * - Never allows negative balances.
     */
    public function applyMovement(
        string $direction,
        float $amount,
        ?string $reason = null,
        ?int $userId = null,
        $related = null,
        ?int $paymentId = null
    ): CashMovement {
        $direction = strtolower(trim($direction));
        $amount = round((float) $amount, 2);

        return DB::transaction(function () use ($direction, $amount, $reason, $userId, $related, $paymentId) {

            /** @var self $drawer */
            $drawer = self::query()
                ->whereKey($this->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $current = round((float) ($drawer->current_balance ?? 0), 2);

            // Normalize direction
            $isIn = $direction === 'in';
            $isOut = $direction === 'out';
            $isAdjust = $direction === 'adjust';

            if (!$isIn && !$isOut && !$isAdjust) {
                // Treat unknown as adjust (keeps old behavior but safe)
                $isAdjust = true;
            }

            // Prevent negative or nonsense amounts
            if ($amount <= 0) {
                // Still log? Usually no. Return a zero-movement? Here we block.
                // If you want to log 0 movements, remove this.
                throw new \InvalidArgumentException('Amount must be greater than 0.');
            }

            // CAP withdrawals to current balance
            if ($isOut) {
                $amount = min($amount, $current);
                if ($amount <= 0) {
                    // nothing to withdraw
                    throw new \InvalidArgumentException('Not enough balance.');
                }
                $drawer->current_balance = round($current - $amount, 2);
            } elseif ($isIn) {
                $drawer->current_balance = round($current + $amount, 2);
            } else {
                // adjust: add amount (can be negative if you pass negative amount; but we block negative above)
                $drawer->current_balance = round($current + $amount, 2);
            }

            // Never allow negative balance (extra safety)
            if ((float) $drawer->current_balance < 0) {
                $drawer->current_balance = 0;
            }

            $drawer->save();

            /** @var CashMovement $movement */
            $movement = $drawer->movements()->create([
                'direction'  => $isAdjust ? 'adjust' : ($isOut ? 'out' : 'in'),
                'amount'     => $amount,
                'reason'     => $reason,
                'user_id'    => $userId,
                'payment_id' => $paymentId,
                'moved_at'   => now(),
            ]);

            if ($related) {
                $movement->related()->associate($related);
                $movement->save();
            }

            return $movement;
        });
    }
}
