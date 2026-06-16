<?php

namespace App\Filament\Resources\AccessoryOrders;

use App\Filament\Resources\AccessoryOrders\Pages\CreateAccessoryOrders;
use App\Filament\Resources\AccessoryOrders\Pages\EditAccessoryOrders;
use App\Filament\Resources\AccessoryOrders\Pages\ListAccessoryOrders;
use App\Filament\Resources\AccessoryOrders\Schemas\AccessoryOrdersForm;
use App\Filament\Resources\AccessoryOrders\Tables\AccessoryOrdersTable;
use App\Models\AccessoryOrders;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccessoryOrdersResource extends Resource
{
    protected static ?string $model = AccessoryOrders::class;

	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $recordTitleAttribute = 'order_id';

    public static function form(Schema $schema): Schema
    {
        return AccessoryOrdersForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccessoryOrdersTable::configure($table);
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
            'index' => ListAccessoryOrders::route('/'),
            'create' => CreateAccessoryOrders::route('/create'),
            'edit' => EditAccessoryOrders::route('/{record}/edit'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.accessory_order');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.accessory_order');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.accessory_order');
    }

    public static function getLabel(): string
    {
        return __('admin.accessory_order');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.accessory_order');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.accessory_order');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.accessory');
    }
}
