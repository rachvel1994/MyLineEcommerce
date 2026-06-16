<?php

namespace App\Filament\Widgets;

use App\Models\PaymentMonthlyReport;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class CurrentMonthPaymentsWidget extends TableWidget
{
    use HasWidgetShield;

    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(PaymentMonthlyReport::query())

            ->defaultSort('month_total', 'desc')

            ->columns([
                TextColumn::make('payment_name')
                    ->label(__('admin.payment'))
                    ->weight('bold'),

                TextColumn::make('month_total')
                    ->label(__('admin.current_month'))
                    ->color(fn($state) => $state > 0 ? 'success' : 'danger')
                    ->money('GEL')
                    ->weight('bold'),

                TextColumn::make('today_total')
                    ->label(__('admin.today'))
                    ->color(fn($state) => $state > 0 ? 'success' : 'danger')
                    ->money('GEL')
                    ->weight('bold'),
            ])
            ->paginated(false)
            ->emptyStateHeading(__('admin.current_month_payments_analytic'))
            ->heading(__('admin.current_month_payments_analytic'));
    }

    public function getTableHeading(): ?string
    {
        return __('admin.current_month_payments_analytic');
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->payment_id;
    }
}
