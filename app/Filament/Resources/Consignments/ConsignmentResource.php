<?php

namespace App\Filament\Resources\Consignments;

use App\Filament\Resources\Consignments\Pages\CreateConsignment;
use App\Filament\Resources\Consignments\Pages\EditConsignment;
use App\Filament\Resources\Consignments\Pages\ListConsignments;
use App\Filament\Resources\Consignments\RelationManagers\AccessoriesRelationManager;
use App\Filament\Resources\Consignments\RelationManagers\PriceChangesRelationManager;
use App\Filament\Resources\Consignments\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\Consignments\Schemas\ConsignmentForm;
use App\Filament\Resources\Consignments\Tables\ConsignmentsTable;
use App\Models\Consignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ConsignmentResource extends Resource
{
    protected static ?string $model = Consignment::class;

    protected static ?int $navigationSort = 15;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    public static function form(Schema $schema): Schema
    {
        return ConsignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConsignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        $can = canAbility('ViewPriceChanges:Consignment');

        if ($can) {
            return [
                ProductsRelationManager::class,
                AccessoriesRelationManager::class,
                PriceChangesRelationManager::class,
            ];
        } else {
            return [
                ProductsRelationManager::class,
                AccessoriesRelationManager::class,
            ];
        }
    }


    public static function getPages(): array
    {
        return [
            'index' => ListConsignments::route('/'),
            'create' => CreateConsignment::route('/create'),
            'edit' => EditConsignment::route('/{record}/edit'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.consignment');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.consignment');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.consignment');
    }

    public static function getLabel(): string
    {
        return __('admin.consignment');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.consignment');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.consignment');
    }
}
