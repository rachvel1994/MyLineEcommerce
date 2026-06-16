<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Traits\HasProductHeaderActions;
use App\Filament\Widgets\ProductListWidget;
use App\Filament\Widgets\ProductPageWidget;
use App\Models\Product;
use App\Models\Status;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class ListProducts extends ListRecords
{
    use HasProductHeaderActions;

    protected string $view = 'filament.pages.list-products';

    protected static string $resource = ProductResource::class;

    protected Width|string|null $maxContentWidth = 'full';

    protected ?string $previousTab = null;

    protected function getHeaderActions(): array
    {
        return [
            ...static::productHeaderActions()
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductListWidget::class,
            ProductPageWidget::class
        ];
    }


    /**
     * ✅ UI RESET (optional but recommended)
     */
    public function updatedActiveTab(): void
    {
        $this->tableFilters = [];
        $this->resetTableFiltersForm();
        $this->reset('tableSearch');
        $this->resetPage();
    }

    public function getTabs(): array
    {
        $tabs = [];

        $statuses = Status::query()
            ->where('show_in_product', true)
            ->orderBy('sort_order')
            ->get();

        $tabs[__('admin.all')] = Tab::make(__('admin.all'))
            ->badge(
                Product::query()
                    ->whereNotIn('status_id', [4, 6, 10])
                    ->count()
            )
            ->modifyQueryUsing(fn(Builder $query) => $query->whereNotIn('status_id', [4, 6, 10]));

        foreach ($statuses as $status) {

            $tabs[$status->name] = Tab::make($status->name)
                ->badge(
                    Product::query()
                        ->where('status_id', $status->id)
                        ->count()
                )
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status_id', $status->id));
        }

        $tabs[__('admin.no_status')] = Tab::make(__('admin.no_status'))
            ->badge(
                Product::query()
                    ->whereNull('status_id')
                    ->count()
            )
            ->modifyQueryUsing(fn(Builder $query) => $query->whereNull('status_id'));

        return $tabs;
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'md' => 1,
            'xl' => 1,
        ];
    }

    #[On('modelStrictSearch')]
    public function modelStrictSearch(?int $id = null): void
    {
        if (blank($id)) {
            unset($this->tableFilters['model_id']);
        } else {
            unset($this->tableFilters['product_name']);
            unset($this->tableFilters['strict_search']);

            $this->tableFilters['model_id'] = [
                'value' => $id,
            ];

            $this->activeTab = 'ყველა';
        }

        $this->resetPage();
        $this->dispatch('$refresh')->self();
    }

    #[On('modelSearch')]
    public function modelSearch(?string $name = null): void
    {
        if (blank($name)) {
            unset($this->tableFilters['product_name']);
        } else {
            unset($this->tableFilters['model_id']);
            unset($this->tableFilters['strict_search']);

            $this->tableFilters['product_name'] = [
                'name' => $name,
            ];

            $this->activeTab = 'ყველა';
        }

        $this->resetPage();
        $this->dispatch('$refresh')->self();
    }

    private function resetConflictingFilters(): void
    {
        unset(
            $this->tableFilters['model_id'],
            $this->tableFilters['product_name'],
            $this->tableFilters['strict_search']
        );
    }


    public function updatedTableFilters(): void
    {
        $statusId = $this->tableFilters['status_id']['value'] ?? null;
        $sku = $this->tableFilters['product']['sku'] ?? null;
        $paymentId = $this->tableFilters['payment_id']['value'] ?? null;

        $status = null;

        if ($sku) {
            $product = Product::select('status_id')
                ->where('sku', $sku)
                ->first();

            if ($product) {
                $status = Status::find($product->status_id);
            }
        }

        if (!$status && $statusId) {
            $status = Status::find($statusId);
        }

        if ($paymentId) {
            $status = Status::find(4);
        }

        if (!$status) {
            return;
        }

        $this->activeTab = $status->name;
    }

    public function updatedTableSearch(): void
    {
        $search = trim((string)$this->tableSearch);

        if (blank($search)) {
            return;
        }

        $product = Product::with(['status', 'model'])
            ->where(function ($query) use ($search) {
                $query->where('sku', 'like', "%{$search}%")
                    ->orWhereHas('model', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
            })->first();

        if (!$product) {
            return;
        }

        $this->activeTab = $product->status?->name;

        $this->resetPage();
        $this->dispatch('$refresh')->self();
    }
}
