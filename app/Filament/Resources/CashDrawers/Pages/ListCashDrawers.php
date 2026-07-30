<?php

declare(strict_types=1);

namespace App\Filament\Resources\CashDrawers\Pages;

use App\Filament\Resources\CashDrawers\CashDrawerResource;
use App\Filament\Widgets\CashDrawerWidget;
use App\Forms\Components\PriceInput;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

class ListCashDrawers extends ListRecords
{
    protected static string $resource = CashDrawerResource::class;

    protected Width|string|null $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fixAmountsByMovements')
                ->label(__('admin.fix_cash_drawer_amounts'))
                ->icon(Heroicon::Calculator)
                ->color('warning')
                ->visible(fn (): bool => canAbility('CanViewAmountFixer:CashDrawer'))
                ->disabled(fn (): bool => ! canAbility('CanUseAmountFixer:CashDrawer'))
                ->requiresConfirmation()
                ->modalHeading(__('admin.fix_cash_drawer_amounts'))
                ->modalDescription(__('admin.fix_cash_drawer_amounts_confirmation'))
                ->schema([
                    DatePicker::make('from')
                        ->label(__('admin.from_date'))
                        ->native(false),
                    DatePicker::make('to')
                        ->label(__('admin.to_date'))
                        ->native(false)
                        ->afterOrEqual('from'),
                    PriceInput::make('opening_balance')
                        ->label(__('admin.opening_balance'))
                        ->minValue(0),
                ])
                ->action(function (array $data): void {
                    abort_unless(canAbility('CanUseAmountFixer:CashDrawer'), 403);

                    $arguments = array_filter([
                        '--from' => $data['from'] ?? null,
                        '--to' => $data['to'] ?? null,
                        '--opening-balance' => $data['opening_balance'] ?? null,
                    ], static fn (mixed $value): bool => filled($value));

                    $arguments['--user'] = (int) auth()->id();
                    $arguments['--apply'] = true;

                    $exitCode = Artisan::call('cash-drawers:rebuild-by-movements', $arguments);
                    $output = trim(Artisan::output());

                    if ($exitCode !== 0) {
                        Notification::make()
                            ->title(__('admin.fix_cash_drawer_amounts_failed'))
                            ->body($output)
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title(__('admin.fix_cash_drawer_amounts_success'))
                        ->body($output)
                        ->success()
                        ->send();
                }),
            CreateAction::make()
                ->label(__('admin.new_day_opening')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CashDrawerWidget::class,
        ];
    }
}
