<?php

namespace App\Filament\Widgets;

use App\Models\ConsignmentPriceChange;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ServiceRepairHistory;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ProductStatsWidget extends BaseWidget
{
    use HasWidgetShield, HasFiltersSchema;

    protected ?string $pollingInterval = '10s';

    // =========================
    // FILTERS
    // =========================

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('start_date')
                ->label(__('admin.start_date'))
                ->default(now()->startOfMonth()->startOfDay()),

            DatePicker::make('end_date')
                ->label(__('admin.end_date'))
                ->default(now()->endOfMonth()->endOfDay()),
        ]);
    }

    // =========================
    // HELPERS (FIXED FOR FILAMENT v3+)
    // =========================

    private function startDate(): CarbonInterface
    {
        return !empty($this->filterFormData['start_date'])
            ? Carbon::parse($this->filterFormData['start_date'])->startOfDay()
            : now()->startOfMonth()->startOfDay();
    }

    private function endDate(): CarbonInterface
    {
        return !empty($this->filterFormData['end_date'])
            ? Carbon::parse($this->filterFormData['end_date'])->endOfDay()
            : now()->endOfMonth()->endOfDay();
    }

    // =========================
    // MAIN STATS
    // =========================

    protected function getStats(): array
    {
        $startDate = $this->startDate();
        $endDate = $this->endDate();

        $query = Product::sold()
            ->whereBetween('created_at', [$startDate, $endDate]);

        // =========================
        // CORE FINANCIALS
        // =========================
        $totalProducts = (clone $query)->count();
        $totalPrice = (clone $query)->sum('price');
        $totalSale = (clone $query)->sum('sale_price');

        $expense = Expense::query()->whereBetween('created_at', [$startDate, $endDate])->sum('amount');

        $serviceExpense = ServiceRepairHistory::query()->whereBetween('created_at', [$startDate, $endDate])
            ->sum('repair_price');

        $cash = DB::table('product_payments')
            ->whereIn(
                'payment_id',
                Payment::query()->where('is_cash_analytic', true)->pluck('id')->toArray()
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('price');

        $consignmentCash = ConsignmentPriceChange::query()
            ->whereIn(
                'payment_id',
                Payment::query()->where('is_cash_analytic', true)->pluck('id')->toArray()
            )
            ->whereBetween('created_at', [$startDate, $endDate]) 
            ->sum('paid_amount');

        $cash = $cash + $consignmentCash;
        $profit = $totalSale - $totalPrice - $expense - $serviceExpense;

        // =========================
        // DAILY / WEEKLY / MONTHLY
        // =========================
        $dailySales = Product::sold()
            ->whereDate('created_at', today()->startOfDay())
            ->sum('sale_price');


        $thisMonthSales = Product::sold()
            ->whereBetween('created_at', [
                now()->startOfMonth()->startOfDay(),
                now()->endOfMonth()->endOfDay()
            ])
            ->sum('sale_price');

        $thisYearSales = Product::sold()
            ->whereBetween('created_at', [
                now()->startOfYear()->startOfDay(),
                now()->endOfYear()->endOfDay()
            ])
            ->sum('sale_price');

        // =========================
        // COMPARISONS (CORE INSIGHTS)
        // =========================

        // Week

        $startOfThisWeek = now()->copy()->startOfWeek();
        $endOfThisWeek = now()->copy()->endOfWeek();

        $startOfLastWeek = now()->copy()->subWeek()->startOfWeek();
        $endOfLastWeek = now()->copy()->subWeek()->endOfWeek();

        $sales = Product::sold()
            ->selectRaw("
                SUM(CASE 
                    WHEN created_at BETWEEN ? AND ? THEN sale_price 
                    ELSE 0 END) as this_week,
                SUM(CASE 
                    WHEN created_at BETWEEN ? AND ? THEN sale_price 
                    ELSE 0 END) as last_week
            ", [
                $startOfThisWeek, $endOfThisWeek,
                $startOfLastWeek, $endOfLastWeek
            ])
            ->first();

        $weeklySales = $sales->this_week;
        $lastWeekSales = $sales->last_week;

        // Month
        $lastMonthSales = Product::sold()
            ->whereBetween('created_at', [
                now()->subMonth()->startOfMonth()->startOfDay(),
                now()->subMonth()->endOfMonth()->endOfDay()
            ])
            ->sum('sale_price');


        // Year
        $lastYearSales = Product::sold()
            ->whereBetween('created_at', [
                now()->subYear()->startOfYear(),
                now()->subYear()->endOfYear()
            ])
            ->sum('sale_price');

        // =========================
        // YOY SAME MONTH (FIXED)
        // =========================
        $thisYearSameMonth = Product::sold()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('sale_price');

        $lastYearSameMonth = Product::sold()
            ->whereYear('created_at', now()->subYear()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('sale_price');

        // =========================
        // RETURN STATS
        // =========================
        return [

            // CORE
            Stat::make(__('admin.sold_product'), $totalProducts)
                ->icon(Heroicon::ShoppingCart),

            Stat::make(__('admin.income'), money($totalSale))
                ->icon(Heroicon::Banknotes),

            Stat::make(__('admin.profit'), money($profit))
                ->icon(Heroicon::ArrowTrendingUp)
                ->color($profit >= 0 ? 'success' : 'danger'),

            // COSTS
            Stat::make(__('admin.expense'), money($expense))
                ->icon(Heroicon::CreditCard)
                ->color('danger'),

            Stat::make(__('admin.service_expense'), money($serviceExpense))
                ->icon(Heroicon::WrenchScrewdriver)
                ->color('warning'),

            Stat::make(__('admin.cash'), money($cash))
                ->icon(Heroicon::CurrencyDollar)
                ->color('success'),

            // PERFORMANCE
            Stat::make(__('admin.daily_sales'), money($dailySales))
                ->icon(Heroicon::Sun)
                ->color('primary'),

            Stat::make(__('admin.weekly_sales'), money($weeklySales))
                ->icon(Heroicon::CalendarDays)
                ->color($lastWeekSales <= $weeklySales ? 'success' : 'danger')
                ->description(
                    ($lastWeekSales <= $weeklySales ? '+' : '') . money($lastWeekSales) . __('admin.vs_last_week')
                ),

            Stat::make(__('admin.monthly_sales'), money($thisMonthSales))
                ->icon(Heroicon::Calendar)
                ->description(
                    ($lastMonthSales <= $thisMonthSales ? '+' : '') . money($lastMonthSales) . __('admin.vs_last_month')
                )
                ->color($lastMonthSales <= $thisMonthSales ? 'success' : 'danger'),

            Stat::make(__('admin.yearly_sales'), money($thisYearSales))
                ->icon(Heroicon::ChartPie)
                ->description(
                    ($lastYearSales <= $thisYearSales ? '+' : '') . money($lastYearSales) . __('admin.vs_last_year')
                )
                ->color($lastYearSales <= $thisYearSales ? 'success' : 'danger'),

            // YOY (FIXED)
            Stat::make(__('admin.yoy_same_month'), money($thisYearSameMonth))
                ->icon(Heroicon::Scale)
                ->description(money($lastYearSameMonth) . __('admin.vs_last_year_same_month'))
                ->color($lastYearSameMonth <= $thisYearSameMonth ? 'success' : 'danger'),
        ];
    }

    public function getHeading(): ?string
    {
        return __('admin.product_stats');
    }
}