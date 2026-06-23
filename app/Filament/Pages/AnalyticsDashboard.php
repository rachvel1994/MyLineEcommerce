<?php

namespace App\Filament\Pages;

use App\Enums\WidgetReport;
use App\Filament\Widgets\CurrentMonthPaymentsWidget;
use App\Filament\Widgets\DeadStockWidget;
use App\Filament\Widgets\LatestProductsWidget;
use App\Filament\Widgets\LowStockByModelChart;
use App\Filament\Widgets\ProductChartWidget;
use App\Filament\Widgets\ProductStatsWidget;
use App\Filament\Widgets\SalesSourcesChartWidget;
use App\Filament\Widgets\SellerPerformanceWidget;
use App\Filament\Widgets\TopModelsWidget;
use App\Filament\Widgets\TopUsersChartWidget;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_widget_report')
                ->label(__('admin.widget_report_excel'))
                ->icon(Heroicon::ArrowDownTray)
                ->color('info')
                ->visible(fn (): bool => (bool) auth()->user()?->can('View:AnalyticsDashboard'))
                ->schema([
                    Select::make('widget')
                        ->label(__('admin.widget'))
                        ->options(WidgetReport::options())
                        ->default(WidgetReport::ALL)
                        ->required()
                        ->searchable(),

                    DatePicker::make('from_date')
                        ->label(__('admin.from_date'))
                        ->default(now()->startOfMonth())
                        ->required(),

                    DatePicker::make('to_date')
                        ->label(__('admin.to_date'))
                        ->default(now()->endOfMonth())
                        ->required(),
                ])
                ->action(function (array $data) {
                    $query = http_build_query(array_filter([
                        'widget' => $data['widget'] ?? WidgetReport::ALL,
                        'from_date' => $data['from_date'] ?? null,
                        'to_date' => $data['to_date'] ?? null,
                    ], fn ($value): bool => filled($value)));

                    return redirect()->to(route('widget-reports.export').($query ? '?'.$query : ''));
                })
                ->openUrlInNewTab(),
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
