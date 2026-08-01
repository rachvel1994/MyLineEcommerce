<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AccessoryOrderItem;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SellerMonthlyStatsWidget extends StatsOverviewWidget
{
    use HasFiltersSchema;

    protected string $view = 'filament.widgets.seller-monthly-stats-widget';

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                DatePicker::make('start_date')
                    ->label(__('admin.start_date')),

                DatePicker::make('end_date')
                    ->label(__('admin.end_date')),
            ]),
        ]);
    }

    private function startDate(): CarbonInterface
    {
        return filled($this->filters['start_date'] ?? null)
            ? Carbon::parse($this->filters['start_date'])->startOfDay()
            : now()->startOfMonth()->startOfDay();
    }

    private function endDate(): CarbonInterface
    {
        return filled($this->filters['end_date'] ?? null)
            ? Carbon::parse($this->filters['end_date'])->endOfDay()
            : now()->endOfDay();
    }

    public function updatedFilters(): void
    {
        $this->cachedStats = null;
    }

    public function resetDateFilters(): void
    {
        $clearedFilters = [
            'start_date' => null,
            'end_date' => null,
        ];

        $this->getFiltersSchema()->fill($clearedFilters);
        $this->filters = $clearedFilters;

        $this->cachedStats = null;
        unset($this->cachedSchemas['content']);
    }

    protected function getStats(): array
    {
        $canSeeCompanyProfit = $this->canSeeCompanyProfit();

        $now = now();

        /*
         * Current month = from first day of current month until today.
         */
        $currentMonthStart = $this->startDate();
        $currentMonthEnd = $this->endDate();

        /*
         * Previous month = previous full calendar month.
         */
        $previousMonth = $now->copy()->subMonthNoOverflow();

        $previousMonthStart = $previousMonth->copy()->startOfMonth()->startOfDay();
        $previousMonthEnd = $previousMonth->copy()->endOfMonth()->endOfDay();

        /*
         * Current year = January 1 until today.
         */
        $currentYearStart = $now->copy()->startOfYear()->startOfDay();
        $currentYearEnd = $now->copy()->endOfDay();

        /*
         * Previous year = previous full calendar year.
         */
        $previousYear = $now->copy()->subYearNoOverflow();

        $previousYearStart = $previousYear->copy()->startOfYear()->startOfDay();
        $previousYearEnd = $previousYear->copy()->endOfYear()->endOfDay();

        $query = AccessoryOrderItem::query()
            ->selectRaw('
                accessory_orders.seller_id,
                users.name as seller_name
            ')
            ->join(
                'accessory_orders',
                'accessory_orders.id',
                '=',
                'accessory_order_items.accessory_order_id'
            )
            ->leftJoin(
                'users',
                'users.id',
                '=',
                'accessory_orders.seller_id'
            )
            ->leftJoin(
                'products',
                'products.id',
                '=',
                'accessory_orders.product_id'
            );

        /*
         * Join accessories only for role ID 1.
         * Normal users do not need accessory sale_price / price for hidden company profit.
         */
        if ($canSeeCompanyProfit) {
            $query->leftJoin(
                'accessories',
                'accessories.id',
                '=',
                'accessory_order_items.accessory_id'
            );
        }

        $periods = [
            'current_month' => [
                $currentMonthStart,
                $currentMonthEnd,
            ],
            'previous_month' => [
                $previousMonthStart,
                $previousMonthEnd,
            ],
            'current_year' => [
                $currentYearStart,
                $currentYearEnd,
            ],
            'previous_year' => [
                $previousYearStart,
                $previousYearEnd,
            ],
        ];

        foreach ($periods as $prefix => [$from, $to]) {
            $this->addPeriodColumns(
                query: $query,
                prefix: $prefix,
                from: $from,
                to: $to,
                includeCompanyProfit: $canSeeCompanyProfit
            );
        }

        $rows = $query
            ->whereBetween('accessory_orders.created_at', [
                $previousYearStart->min($currentMonthStart),
                $previousYearEnd->max($currentMonthEnd),
            ])

            /*
             * Count:
             * 1. Orders without product
             * 2. Orders where product was deleted / missing
             * 3. Orders where product status_id = 4
             */
            ->where(function ($query) {
                $query
                    ->whereNull('accessory_orders.product_id')
                    ->orWhereNull('products.id')
                    ->orWhere('products.status_id', 4);
            })

            ->groupBy(
                'accessory_orders.seller_id',
                'users.name'
            )

            ->orderByDesc(
                'current_month_total_price'
            )

            ->get();

        if ($rows->isEmpty()) {
            return [
                Stat::make(__('admin.no_data'), money(0))
                    ->description(__('admin.no_sales_this_month'))
                    ->color('gray'),
            ];
        }

        $currentMonthTotals = $this->getPeriodTotals(
            rows: $rows,
            prefix: 'current_month',
            includeCompanyProfit: $canSeeCompanyProfit
        );

        $previousMonthTotals = $this->getPeriodTotals(
            rows: $rows,
            prefix: 'previous_month',
            includeCompanyProfit: $canSeeCompanyProfit
        );

        $currentYearTotals = $this->getPeriodTotals(
            rows: $rows,
            prefix: 'current_year',
            includeCompanyProfit: $canSeeCompanyProfit
        );

        $previousYearTotals = $this->getPeriodTotals(
            rows: $rows,
            prefix: 'previous_year',
            includeCompanyProfit: $canSeeCompanyProfit
        );

        $stats = [];

        $stats[] = $this->makePeriodTotalStat(
            title: __('admin.current_month'),
            totals: $currentMonthTotals,
            color: 'primary',
            canSeeCompanyProfit: $canSeeCompanyProfit
        );

        $stats[] = $this->makePeriodTotalStat(
            title: __('admin.previous_month'),
            totals: $previousMonthTotals,
            color: 'warning',
            canSeeCompanyProfit: $canSeeCompanyProfit
        );

        $stats[] = $this->makePeriodTotalStat(
            title: __('admin.current_year'),
            totals: $currentYearTotals,
            color: 'success',
            canSeeCompanyProfit: $canSeeCompanyProfit
        );

        $stats[] = $this->makePeriodTotalStat(
            title: __('admin.previous_year'),
            totals: $previousYearTotals,
            color: 'info',
            canSeeCompanyProfit: $canSeeCompanyProfit
        );

        foreach ($rows as $row) {
            $currentMonthValue = (float) ($row->current_month_total_price ?? 0);
            $previousMonthValue = (float) ($row->previous_month_total_price ?? 0);
            $currentYearValue = (float) ($row->current_year_total_price ?? 0);
            $previousYearValue = (float) ($row->previous_year_total_price ?? 0);

            $description = __('admin.previous_month').': '.money($previousMonthValue).
                ' | '.
                __('admin.current_year').': '.money($currentYearValue).
                ' | '.
                __('admin.previous_year').': '.money($previousYearValue).
                ' | '.
                __('admin.gifted').': '.(int) ($row->current_month_gifted_quantity ?? 0);

            if ($canSeeCompanyProfit) {
                $description .=
                    ' | '.
                    __('admin.company_profit').': '.money((float) ($row->current_month_company_profit ?? 0));
            }

            $stats[] = Stat::make(
                $row->seller_name ?? __('admin.unknown'),
                money($currentMonthValue)
            )
                ->description($description)
                ->color('success');
        }

        return $stats;
    }

    private function addPeriodColumns(
        $query,
        string $prefix,
        $from,
        $to,
        bool $includeCompanyProfit
    ): void {
        /*
         * Common columns for all users.
         *
         * sold_quantity = only non-gift quantity
         * gifted_quantity = only gifted quantity
         * total_quantity = all quantity
         */
        $query->selectRaw("
            COALESCE(SUM(
                CASE
                    WHEN accessory_orders.created_at BETWEEN ? AND ?
                        AND COALESCE(accessory_order_items.is_gift, 0) = 0
                    THEN COALESCE(accessory_order_items.quantity, 0)
                    ELSE 0
                END
            ), 0) as {$prefix}_sold_quantity,

            COALESCE(SUM(
                CASE
                    WHEN accessory_orders.created_at BETWEEN ? AND ?
                        AND COALESCE(accessory_order_items.is_gift, 0) = 1
                    THEN COALESCE(accessory_order_items.quantity, 0)
                    ELSE 0
                END
            ), 0) as {$prefix}_gifted_quantity,

            COALESCE(SUM(
                CASE
                    WHEN accessory_orders.created_at BETWEEN ? AND ?
                    THEN COALESCE(accessory_order_items.quantity, 0)
                    ELSE 0
                END
            ), 0) as {$prefix}_total_quantity,

            COALESCE(SUM(
                CASE
                    WHEN accessory_orders.created_at BETWEEN ? AND ?
                    THEN COALESCE(accessory_order_items.total_price, 0)
                    ELSE 0
                END
            ), 0) as {$prefix}_total_price
        ", [
            $from,
            $to,

            $from,
            $to,

            $from,
            $to,

            $from,
            $to,
        ]);

        /*
         * Company profit columns only for role ID 1.
         *
         * Option B:
         *
         * Normal item:
         * company_profit = (sale_price - price) * quantity
         *
         * Gift item:
         * company_profit = (0 - price) * quantity
         *
         * So gifts are counted as company loss.
         */
        if ($includeCompanyProfit) {
            $query->selectRaw("
                COALESCE(SUM(
                    CASE
                        WHEN accessory_orders.created_at BETWEEN ? AND ?
                            AND COALESCE(accessory_order_items.is_gift, 0) = 0
                        THEN (
                            COALESCE(accessories.sale_price, 0)
                            *
                            COALESCE(accessory_order_items.quantity, 0)
                        )
                        ELSE 0
                    END
                ), 0) as {$prefix}_sale_price_total,

                COALESCE(SUM(
                    CASE
                        WHEN accessory_orders.created_at BETWEEN ? AND ?
                        THEN (
                            COALESCE(accessories.price, 0)
                            *
                            COALESCE(accessory_order_items.quantity, 0)
                        )
                        ELSE 0
                    END
                ), 0) as {$prefix}_cost_total,

                COALESCE(SUM(
                    CASE
                        WHEN accessory_orders.created_at BETWEEN ? AND ?
                        THEN (
                            CASE
                                WHEN COALESCE(accessory_order_items.is_gift, 0) = 1
                                THEN (
                                    0 - COALESCE(accessories.price, 0)
                                )
                                ELSE (
                                    COALESCE(accessories.sale_price, 0)
                                    -
                                    COALESCE(accessories.price, 0)
                                )
                            END
                            *
                            COALESCE(accessory_order_items.quantity, 0)
                        )
                        ELSE 0
                    END
                ), 0) as {$prefix}_company_profit
            ", [
                $from,
                $to,

                $from,
                $to,

                $from,
                $to,
            ]);
        }
    }

    private function getPeriodTotals(
        $rows,
        string $prefix,
        bool $includeCompanyProfit
    ): array {
        $totals = [
            'total_price' => (float) $rows->sum($prefix.'_total_price'),

            'sold_quantity' => (int) $rows->sum($prefix.'_sold_quantity'),
            'gifted_quantity' => (int) $rows->sum($prefix.'_gifted_quantity'),
            'total_quantity' => (int) $rows->sum($prefix.'_total_quantity'),
        ];

        if ($includeCompanyProfit) {
            $totals['sale_price_total'] = (float) $rows->sum($prefix.'_sale_price_total');
            $totals['cost_total'] = (float) $rows->sum($prefix.'_cost_total');
            $totals['company_profit'] = (float) $rows->sum($prefix.'_company_profit');
        }

        return $totals;
    }

    private function makePeriodTotalStat(
        string $title,
        array $totals,
        string $color,
        bool $canSeeCompanyProfit
    ): Stat {
        $value = money((float) ($totals['total_price'] ?? 0));

        $description = $canSeeCompanyProfit
            ? (
                __('admin.company_profit').': '.money((float) ($totals['company_profit'] ?? 0)).
                ' | '.
                __('admin.gifted').': '.(int) ($totals['gifted_quantity'] ?? 0).
                ' | '.
                __('admin.total').': '.(int) ($totals['total_quantity'] ?? 0)
            )
            : (
                __('admin.total_price').': '.money((float) ($totals['total_price'] ?? 0)).
                ' | '.
                __('admin.gifted').': '.(int) ($totals['gifted_quantity'] ?? 0).
                ' | '.
                __('admin.total').': '.(int) ($totals['total_quantity'] ?? 0)
            );

        return Stat::make($title, $value)
            ->description($description)
            ->color($color);
    }

    private function canSeeCompanyProfit(): bool
    {
        return auth()->check()
            && auth()->user()
                ->roles()
                ->where('roles.id', 1)
                ->exists();
    }

    public function getHeading(): ?string
    {
        return __('admin.seller_monthly_profit');
    }
}
