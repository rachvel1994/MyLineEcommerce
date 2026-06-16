<?php

namespace App\Filament\Resources\RepairInformation;

use App\Filament\Resources\RepairInformation\Pages\CreateRepairInformation;
use App\Filament\Resources\RepairInformation\Pages\EditRepairInformation;
use App\Filament\Resources\RepairInformation\Pages\ListRepairInformation;
use App\Filament\Resources\RepairInformation\Schemas\RepairInformationForm;
use App\Filament\Resources\RepairInformation\Tables\RepairInformationTable;
use App\Models\RepairInformation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RepairInformationResource extends Resource
{
    protected static ?string $model = RepairInformation::class;

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::WrenchScrewdriver;

    public static function form(Schema $schema): Schema
    {
        return RepairInformationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RepairInformationTable::configure($table);
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
            'index' => ListRepairInformation::route('/'),
            'create' => CreateRepairInformation::route('/create'),
            'edit' => EditRepairInformation::route('/{record}/edit'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.repair_information');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.repair_information');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.repair_information');
    }

    public static function getLabel(): string
    {
        return __('admin.repair_information');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.repair_information');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.repair_information');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.settings');
    }
}
