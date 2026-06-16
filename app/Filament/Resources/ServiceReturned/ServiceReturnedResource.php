<?php

namespace App\Filament\Resources\ServiceReturned;


use App\Filament\Resources\ServiceReturned\Pages\ListServiceReturned;
use App\Filament\Resources\ServiceReturned\Schemas\ServiceReturnedForm;
use App\Filament\Resources\ServiceReturned\Tables\ServiceReturnedTable;
use App\Models\ServiceProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ServiceReturnedResource extends Resource
{
    protected static ?string $model = ServiceProduct::class;

    protected static ?int $navigationSort = 23;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'sku';

    public static function form(Schema $schema): Schema
    {
        return ServiceReturnedForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceReturnedTable::configure($table);
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
            'index' => ListServiceReturned::route('/'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.service_returned');
    }
    

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.service_returned');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.service_returned');
    }

    public static function getLabel(): string
    {
        return __('admin.service_returned');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.service_returned');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.service_returned');
    }

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();

        if ($user && $user->roles->contains('id', 4)) {
            return null;
        }

        return __('admin.service');
    }

    public static function getNavigationBadge(): ?string
    {
        return ServiceProduct::query()->with(['model', 'battery', 'color', 'condition'])
            ->where('status_id', 12)
            ->when(!canAbility('ShowAllProducts:User'), function ($q) {
                $q->whereHas('services', function ($serviceQuery) {
                    $serviceQuery->where('technic_id', auth()->id());
                });
            })->count() ?? 0;
    }
}
