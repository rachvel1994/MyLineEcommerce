<?php

namespace App\Filament\Resources\Guarantees;

use App\Filament\Resources\Guarantees\Pages\CreateGuarantee;
use App\Filament\Resources\Guarantees\Pages\EditGuarantee;
use App\Filament\Resources\Guarantees\Pages\ListGuarantees;
use App\Filament\Resources\Guarantees\Schemas\GuaranteeForm;
use App\Filament\Resources\Guarantees\Tables\GuaranteesTable;
use App\Models\Guarantee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GuaranteeResource extends Resource
{
    protected static ?string $model = Guarantee::class;
    protected static ?int $navigationSort = 5;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Pencil;

    public static function form(Schema $schema): Schema
    {
        return GuaranteeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuaranteesTable::configure($table);
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
            'index' => ListGuarantees::route('/'),
            'create' => CreateGuarantee::route('/create'),
            'edit' => EditGuarantee::route('/{record}/edit'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.guarantee');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.guarantee');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.guarantee');
    }

    public static function getLabel(): string
    {
        return __('admin.guarantee');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.guarantee');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.guarantee');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.settings');
    }
}
