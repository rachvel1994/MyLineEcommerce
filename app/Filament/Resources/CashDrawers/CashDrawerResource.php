<?php

namespace App\Filament\Resources\CashDrawers;

use App\Filament\Resources\CashDrawers\Pages\CreateCashDrawer;
use App\Filament\Resources\CashDrawers\Pages\EditCashDrawer;
use App\Filament\Resources\CashDrawers\Pages\ListCashDrawers;
use App\Filament\Resources\CashDrawers\RelationManagers\MovementsRelationManager;
use App\Filament\Resources\CashDrawers\Schemas\CashDrawerForm;
use App\Filament\Resources\CashDrawers\Tables\CashDrawersTable;
use App\Models\CashDrawer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CashDrawerResource extends Resource
{
    protected static ?string $model = CashDrawer::class;

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Wallet;

    public static function form(Schema $schema): Schema
    {
        return CashDrawerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashDrawersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MovementsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashDrawers::route('/'),
            'create' => CreateCashDrawer::route('/create'),
            'edit' => EditCashDrawer::route('/{record}/edit'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.box_office');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.box_office');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.box_office');
    }

    public static function getLabel(): string
    {
        return __('admin.box_office');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.box_office');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.box_office');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.box_office');
    }
}
