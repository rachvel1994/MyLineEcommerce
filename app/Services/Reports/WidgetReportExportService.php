<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Enums\WidgetReport;
use App\Models\CashDrawer;
use App\Models\CashMovement;
use App\Models\ConsignmentPriceChange;
use App\Models\DeadStockByModel;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\LowStockByModel;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ServiceRepairHistory;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WidgetReportExportService
{
    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    public function resolveDateRange(?string $fromDate, ?string $toDate): array
    {
        return [
            $fromDate
                ? Carbon::parse($fromDate)->startOfDay()
                : now()->startOfMonth()->startOfDay(),
            $toDate
                ? Carbon::parse($toDate)->endOfDay()
                : now()->endOfMonth()->endOfDay(),
        ];
    }

    public function downloadResponse(
        string $widget,
        CarbonInterface $fromDate,
        CarbonInterface $toDate,
        User $user
    ): StreamedResponse {
        $spreadsheet = $this->spreadsheet($widget, $fromDate, $toDate, $user);
        $writer = new Xlsx($spreadsheet);
        $fileName = $this->fileName($widget, $fromDate, $toDate);

        return new StreamedResponse(
            static function () use ($writer): void {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
                'Pragma' => 'public',
                'Expires' => '0',
            ]
        );
    }

    public function spreadsheet(
        string $widget,
        CarbonInterface $fromDate,
        CarbonInterface $toDate,
        User $user
    ): Spreadsheet {
        $reports = $this->selectedReports($widget);
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);
        $usedSheetTitles = [];

        $this->appendSheet(
            $spreadsheet,
            __('admin.widget_report'),
            [
                __('admin.name'),
                __('admin.value'),
            ],
            [
                [__('admin.widget'), $widget === WidgetReport::ALL ? __('admin.all_widgets') : WidgetReport::from($widget)->label()],
                [__('admin.report_period'), $fromDate->format('Y-m-d').' - '.$toDate->format('Y-m-d')],
                [__('admin.generated_at'), now()->format('Y-m-d H:i:s')],
                [__('admin.generated_by'), $user->name],
            ],
            $usedSheetTitles
        );

        foreach ($reports as $report) {
            $sheet = $this->reportSheet($report, $fromDate, $toDate, $user);

            $this->appendSheet(
                $spreadsheet,
                $sheet['title'],
                $sheet['headings'],
                $sheet['rows'],
                $usedSheetTitles
            );
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    public function fileName(string $widget, CarbonInterface $fromDate, CarbonInterface $toDate): string
    {
        $widgetName = $widget === WidgetReport::ALL ? WidgetReport::ALL : WidgetReport::from($widget)->value;

        return sprintf(
            'widget-reports-%s-%s-%s.xlsx',
            $widgetName,
            $fromDate->format('Y-m-d'),
            $toDate->format('Y-m-d'),
        );
    }

    /**
     * @return list<WidgetReport>
     */
    public function selectedReports(?string $widget): array
    {
        if (blank($widget) || $widget === WidgetReport::ALL) {
            return WidgetReport::cases();
        }

        return [
            WidgetReport::from($widget),
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function reportSheet(
        WidgetReport $report,
        CarbonInterface $fromDate,
        CarbonInterface $toDate,
        User $user
    ): array {
        return match ($report) {
            WidgetReport::ProductStats => $this->productStatsSheet($fromDate, $toDate),
            WidgetReport::TopModels => $this->topModelsSheet($fromDate, $toDate),
            WidgetReport::TopUsers => $this->topUsersSheet($fromDate, $toDate),
            WidgetReport::SalesSources => $this->salesSourcesSheet($fromDate, $toDate),
            WidgetReport::SellerPerformance => $this->sellerPerformanceSheet($fromDate, $toDate),
            WidgetReport::LatestProducts => $this->latestProductsSheet($fromDate, $toDate),
            WidgetReport::DeadStock => $this->deadStockSheet(),
            WidgetReport::LowStock => $this->lowStockSheet(),
            WidgetReport::CurrentMonthPayments => $this->paymentsSheet($fromDate, $toDate),
            WidgetReport::ExpenseStats => $this->expenseStatsSheet($fromDate, $toDate),
            WidgetReport::RepairHistory => $this->repairHistorySheet($fromDate, $toDate),
            WidgetReport::AccessorySales => $this->accessorySalesSheet($fromDate, $toDate),
            WidgetReport::SellerMonthlyStats => $this->sellerAccessoryStatsSheet($fromDate, $toDate, $user),
            WidgetReport::CashDrawer => $this->cashDrawerSheet($fromDate, $toDate),
        };
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function productStatsSheet(CarbonInterface $fromDate, CarbonInterface $toDate): array
    {
        $soldQuery = Product::sold()->whereBetween('created_at', [$fromDate, $toDate]);
        $totalProducts = (clone $soldQuery)->count();
        $totalCost = (float) (clone $soldQuery)->sum('price');
        $totalSale = (float) (clone $soldQuery)->sum('sale_price');
        $expense = (float) Expense::query()->whereBetween('created_at', [$fromDate, $toDate])->sum('amount');
        $serviceExpense = (float) ServiceRepairHistory::query()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('repair_price');
        $cashPaymentIds = Payment::query()
            ->where('is_cash_analytic', true)
            ->pluck('id')
            ->all();

        $cash = (float) DB::table('product_payments')
            ->whereIn('payment_id', $cashPaymentIds)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('price');
        $consignmentCash = (float) ConsignmentPriceChange::query()
            ->whereIn('payment_id', $cashPaymentIds)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('paid_amount');
        $profit = $totalSale - $totalCost - $expense - $serviceExpense;

        return [
            'title' => __('admin.product_stats'),
            'headings' => [
                __('admin.report_period'),
                __('admin.name'),
                __('admin.value'),
            ],
            'rows' => [
                [__('admin.selected_period'), __('admin.sold_product'), $totalProducts],
                [__('admin.selected_period'), __('admin.self_price'), $totalCost],
                [__('admin.selected_period'), __('admin.income'), $totalSale],
                [__('admin.selected_period'), __('admin.expense'), $expense],
                [__('admin.selected_period'), __('admin.service_expense'), $serviceExpense],
                [__('admin.selected_period'), __('admin.cash'), $cash + $consignmentCash],
                [__('admin.selected_period'), __('admin.profit'), $profit],
                [__('admin.today'), __('admin.daily_sales'), $this->soldSalesTotal(today()->startOfDay(), now()->endOfDay())],
                [__('admin.current_month'), __('admin.monthly_sales'), $this->soldSalesTotal(now()->startOfMonth()->startOfDay(), now()->endOfMonth()->endOfDay())],
                [__('admin.current_year'), __('admin.yearly_sales'), $this->soldSalesTotal(now()->startOfYear()->startOfDay(), now()->endOfYear()->endOfDay())],
                [__('admin.previous_month'), __('admin.monthly_sales'), $this->soldSalesTotal(now()->subMonthNoOverflow()->startOfMonth()->startOfDay(), now()->subMonthNoOverflow()->endOfMonth()->endOfDay())],
                [__('admin.previous_year'), __('admin.yearly_sales'), $this->soldSalesTotal(now()->subYearNoOverflow()->startOfYear()->startOfDay(), now()->subYearNoOverflow()->endOfYear()->endOfDay())],
            ],
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function topModelsSheet(CarbonInterface $fromDate, CarbonInterface $toDate): array
    {
        $rows = Product::query()
            ->with('model:id,name')
            ->where('status_id', 4)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('model_id, COUNT(*) as total_sales, SUM(COALESCE(sale_price, 0) - COALESCE(price, 0)) as total_profit')
            ->groupBy('model_id')
            ->orderByDesc('total_profit')
            ->limit(10)
            ->get()
            ->map(fn (Product $row): array => [
                $row->model?->name ?? __('admin.unknown'),
                (int) $row->total_sales,
                (float) $row->total_profit,
            ])
            ->all();

        return [
            'title' => __('admin.top_sale_models'),
            'headings' => [
                __('admin.model'),
                __('admin.sold_product'),
                __('admin.profit'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function topUsersSheet(CarbonInterface $fromDate, CarbonInterface $toDate): array
    {
        $rows = Product::query()
            ->with('user:id,name,mobile')
            ->where('status_id', 4)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn (Product $row): array => [
                $row->user?->name ?? __('admin.unknown'),
                $row->user?->mobile ?? '',
                (int) $row->total,
            ])
            ->all();

        return [
            'title' => __('admin.top_buyers_analytic'),
            'headings' => [
                __('admin.buyer'),
                __('admin.mobile'),
                __('admin.sold_product'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function salesSourcesSheet(CarbonInterface $fromDate, CarbonInterface $toDate): array
    {
        $rows = Product::query()
            ->sold()
            ->whereBetween('products.created_at', [$fromDate, $toDate])
            ->leftJoin('hear_abouts', 'products.hear_about_id', '=', 'hear_abouts.id')
            ->selectRaw('products.hear_about_id, hear_abouts.name, COUNT(*) as total')
            ->groupBy('products.hear_about_id', 'hear_abouts.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row): array => [
                $row->name ?? __('admin.unknown'),
                (int) $row->total,
            ])
            ->all();

        return [
            'title' => __('admin.sales_sources_analytic'),
            'headings' => [
                __('admin.sales_sources'),
                __('admin.sold_product'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function sellerPerformanceSheet(CarbonInterface $fromDate, CarbonInterface $toDate): array
    {
        $rows = Product::query()
            ->with('seller:id,name')
            ->sold()
            ->whereNotNull('seller_id')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('seller_id, COUNT(*) as total')
            ->groupBy('seller_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn (Product $row): array => [
                $row->seller?->name ?? __('admin.unknown'),
                (int) $row->total,
            ])
            ->all();

        return [
            'title' => __('admin.sales_by_seller_analytic'),
            'headings' => [
                __('admin.seller'),
                __('admin.sold_product'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function latestProductsSheet(CarbonInterface $fromDate, CarbonInterface $toDate): array
    {
        $rows = Product::query()
            ->with(['status:id,name', 'model:id,name', 'color:id,name', 'storage:id,name', 'battery:id,name', 'condition:id,name', 'user:id,name,mobile'])
            ->where('status_id', 4)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(fn (Product $product): array => [
                $product->created_at?->format('Y-m-d H:i:s') ?? '',
                $product->status?->name ?? '',
                $product->model?->name ?? '',
                $product->order_id ?? '',
                (float) ($product->sale_price ?? 0),
                (string) ($product->sku ?? ''),
                $product->color?->name ?? '',
                $product->storage?->name ?? '',
                $product->battery?->name ?? '',
                $product->condition?->name ?? '',
                $product->user?->name ?? '',
                $product->user?->mobile ?? '',
            ])
            ->all();

        return [
            'title' => __('admin.latest_sold_products'),
            'headings' => [
                __('admin.created_at'),
                __('admin.status'),
                __('admin.model'),
                __('admin.order_id'),
                __('admin.sale_price'),
                __('admin.sku'),
                __('admin.color'),
                __('admin.storage'),
                __('admin.battery'),
                __('admin.condition'),
                __('admin.user'),
                __('admin.mobile'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function deadStockSheet(): array
    {
        $rows = DeadStockByModel::query()
            ->with('model:id,name')
            ->orderByDesc('dead_stock')
            ->get()
            ->map(fn (DeadStockByModel $row): array => [
                $row->model?->name ?? __('admin.unknown'),
                (int) $row->real_stock,
                (int) $row->dead_stock,
            ])
            ->all();

        return [
            'title' => __('admin.dead_stock_analytic'),
            'headings' => [
                __('admin.model'),
                __('admin.quantity'),
                __('admin.dead_stock'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function lowStockSheet(): array
    {
        $rows = LowStockByModel::query()
            ->with('model:id,name')
            ->orderBy('total')
            ->get()
            ->map(fn (LowStockByModel $row): array => [
                $row->model?->name ?? __('admin.unknown'),
                (int) $row->total,
            ])
            ->all();

        return [
            'title' => __('admin.low_stock_analytic'),
            'headings' => [
                __('admin.model'),
                __('admin.quantity'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function paymentsSheet(CarbonInterface $fromDate, CarbonInterface $toDate): array
    {
        $todayStart = today()->startOfDay();
        $todayEnd = now()->endOfDay();

        $rows = DB::table('product_payments')
            ->join('payments', 'payments.id', '=', 'product_payments.payment_id')
            ->join('products', 'products.id', '=', 'product_payments.product_id')
            ->where('products.status_id', 4)
            ->selectRaw(
                'payments.name as payment_name,
                SUM(CASE WHEN product_payments.created_at BETWEEN ? AND ? THEN product_payments.price ELSE 0 END) as period_total,
                SUM(CASE WHEN product_payments.created_at BETWEEN ? AND ? THEN product_payments.price ELSE 0 END) as today_total',
                [$fromDate, $toDate, $todayStart, $todayEnd]
            )
            ->groupBy('payments.id', 'payments.name')
            ->orderByDesc('period_total')
            ->get()
            ->map(static fn ($row): array => [
                $row->payment_name,
                (float) $row->period_total,
                (float) $row->today_total,
            ])
            ->all();

        return [
            'title' => __('admin.current_month_payments_analytic'),
            'headings' => [
                __('admin.payment'),
                __('admin.period_total'),
                __('admin.today'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function expenseStatsSheet(CarbonInterface $fromDate, CarbonInterface $toDate): array
    {
        $base = Expense::query()
            ->whereDate('spent_at', '>=', $fromDate)
            ->whereDate('spent_at', '<=', $toDate);

        $rows = [
            [
                __('admin.total'),
                (float) (clone $base)->sum('amount'),
            ],
        ];

        ExpenseType::query()
            ->withSum(['expenses as sum_amount' => function ($query) use ($fromDate, $toDate): void {
                $query
                    ->whereDate('spent_at', '>=', $fromDate)
                    ->whereDate('spent_at', '<=', $toDate);
            }], 'amount')
            ->orderByDesc('sum_amount')
            ->get()
            ->each(static function (ExpenseType $type) use (&$rows): void {
                $rows[] = [
                    $type->name,
                    (float) ($type->sum_amount ?? 0),
                ];
            });

        return [
            'title' => __('admin.expense_static_info'),
            'headings' => [
                __('admin.expense_type'),
                __('admin.amount'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function repairHistorySheet(CarbonInterface $fromDate, CarbonInterface $toDate): array
    {
        $base = ServiceRepairHistory::query()
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate);

        $total = (float) (clone $base)->sum('repair_price');
        $paid = (float) (clone $base)
            ->where('is_paid', true)
            ->sum('repair_price');

        return [
            'title' => __('admin.repair_static_info'),
            'headings' => [
                __('admin.name'),
                __('admin.amount'),
            ],
            'rows' => [
                [__('admin.total'), $total],
                [__('admin.is_payed'), $paid],
                [__('admin.debt'), max(0, $total - $paid)],
            ],
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function accessorySalesSheet(CarbonInterface $fromDate, CarbonInterface $toDate): array
    {
        $rows = $this->accessorySalesBaseQuery()
            ->selectRaw('
                accessory_orders.seller_id,
                users.name as seller_name,
                COALESCE(SUM(accessory_order_items.quantity), 0) as total_quantity,
                COALESCE(SUM(accessory_order_items.total_price), 0) as revenue
            ')
            ->whereBetween('accessory_orders.created_at', [$fromDate, $toDate])
            ->groupBy('accessory_orders.seller_id', 'users.name')
            ->orderByDesc('revenue')
            ->get()
            ->map(static fn ($row): array => [
                $row->seller_name ?? __('admin.unknown'),
                (int) $row->total_quantity,
                (float) $row->revenue,
            ])
            ->all();

        array_unshift($rows, [
            __('admin.total'),
            array_sum(array_column($rows, 1)),
            array_sum(array_column($rows, 2)),
        ]);

        return [
            'title' => __('admin.accessory_sales_report'),
            'headings' => [
                __('admin.seller'),
                __('admin.quantity'),
                __('admin.income'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function sellerAccessoryStatsSheet(CarbonInterface $fromDate, CarbonInterface $toDate, User $user): array
    {
        $canSeeCompanyProfit = $this->canSeeCompanyProfit($user);

        $query = $this->accessorySalesBaseQuery()
            ->selectRaw('
                accessory_orders.seller_id,
                users.name as seller_name,
                COALESCE(SUM(CASE WHEN COALESCE(accessory_order_items.is_gift, 0) = 0 THEN accessory_order_items.quantity ELSE 0 END), 0) as sold_quantity,
                COALESCE(SUM(CASE WHEN COALESCE(accessory_order_items.is_gift, 0) = 1 THEN accessory_order_items.quantity ELSE 0 END), 0) as gifted_quantity,
                COALESCE(SUM(accessory_order_items.quantity), 0) as total_quantity,
                COALESCE(SUM(accessory_order_items.total_price), 0) as total_price
            ')
            ->whereBetween('accessory_orders.created_at', [$fromDate, $toDate]);

        if ($canSeeCompanyProfit) {
            $query
                ->leftJoin('accessories', 'accessories.id', '=', 'accessory_order_items.accessory_id')
                ->selectRaw('
                    COALESCE(SUM(
                        CASE
                            WHEN COALESCE(accessory_order_items.is_gift, 0) = 0
                            THEN COALESCE(accessories.sale_price, 0) * COALESCE(accessory_order_items.quantity, 0)
                            ELSE 0
                        END
                    ), 0) as sale_price_total,
                    COALESCE(SUM(COALESCE(accessories.price, 0) * COALESCE(accessory_order_items.quantity, 0)), 0) as cost_total,
                    COALESCE(SUM(
                        CASE
                            WHEN COALESCE(accessory_order_items.is_gift, 0) = 1
                            THEN 0 - COALESCE(accessories.price, 0)
                            ELSE COALESCE(accessories.sale_price, 0) - COALESCE(accessories.price, 0)
                        END * COALESCE(accessory_order_items.quantity, 0)
                    ), 0) as company_profit
                ');
        }

        $rows = $query
            ->groupBy('accessory_orders.seller_id', 'users.name')
            ->orderByDesc($canSeeCompanyProfit ? 'company_profit' : 'total_price')
            ->get()
            ->map(static function ($row) use ($canSeeCompanyProfit): array {
                $data = [
                    $row->seller_name ?? __('admin.unknown'),
                    (int) $row->sold_quantity,
                    (int) $row->gifted_quantity,
                    (int) $row->total_quantity,
                    (float) $row->total_price,
                ];

                if ($canSeeCompanyProfit) {
                    $data[] = (float) $row->sale_price_total;
                    $data[] = (float) $row->cost_total;
                    $data[] = (float) $row->company_profit;
                }

                return $data;
            })
            ->all();

        $headings = [
            __('admin.seller'),
            __('admin.sold_quantity'),
            __('admin.gifted_quantity'),
            __('admin.total_quantity'),
            __('admin.total_price'),
        ];

        if ($canSeeCompanyProfit) {
            array_push(
                $headings,
                __('admin.sale_price_total'),
                __('admin.cost_total'),
                __('admin.company_profit'),
            );
        }

        return [
            'title' => __('admin.seller_monthly_profit'),
            'headings' => $headings,
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title: string, headings: list<string>, rows: list<list<mixed>>}
     */
    private function cashDrawerSheet(CarbonInterface $fromDate, CarbonInterface $toDate): array
    {
        $drawer = CashDrawer::query()->latest('id')->first();

        if (! $drawer) {
            return [
                'title' => __('admin.cash_drawer_widget'),
                'headings' => [
                    __('admin.name'),
                    __('admin.amount'),
                ],
                'rows' => [
                    [__('admin.no_drawer'), 0],
                ],
            ];
        }

        $beforeRange = CashMovement::query()
            ->where('cash_drawer_id', $drawer->id)
            ->where('moved_at', '<', $fromDate)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN direction = 'in' THEN amount END), 0)
                - COALESCE(SUM(CASE WHEN direction = 'out' THEN amount END), 0)
                + COALESCE(SUM(CASE WHEN direction = 'adjust' THEN amount END), 0)
                as balance
            ")
            ->value('balance');

        $periodTotals = CashMovement::query()
            ->where('cash_drawer_id', $drawer->id)
            ->whereBetween('moved_at', [$fromDate, $toDate])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN direction = 'in' THEN amount END), 0) as cash_in,
                COALESCE(SUM(CASE WHEN direction = 'out' THEN amount END), 0) as cash_out,
                COALESCE(SUM(CASE WHEN direction = 'adjust' THEN amount END), 0) as adjust
            ")
            ->first();

        $startBalance = (float) ($drawer->opening_balance ?? 0) + (float) ($beforeRange ?? 0);
        $cashIn = (float) ($periodTotals->cash_in ?? 0);
        $cashOut = (float) ($periodTotals->cash_out ?? 0);
        $adjust = (float) ($periodTotals->adjust ?? 0);
        $endBalance = $startBalance + $cashIn - $cashOut + $adjust;

        return [
            'title' => __('admin.cash_drawer_widget'),
            'headings' => [
                __('admin.name'),
                __('admin.amount'),
            ],
            'rows' => [
                [__('admin.start_of_day'), $startBalance],
                [__('admin.cash_in'), $cashIn],
                [__('admin.cash_out'), $cashOut],
                [__('admin.cash_adjustment'), $adjust],
                [__('admin.end_of_day'), $endBalance],
            ],
        ];
    }

    private function soldSalesTotal(CarbonInterface $fromDate, CarbonInterface $toDate): float
    {
        return (float) Product::sold()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('sale_price');
    }

    private function accessorySalesBaseQuery(): Builder
    {
        return DB::table('accessory_order_items')
            ->join('accessory_orders', 'accessory_orders.id', '=', 'accessory_order_items.accessory_order_id')
            ->leftJoin('users', 'users.id', '=', 'accessory_orders.seller_id')
            ->leftJoin('products', 'products.id', '=', 'accessory_orders.product_id')
            ->where(static function (Builder $query): void {
                $query
                    ->whereNull('accessory_orders.product_id')
                    ->orWhereNull('products.id')
                    ->orWhere('products.status_id', 4);
            });
    }

    private function canSeeCompanyProfit(User $user): bool
    {
        return $user
            ->roles()
            ->where('roles.id', 1)
            ->exists();
    }

    /**
     * @param  list<string>  $headings
     * @param  list<list<mixed>>  $rows
     * @param  array<string, true>  $usedSheetTitles
     */
    private function appendSheet(
        Spreadsheet $spreadsheet,
        string $title,
        array $headings,
        array $rows,
        array &$usedSheetTitles
    ): void {
        $sheet = new Worksheet($spreadsheet, $this->uniqueSheetTitle($title, $usedSheetTitles));
        $spreadsheet->addSheet($sheet);

        foreach ($headings as $columnIndex => $heading) {
            $sheet->setCellValueByColumnAndRow($columnIndex + 1, 1, $heading);
        }

        if ($rows === []) {
            $rows = [
                [__('admin.no_data')],
            ];
        }

        foreach ($rows as $rowIndex => $row) {
            foreach (array_values($row) as $columnIndex => $value) {
                $cellColumn = $columnIndex + 1;
                $cellRow = $rowIndex + 2;

                if (is_int($value) || is_float($value)) {
                    $sheet->setCellValueByColumnAndRow($cellColumn, $cellRow, $value);

                    continue;
                }

                $sheet->setCellValueExplicitByColumnAndRow(
                    $cellColumn,
                    $cellRow,
                    (string) ($value ?? ''),
                    DataType::TYPE_STRING
                );
            }
        }

        $lastColumn = max(count($headings), 1);
        $sheet->freezePane('A2');
        $sheet->getStyleByColumnAndRow(1, 1, $lastColumn, 1)->getFont()->setBold(true);

        for ($column = 1; $column <= $lastColumn; $column++) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }
    }

    private function safeSheetTitle(string $title): string
    {
        $title = preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', ' ', strip_tags($title)) ?: __('admin.widget_report');
        $title = trim(preg_replace('/\\s+/', ' ', $title) ?: $title);

        return mb_substr($title, 0, 31);
    }

    /**
     * @param  array<string, true>  $usedSheetTitles
     */
    private function uniqueSheetTitle(string $title, array &$usedSheetTitles): string
    {
        $baseTitle = $this->safeSheetTitle($title);
        $candidate = $baseTitle;
        $counter = 2;

        while (isset($usedSheetTitles[$candidate])) {
            $suffix = ' '.$counter;
            $candidate = mb_substr($baseTitle, 0, 31 - mb_strlen($suffix)).$suffix;
            $counter++;
        }

        $usedSheetTitles[$candidate] = true;

        return $candidate;
    }
}
