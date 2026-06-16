<?php

namespace App\Console\Commands;

use App\Models\CashDrawer;
use Illuminate\Console\Command;

class CloseCashDrawer extends Command
{
    protected $signature = 'cashdrawer:close';

    protected $description = 'Automatically close current cash drawer and open a new one';

    public function handle(): int
    {
        $drawer = CashDrawer::query()
            ->whereNull('closed_at')
            ->latest('id')
            ->first();

        if (! $drawer) {
            $this->info('No active cash drawer found.');

            return self::SUCCESS;
        }

        $drawer->update([
            'closed_at' => now(),
            'closed_by' => 4,
        ]);

        $this->info("Cash drawer #{$drawer->id} closed.");
        
        $openAt = now()->isSaturday()
            ? now()->next('Monday')->startOfDay()->addHours(8)
            : now()->addDay()->startOfDay()->addHours(8);

        $newDrawer = CashDrawer::query()->create([
            'opening_balance' => $drawer->current_balance,
            'current_balance' => $drawer->current_balance,
            'opened_at' => $openAt,
            'opened_by' => 4,
        ]);

        $this->info(
            "New cash drawer #{$newDrawer->id} scheduled for {$openAt->format('Y-m-d H:i:s')}."
        );

        return self::SUCCESS;
    }
}