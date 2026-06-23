<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Filament\Resources\Products\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\Products\RelationManagers\RepairHistoriesRelationManager;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;
use Livewire\Attributes\Url;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingStorefront;

    #[Url]
    public string $filters = '';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                CommentsEntry::make('comments')
                    ->label(__('admin.service_comment'))
                    ->mentionables(User::query()
                        ->whereHas('roles', function ($query) {
                            $query->where('id', 1);
                        })->get())
                    ->perPage(8)
                    ->loadMoreIncrementsBy(8)
                    ->columnSpanFull()
                    ->loadMoreLabel(__('admin.show_older')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
            RepairHistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
            'view' => ViewProduct::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'battery',
                'color',
                'condition',
                'hearAbout',
                'information',
                'model',
                'payments.payment',
                'services',
                'status',
                'storage',
                'user',
            ])
            ->orderBy(
                Status::query()->select('sort_order')
                    ->whereColumn('statuses.id', 'products.status_id')
            )
            ->orderByDesc('products.created_at');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.product');
    }

    public static function getTitleCasePluralModelLabel(): string
    {
        return __('admin.product');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.product');
    }

    public static function getLabel(): string
    {
        return __('admin.product');
    }

    public static function getTitleCaseModelLabel(): string
    {
        return __('admin.product');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.product');
    }
}
