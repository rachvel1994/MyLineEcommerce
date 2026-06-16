<?php

namespace App\Providers;

use App\Models\AccessoryOrderItem;
use App\Models\CashDrawer;
use App\Models\ConsignmentPriceChange;
use App\Models\Product;
use App\Models\ServiceRepairHistory;
use App\Models\User;
use App\Observers\CashDrawerObserver;
use App\Observers\AccessoryOrderItemObserver;
use App\Observers\ConsignmentPriceChangeObserver;
use App\Observers\ServiceRepairHistoryObserver;
use App\Observers\ProductObserver;
use App\Observers\UserObserver;
use BezhanSalleh\FilamentShield\FilamentShield;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;
use Livewire\Blaze\Blaze;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Blaze::optimize()->in(
            resource_path('views/*'),
            fold: true
        );
        FilamentAsset::register([
            Css::make('custom-stylesheet-theme', __DIR__ . '/../../resources/css/custom.css')->loadedOnRequest(),
        ]);

        Table::configureUsing(function (Table $table): void {
            $table
                ->paginationPageOptions([10, 25, 50, 100, 'all'])
                ->defaultPaginationPageOption(50);
        });
        Product::observe(ProductObserver::class);
        CashDrawer::observe(CashDrawerObserver::class);
        AccessoryOrderItem::observe(AccessoryOrderItemObserver::class);
        User::observe(UserObserver::class);
        ConsignmentPriceChange::observe(ConsignmentPriceChangeObserver::class);
        ServiceRepairHistory::observe(ServiceRepairHistoryObserver::class);
        (new FilamentShield)->prohibitDestructiveCommands($this->app->isProduction());
    }
}
