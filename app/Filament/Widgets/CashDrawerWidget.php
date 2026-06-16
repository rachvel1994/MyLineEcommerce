<?php

namespace App\Filament\Widgets;

use App\Models\CashDrawer;
use App\Models\CashMovement;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashDrawerWidget extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected function getStats(): array
    {
        $drawer = CashDrawer::current();

        if (!$drawer) {
            return [
                Stat::make(__('admin.no_drawer'), '0.00 GEL'),
            ];
        }

        $startOfDay = today()->startOfDay();
        $endOfDay = now()->endOfDay();

        $beforeToday = CashMovement::query()
            ->where('cash_drawer_id', $drawer->id)
            ->where('created_at', '<', $startOfDay)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN direction = 'in' THEN amount END),0)
                - COALESCE(SUM(CASE WHEN direction = 'out' THEN amount END),0)
                + COALESCE(SUM(CASE WHEN direction = 'adjust' THEN amount END),0)
                as balance
            ")
            ->value('balance');

        $startBalance = ($drawer->opening_balance ?? 0) + ($beforeToday ?? 0);

        $todayTotals = CashMovement::query()
            ->where('cash_drawer_id', $drawer->id)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN direction = 'in' THEN amount END),0) as cash_in,
                COALESCE(SUM(CASE WHEN direction = 'out' THEN amount END),0) as cash_out,
                COALESCE(SUM(CASE WHEN direction = 'adjust' THEN amount END),0) as adjust
            ")
            ->first();

        $endBalance = $startBalance
            + $todayTotals->cash_in
            - $todayTotals->cash_out
            + $todayTotals->adjust;

        return [
            Stat::make(__('admin.start_of_day'), number_format($startBalance, 2) . ' GEL')
                ->description(__('admin.balance_at_00'))
                ->descriptionIcon(Heroicon::Sun)
                ->color('gray'),

            Stat::make(__('admin.end_of_day'), number_format($endBalance, 2) . ' GEL')
                ->description(__('admin.current_balance'))
                ->descriptionIcon(Heroicon::Moon)
                ->color('success'),

            Stat::make(__('admin.today_in'), number_format($todayTotals->cash_in, 2) . ' GEL')
                ->description(__('admin.income_today'))
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->color('success'),

            Stat::make(__('admin.today_out'), number_format($todayTotals->cash_out, 2) . ' GEL')
                ->description(__('admin.expense_today'))
                ->descriptionIcon(Heroicon::ArrowTrendingDown)
                ->color('danger'),

//            Stat::make(__('admin.today_adjust'), number_format($todayTotals->adjust, 2) . ' GEL')
//                ->description(__('admin.adjustments_today'))
//                ->descriptionIcon(Heroicon::AdjustmentsHorizontal)
//                ->color('warning'),
        ];
    }

    public function getHeading(): ?string
    {
        return __('admin.cash_drawer_widget');
    }
}