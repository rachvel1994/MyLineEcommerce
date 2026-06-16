<?php

namespace App\Filament\Resources\Storages;

use App\Filament\Resources\Storages\Pages\CreateStorage;
use App\Filament\Resources\Storages\Pages\EditStorage;
use App\Filament\Resources\Storages\Pages\ListStorages;
use App\Filament\Resources\Storages\Schemas\StorageForm;
use App\Filament\Resources\Storages\Tables\StoragesTable;
use App\Models\Storage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StorageResource extends Resource
{
    protected static ?string $model = Storage::class;

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ServerStack;

    public static function form(Schema $schema): Schema
    {
        return StorageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoragesTable::configure($table);
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
            'index' => ListStorages::route('/'),
            'create' => CreateStorage::route('/create'),
            'edit' => EditStorage::route('/{record}/edit'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.storage');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.storage');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.storage');
    }

    public static function getLabel(): string
    {
        return __('admin.storage');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.storage');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.storage');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.settings');
    }
}
