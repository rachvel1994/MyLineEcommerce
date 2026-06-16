<?php

namespace App\Observers;

use App\Models\CashDrawer;
use Illuminate\Support\Facades\Auth;

class CashDrawerObserver
{
    public function creating(CashDrawer $cashDrawer): void
    {
        $previousDrawer = CashDrawer::query()
            ->whereNull('closed_at')
            ->latest('id')
            ->first();

        if ($previousDrawer) {
            $previousDrawer->update([
                'closed_at' => now(),
                'closed_by' =>  Auth::id() ?? 4,
            ]);
        } else {
            $cashDrawer->opening_balance ??= 0;
            $cashDrawer->current_balance ??= 0;
        }

        $cashDrawer->opened_at ??= now()->addDay()->startOfDay()->addHours(8);
        $cashDrawer->opened_by ??= Auth::id() ?? 4;
    }
}