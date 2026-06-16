<?php

namespace App\Filament\Resources\Conditions;

use App\Filament\Resources\Conditions\Pages\CreateCondition;
use App\Filament\Resources\Conditions\Pages\EditCondition;
use App\Filament\Resources\Conditions\Pages\ListConditions;
use App\Filament\Resources\Conditions\Schemas\ConditionForm;
use App\Filament\Resources\Conditions\Tables\ConditionsTable;
use App\Models\Condition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ConditionResource extends Resource
{
    protected static ?string $model = Condition::class;
    protected static ?int $navigationSort = 5;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShieldCheck;

    public static function form(Schema $schema): Schema
    {
        return ConditionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConditionsTable::configure($table);
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
            'index' => ListConditions::route('/'),
            'create' => CreateCondition::route('/create'),
            'edit' => EditCondition::route('/{record}/edit'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.condition');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.condition');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.condition');
    }

    public static function getLabel(): string
    {
        return __('admin.condition');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.condition');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.condition');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.settings');
    }
}
