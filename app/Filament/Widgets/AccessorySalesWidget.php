<?php

namespace App\Filament\Widgets;

use App\Models\AccessoryOrderItem;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccessorySalesWidget extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected function getStats(): array
    {
        $rows = AccessoryOrderItem::query()
            ->selectRaw('
                accessory_orders.seller_id,
                users.name as seller_name,
                COALESCE(SUM(accessory_order_items.quantity), 0) as total_quantity,
                COALESCE(SUM(accessory_order_items.total_price), 0) as revenue
            ')
            ->join('accessory_orders', 'accessory_orders.id', '=', 'accessory_order_items.accessory_order_id')
            ->join('users', 'users.id', '=', 'accessory_orders.seller_id')
            ->leftJoin('products', 'products.id', '=', 'accessory_orders.product_id')
            ->where(function ($q) {
                $q->whereNull('products.id')
                    ->orWhere('products.status_id', 4);
            })
            ->whereBetween('accessory_orders.created_at',[
                now()->startOfMonth()->startOfDay(),
                now()->endOfMonth()->endOfDay()
            ])
            ->groupBy('accessory_orders.seller_id', 'users.name')
            ->orderByDesc('revenue')
            ->get();

        $monthlyTotal = (float) $rows->sum('revenue');

        if ($rows->isEmpty()) {
            return [
                Stat::make(__('admin.no_data'), '0.00 GEL')
                    ->description(__('admin.no_sales_this_month'))
                    ->color('gray'),
            ];
        }

        $stats = [
            Stat::make(__('admin.monthly_sum'), money($monthlyTotal))
                ->description(__('admin.total_revenue_this_month'))
                ->color('primary'),
        ];

        return array_merge(
            $stats,
            $rows->map(function ($row) {
                return Stat::make(
                    $row->seller_name ?? __('admin.unknown'),
                    money((float) ($row->revenue ?? 0))
                )
                    ->description(__('admin.quantity') . ': ' . (int) ($row->total_quantity ?? 0))
                    ->color('success');
            })->toArray()
        );
    }

    public function getHeading(): ?string
    {
        return __('admin.seller_monthly_profit');
    }
}
