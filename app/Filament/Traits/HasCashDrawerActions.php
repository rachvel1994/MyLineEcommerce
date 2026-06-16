<?php

namespace App\Filament\Traits;

use App\Forms\Components\PriceInput;
use App\Models\CashDrawer;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;

trait HasCashDrawerActions
{
    /**
     * Override if you want different ability naming strategy.
     */
    protected static function cashDrawerAbility(string $ability): string
    {
        return "{$ability}:CashDrawer";
    }

    protected static function canCashDrawer(string $ability): bool
    {
        return canAbility(static::cashDrawerAbility($ability));
    }

    protected static function isOpen(CashDrawer $record): bool
    {
        return blank($record->closed_at);
    }

    protected static function isClosed(CashDrawer $record): bool
    {
        return filled($record->closed_at);
    }

    public static function cashDrawerActions(): array
    {
        return [
            static::setOpening(),
            static::deposit(),
            static::withdraw(),
            static::closeDrawer(),
            static::reopenDrawer(),
        ];
    }

    protected static function setOpening(): Action
    {
        return Action::make('setOpening')
            ->label(__('admin.setOpening'))
            ->icon(Heroicon::Bolt)
            ->visible(fn(CashDrawer $record) => static::isOpen($record) && static::canCashDrawer('CanSetOpeningBalance'))
            ->disabled(fn(CashDrawer $record) => static::isClosed($record) || !static::canCashDrawer('CanSetOpeningBalance'))
            ->schema([
                PriceInput::make('opening_balance')
                    ->label(__('admin.opening_balance'))
                    ->required()
                    ->minValue(0),
            ])
            ->action(function (CashDrawer $record, array $data) {
                $newOpening = round((float)($data['opening_balance'] ?? 0), 2);
                $delta = round($newOpening - (float)$record->opening_balance, 2);

                $record->opening_balance = $newOpening;
                $record->current_balance = max(0, round((float)$record->current_balance + $delta, 2));
                $record->opened_at = $record->opened_at ?: now();
                $record->opened_by = $record->opened_by ?: auth()->id();
                $record->save();
            });
    }

    protected static function withdraw(): Action
    {
        return Action::make('withdraw')
            ->label(__('admin.withdraw'))
            ->icon(Heroicon::ArrowDownCircle)
            ->color('danger')
            ->visible(fn(CashDrawer $record) => static::isOpen($record) && static::canCashDrawer('CanWithdraw'))
            ->disabled(fn(CashDrawer $record) => $record->current_balance <= 0
                ? true
                : (static::isClosed($record)
                    || !static::canCashDrawer('CanSetOpeningBalance')))
            ->schema([
                PriceInput::make('amount')
                    ->label(__('admin.amount'))
                    ->minValue(0.01)
                    ->maxValue(fn(CashDrawer $record) => (float)($record->current_balance ?? 0)),

                Textarea::make('reason')
                    ->label(__('admin.reason'))
                    ->rows(3)
                    ->required(),
            ])
            ->action(fn(CashDrawer $record, array $data) => $record->applyMovement(
                'out',
                (float)($data['amount'] ?? 0),
                $data['reason'] ?? null,
                auth()->id(),
            ));
    }

    protected static function deposit(): Action
    {
        return Action::make('deposit')
            ->label(__('admin.deposit'))
            ->icon(Heroicon::ArrowUpCircle)
            ->color('success')
            ->visible(fn(CashDrawer $record) => static::isOpen($record) && static::canCashDrawer('CanDeposit'))
            ->disabled(fn(CashDrawer $record) => static::isClosed($record) || !static::canCashDrawer('CanDeposit'))
            ->schema([
                PriceInput::make('amount')
                    ->label(__('admin.amount'))
                    ->minValue(0.01),

                Textarea::make('reason')
                    ->label(__('admin.reason'))
                    ->required()
                    ->rows(2),
            ])
            ->action(fn(CashDrawer $record, array $data) => $record->applyMovement(
                'in',
                (float)($data['amount'] ?? 0),
                $data['reason'] ?? null,
                auth()->id(),
            ));
    }

    protected static function closeDrawer(): Action
    {
        return Action::make('closeDrawer')
            ->label(__('admin.close_drawer'))
            ->icon(Heroicon::LockClosed)
            ->color('warning')
            ->visible(fn(CashDrawer $record) => static::isOpen($record) && static::canCashDrawer('CanClose'))
            ->disabled(fn(CashDrawer $record) => static::isClosed($record) || !static::canCashDrawer('CanClose'))
            ->requiresConfirmation()
            ->modalHeading(__('admin.close_drawer'))
            ->modalDescription(__('admin.close_drawer_confirm'))
            ->schema([
                Textarea::make('note')
                    ->label(__('admin.reason'))
                    ->rows(2),
            ])
            ->action(function (CashDrawer $record) {
                $record->closed_at = now();
                $record->closed_by = auth()->id();
                $record->save();
            });
    }

    protected static function reopenDrawer(): Action
    {
        return Action::make('reopenDrawer')
            ->label(__('admin.reopen_drawer'))
            ->icon(Heroicon::LockOpen)
            ->color('info')
            ->visible(fn(CashDrawer $record) => static::isClosed($record) && static::canCashDrawer('CanReopen'))
            ->disabled(fn(CashDrawer $record) => static::isOpen($record) || !static::canCashDrawer('CanReopen'))
            ->requiresConfirmation()
            ->modalHeading(__('admin.reopen_drawer'))
            ->action(function (CashDrawer $record) {
                $record->closed_at = null;
                $record->closed_by = null;
                $record->save();
            });
    }
}
