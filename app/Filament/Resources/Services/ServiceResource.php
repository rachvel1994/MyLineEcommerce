<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Services\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\Services\RelationManagers\RepairRelationManager;
use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Filament\Resources\Services\Tables\ServicesTable;
use App\Models\Service;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog8Tooth;

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function canEdit(Model $record): bool
    {
        if (canAbility('ShowAllProducts:User')) {
            return true;
        }

        return $record->technic_id === auth()->id();
    }

    public static function table(Table $table): Table
    {
        return ServicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        $can = canAbility('ViewRepairHistories:Service');

        if ($can) {
            return [
                ProductsRelationManager::class,
                RepairRelationManager::class,
            ];
        } else {
            return [
                ProductsRelationManager::class,
            ];
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.service');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.service');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.service');
    }

    public static function getLabel(): string
    {
        return __('admin.service');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.service');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.service');
    }

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();

        if ($user && $user->roles->contains('id', 4)) {
            return null;
        }

        return __('admin.service');
    }
}
