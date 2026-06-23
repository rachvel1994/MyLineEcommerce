<?php

declare(strict_types=1);

namespace App\Enums;

enum WidgetReport: string
{
    public const ALL = 'all';

    case ProductStats = 'product_stats';
    case TopModels = 'top_models';
    case TopUsers = 'top_users';
    case SalesSources = 'sales_sources';
    case SellerPerformance = 'seller_performance';
    case LatestProducts = 'latest_products';
    case DeadStock = 'dead_stock';
    case LowStock = 'low_stock';
    case CurrentMonthPayments = 'current_month_payments';
    case ExpenseStats = 'expense_stats';
    case RepairHistory = 'repair_history';
    case AccessorySales = 'accessory_sales';
    case SellerMonthlyStats = 'seller_monthly_stats';
    case CashDrawer = 'cash_drawer';

    public function label(): string
    {
        return match ($this) {
            self::ProductStats => __('admin.product_stats'),
            self::TopModels => __('admin.top_sale_models'),
            self::TopUsers => __('admin.top_buyers_analytic'),
            self::SalesSources => __('admin.sales_sources_analytic'),
            self::SellerPerformance => __('admin.sales_by_seller_analytic'),
            self::LatestProducts => __('admin.latest_sold_products'),
            self::DeadStock => __('admin.dead_stock_analytic'),
            self::LowStock => __('admin.low_stock_analytic'),
            self::CurrentMonthPayments => __('admin.current_month_payments_analytic'),
            self::ExpenseStats => __('admin.expense_static_info'),
            self::RepairHistory => __('admin.repair_static_info'),
            self::AccessorySales => __('admin.accessory_sales_report'),
            self::SellerMonthlyStats => __('admin.seller_monthly_profit'),
            self::CashDrawer => __('admin.cash_drawer_widget'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [
            self::ALL => __('admin.all_widgets'),
        ];

        foreach (self::cases() as $report) {
            $options[$report->value] = $report->label();
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $report): string => $report->value,
            self::cases(),
        );
    }
}
