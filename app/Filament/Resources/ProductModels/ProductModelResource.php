<?php

namespace App\Filament\Resources\ProductModels;

use App\Filament\Resources\ProductModels\Pages\CreateProductModel;
use App\Filament\Resources\ProductModels\Pages\EditProductModel;
use App\Filament\Resources\ProductModels\Pages\ListProductModels;
use App\Filament\Resources\ProductModels\Schemas\ProductModelForm;
use App\Filament\Resources\ProductModels\Tables\ProductModelsTable;
use App\Models\ProductModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductModelResource extends Resource
{
    protected static ?string $model = ProductModel::class;

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Sparkles;

    public static function form(Schema $schema): Schema
    {
        return ProductModelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductModelsTable::configure($table);
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
            'index' => ListProductModels::route('/'),
            'create' => CreateProductModel::route('/create'),
            'edit' => EditProductModel::route('/{record}/edit'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.model');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.model');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.model');
    }

    public static function getLabel(): string
    {
        return __('admin.model');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.model');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.model');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.settings');
    }
}
