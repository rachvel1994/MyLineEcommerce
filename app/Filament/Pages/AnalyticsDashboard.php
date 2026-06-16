<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LatestProductsWidget;
use App\Filament\Widgets\ProductChartWidget;
use App\Filament\Widgets\ProductStatsWidget;
use App\Filament\Widgets\SalesSourcesChartWidget;
use App\Filament\Widgets\SellerPerformanceWidget;
use App\Filament\Widgets\DeadStockWidget;
use App\Filament\Widgets\CurrentMonthPaymentsWidget;
use App\Filament\Widgets\LowStockByModelChart;
use App\Filament\Widgets\TopModelsWidget;
use App\Filament\Widgets\TopUsersChartWidget;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class AnalyticsDashboard extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PresentationChartLine;

    protected Width|string|null $maxContentWidth = 'full';

    protected string $view = 'filament.pages.analytics-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            ProductStatsWidget::class,
//            ProductChartWidget::class,
            DeadStockWidget::class,
            LowStockByModelChart::class,
            CurrentMonthPaymentsWidget::class,
            TopModelsWidget::class,
            TopUsersChartWidget::class,
            SalesSourcesChartWidget::class,
            SellerPerformanceWidget::class,
            LatestProductsWidget::class,
        ];
    }

    protected function getHeaderWidgetsFormSchema(): array
    {
        return [
            DatePicker::make('from')->default(now()->startOfMonth()->startOfDay()),
            DatePicker::make('to')->default(now()->endOfMonth()->endOfDay()),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.analytics_dashboard');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin.analytics_dashboard');
    }
}
