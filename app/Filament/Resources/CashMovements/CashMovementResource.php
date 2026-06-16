<?php

namespace App\Filament\Resources\CashMovements;

use App\Filament\Resources\CashMovements\Pages\CreateCashMovement;
use App\Filament\Resources\CashMovements\Pages\EditCashMovement;
use App\Filament\Resources\CashMovements\Pages\ListCashMovements;
use App\Filament\Resources\CashMovements\Schemas\CashMovementForm;
use App\Filament\Resources\CashMovements\Tables\CashMovementsTable;
use App\Models\CashMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CashMovementResource extends Resource
{
    protected static ?string $model = CashMovement::class;

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowPath;

    protected static ?string $recordTitleAttribute = 'amount';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return CashMovementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashMovementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashMovements::route('/'),
            'create' => CreateCashMovement::route('/create'),
            'edit' => EditCashMovement::route('/{record}/edit'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.cash_history');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.cash_history');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.cash_history');
    }

    public static function getLabel(): string
    {
        return __('admin.cash_history');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.cash_history');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.cash_history');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.box_office');
    }
}
