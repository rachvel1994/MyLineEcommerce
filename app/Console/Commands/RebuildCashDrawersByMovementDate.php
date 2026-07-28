<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class RebuildCashDrawersByMovementDate extends Command
{
    protected $signature = 'cash-drawers:rebuild-by-movements
                            {--from= : First movement date to rebuild (Y-m-d)}
                            {--to= : Last movement date to rebuild (Y-m-d)}
                            {--user=4 : Existing user ID recorded as opened_by and closed_by}
                            {--opening-balance= : Override the first drawer opening balance}
                            {--apply : Apply changes; without this option the command is a dry run}';

    protected $description = 'Rebuild daily cash drawers from cash movement dates and recalculate their balances';

    public function handle(): int
    {
        try {
            $from = $this->dateOption('from');
            $to = $this->dateOption('to');
            $userId = $this->userId();

            if ($from !== null && $to !== null && $from->isAfter($to)) {
                throw new RuntimeException('The --from date must be before or equal to the --to date.');
            }

            $dates = $this->movementDates($from, $to);

            if ($dates->isEmpty()) {
                $this->warn('No cash movements were found for the selected period.');

                return self::SUCCESS;
            }

            $this->displayPlan($dates);

            if (! (bool) $this->option('apply')) {
                $this->newLine();
                $this->warn('Dry run only. No database changes were made.');
                $this->line('Run the same command with --apply after reviewing the dates above.');

                return self::SUCCESS;
            }

            $openingBalance = $this->openingBalanceOption();

            DB::transaction(function () use ($dates, $userId, $openingBalance): void {
                $this->rebuild($dates, $userId, $openingBalance);
            }, 3);

            $this->newLine();
            $this->info('Cash drawers rebuilt successfully.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @param  Collection<int, string>  $dates
     */
    private function rebuild(Collection $dates, int $userId, ?int $openingBalance): void
    {
        $firstDate = CarbonImmutable::createFromFormat('!Y-m-d', $dates->first());
        $lastDate = CarbonImmutable::createFromFormat('!Y-m-d', $dates->last());

        if ($firstDate === false || $lastDate === false) {
            throw new RuntimeException('Unable to resolve the movement date range.');
        }

        DB::table('cash_movements')
            ->whereBetween('moved_at', [$firstDate->startOfDay(), $lastDate->endOfDay()])
            ->lockForUpdate()
            ->get(['id']);

        DB::table('cash_drawers')
            ->where(function ($query) use ($firstDate, $lastDate): void {
                $query
                    ->whereBetween('opened_at', [$firstDate->startOfDay(), $lastDate->endOfDay()])
                    ->orWhereNull('closed_at');
            })
            ->lockForUpdate()
            ->get(['id']);

        $runningBalance = $openingBalance ?? $this->resolveOpeningBalance($firstDate);
        $drawerIds = [];

        foreach ($dates as $dateValue) {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $dateValue);

            if ($date === false) {
                throw new RuntimeException("Invalid movement date [{$dateValue}].");
            }

            $drawerId = $this->canonicalDrawerId($date, $userId, $runningBalance);
            $drawerIds[$dateValue] = $drawerId;

            DB::table('cash_movements')
                ->whereBetween('moved_at', [$date->startOfDay(), $date->endOfDay()])
                ->update([
                    'cash_drawer_id' => $drawerId,
                    'updated_at' => now(),
                ]);
        }

        foreach ($dates as $dateValue) {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $dateValue);

            if ($date === false) {
                throw new RuntimeException("Invalid movement date [{$dateValue}].");
            }

            $drawerId = $drawerIds[$dateValue];
            $this->deleteEmptyDuplicateDrawers($date, $drawerId);

            [$cashIn, $cashOut, $adjustment] = $this->movementTotals($drawerId, $date);
            $currentBalance = $runningBalance + $cashIn - $cashOut + $adjustment;

            if ($currentBalance < 0) {
                throw new RuntimeException(sprintf(
                    'Drawer balance becomes negative on %s (%.2f). Check the movements or use the correct --opening-balance.',
                    $dateValue,
                    $this->fromCents($currentBalance),
                ));
            }

            $hasLaterDrawer = DB::table('cash_drawers')
                ->where('opened_at', '>', $date->endOfDay())
                ->exists();
            $isLast = $dateValue === $dates->last();
            $shouldClose = ! $isLast || $hasLaterDrawer;

            DB::table('cash_drawers')
                ->where('id', $drawerId)
                ->update([
                    'opening_balance' => $this->fromCents($runningBalance),
                    'current_balance' => $this->fromCents($currentBalance),
                    'opened_at' => $date->startOfDay(),
                    'opened_by' => DB::raw('COALESCE(opened_by, '.(int) $userId.')'),
                    'closed_at' => $shouldClose ? $date->endOfDay() : null,
                    'closed_by' => $shouldClose ? $userId : null,
                    'updated_at' => now(),
                ]);

            $this->line(sprintf(
                '%s | Drawer: %d | Opening: %.2f | In: %.2f | Out: %.2f | Adjust: %.2f | Current: %.2f',
                $dateValue,
                $drawerId,
                $this->fromCents($runningBalance),
                $this->fromCents($cashIn),
                $this->fromCents($cashOut),
                $this->fromCents($adjustment),
                $this->fromCents($currentBalance),
            ));

            $runningBalance = $currentBalance;
        }
    }

    private function canonicalDrawerId(
        CarbonImmutable $date,
        int $userId,
        int $openingBalance,
    ): int {
        $drawerId = DB::table('cash_drawers')
            ->whereBetween('opened_at', [$date->startOfDay(), $date->endOfDay()])
            ->orderBy('opened_at')
            ->orderBy('id')
            ->value('id');

        if ($drawerId !== null) {
            return (int) $drawerId;
        }

        return (int) DB::table('cash_drawers')->insertGetId([
            'opening_balance' => $this->fromCents($openingBalance),
            'current_balance' => $this->fromCents($openingBalance),
            'opened_at' => $date->startOfDay(),
            'opened_by' => $userId,
            'closed_at' => null,
            'closed_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function deleteEmptyDuplicateDrawers(CarbonImmutable $date, int $canonicalDrawerId): void
    {
        $duplicateIds = DB::table('cash_drawers')
            ->whereBetween('opened_at', [$date->startOfDay(), $date->endOfDay()])
            ->where('id', '!=', $canonicalDrawerId)
            ->pluck('id');

        foreach ($duplicateIds as $duplicateId) {
            if (DB::table('cash_movements')->where('cash_drawer_id', $duplicateId)->exists()) {
                throw new RuntimeException(
                    "Duplicate drawer [{$duplicateId}] owns movements outside {$date->toDateString()}; no drawers were changed."
                );
            }
        }

        if ($duplicateIds->isNotEmpty()) {
            DB::table('cash_drawers')->whereIn('id', $duplicateIds)->delete();
        }
    }

    /**
     * @return array{int, int, int}
     */
    private function movementTotals(int $drawerId, CarbonImmutable $date): array
    {
        $totals = [0, 0, 0];

        DB::table('cash_movements')
            ->where('cash_drawer_id', $drawerId)
            ->whereBetween('moved_at', [$date->startOfDay(), $date->endOfDay()])
            ->orderBy('id')
            ->get(['direction', 'amount'])
            ->each(function (object $movement) use (&$totals): void {
                $amount = $this->toCents((string) $movement->amount);

                match ($movement->direction) {
                    'in' => $totals[0] += abs($amount),
                    'out' => $totals[1] += abs($amount),
                    'adjust' => $totals[2] += $amount,
                    default => throw new RuntimeException(
                        "Unsupported cash movement direction [{$movement->direction}]."
                    ),
                };
            });

        return $totals;
    }

    private function resolveOpeningBalance(CarbonImmutable $firstDate): int
    {
        $sameDayOpening = DB::table('cash_drawers')
            ->whereBetween('opened_at', [$firstDate->startOfDay(), $firstDate->endOfDay()])
            ->orderBy('opened_at')
            ->orderBy('id')
            ->value('opening_balance');

        if ($sameDayOpening !== null) {
            return $this->toCents((string) $sameDayOpening);
        }

        $previousBalance = DB::table('cash_drawers')
            ->where('opened_at', '<', $firstDate->startOfDay())
            ->orderByDesc('opened_at')
            ->orderByDesc('id')
            ->value('current_balance');

        return $this->toCents((string) ($previousBalance ?? '0'));
    }

    /**
     * @return Collection<int, string>
     */
    private function movementDates(?CarbonImmutable $from, ?CarbonImmutable $to): Collection
    {
        return DB::table('cash_movements')
            ->when($from !== null, fn ($query) => $query->where('moved_at', '>=', $from->startOfDay()))
            ->when($to !== null, fn ($query) => $query->where('moved_at', '<=', $to->endOfDay()))
            ->selectRaw('DATE(moved_at) AS movement_date')
            ->distinct()
            ->orderBy('movement_date')
            ->pluck('movement_date')
            ->map(fn (mixed $date): string => (string) $date)
            ->values();
    }

    /**
     * @param  Collection<int, string>  $dates
     */
    private function displayPlan(Collection $dates): void
    {
        $this->info('Movement dates found:');

        foreach ($dates as $date) {
            $count = DB::table('cash_movements')->whereDate('moved_at', $date)->count();
            $this->line("{$date}: {$count} movement(s)");
        }
    }

    private function userId(): int
    {
        $userId = filter_var($this->option('user'), FILTER_VALIDATE_INT);

        if (! is_int($userId) || $userId < 1 || ! DB::table('users')->where('id', $userId)->exists()) {
            throw new RuntimeException('The --user option must reference an existing user.');
        }

        return $userId;
    }

    private function dateOption(string $name): ?CarbonImmutable
    {
        $value = $this->option($name);

        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);
        } catch (InvalidFormatException) {
            $date = false;
        }

        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new RuntimeException("The --{$name} option must use the Y-m-d format.");
        }

        return $date;
    }

    private function openingBalanceOption(): ?int
    {
        $value = $this->option('opening-balance');

        if (! is_string($value) || $value === '') {
            return null;
        }

        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) {
            throw new RuntimeException('The --opening-balance option must be a non-negative amount with at most two decimals.');
        }

        return $this->toCents($value);
    }

    private function toCents(string $amount): int
    {
        $normalized = trim($amount);
        $negative = str_starts_with($normalized, '-');
        $normalized = ltrim($normalized, '+-');
        [$whole, $decimal] = array_pad(explode('.', $normalized, 2), 2, '');
        $cents = ((int) $whole * 100) + (int) str_pad(substr($decimal, 0, 2), 2, '0');

        return $negative ? -$cents : $cents;
    }

    private function fromCents(int $amount): float
    {
        return $amount / 100;
    }
}
