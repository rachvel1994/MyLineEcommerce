<?php

namespace App\Filament\Resources\Batteries;

use App\Filament\Resources\Batteries\Pages\CreateBattery;
use App\Filament\Resources\Batteries\Pages\EditBattery;
use App\Filament\Resources\Batteries\Pages\ListBatteries;
use App\Filament\Resources\Batteries\Schemas\BatteryForm;
use App\Filament\Resources\Batteries\Tables\BatteriesTable;
use App\Models\Battery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BatteryResource extends Resource
{
    protected static ?string $model = Battery::class;
    protected static ?int $navigationSort = 5;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::PercentBadge;

    public static function form(Schema $schema): Schema
    {
        return BatteryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BatteriesTable::configure($table);
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
            'index' => ListBatteries::route('/'),
            'create' => CreateBattery::route('/create'),
            'edit' => EditBattery::route('/{record}/edit'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.battery');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.battery');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.battery');
    }

    public static function getLabel(): string
    {
        return __('admin.battery');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.battery');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.battery');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.settings');
    }
}
