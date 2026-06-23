<?php

namespace App\Filament\Widgets;

use App\Models\ProductModel as DeviceModel;
use App\Models\Status;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class ProductListWidget extends Widget
{
    use HasWidgetShield;

    private const CACHE_KEY = 'filament.product-list-widget.stock-models';

    protected string $view = 'filament.widgets.product-list-widget';

    public ?array $modelGroups = [];

    public array $productModelIds = [];

    protected $listeners = ['refreshProductList' => 'refreshProductList'];

    public function mount(): void
    {
        $this->loadProductData();
    }

    public function refreshProductList(): void
    {
        Cache::forget(self::CACHE_KEY);

        $this->loadProductData();
    }

    private function loadProductData(): void
    {
        $data = Cache::remember(self::CACHE_KEY, now()->addMinute(), fn (): array => $this->buildProductData());

        $this->modelGroups = $data['modelGroups'];
        $this->productModelIds = $data['productModelIds'];
    }

    /**
     * @return array{modelGroups: array<int, array{id: int, label: string, models: array<int, array{id: int, name: string}>}>, productModelIds: array<int, int>}
     */
    private function buildProductData(): array
    {
        $statusIds = Status::query()
            ->whereIn('name', ['საწყობშია', 'მაღაზიაშია'])
            ->pluck('id');

        $models = DeviceModel::query()
            ->with(['parent:id,name,parent_id'])
            ->whereHas('products', fn ($q) => $q->whereIn('status_id', $statusIds))
            ->select(['id', 'name', 'parent_id'])
            ->orderBy('name')
            ->get();

        $grouped = [];

        foreach ($models as $model) {
            $modelName = trim((string) $model->name);

            if ($modelName === '') {
                continue;
            }

            if ($model->parent_id && $model->parent) {
                $parentName = trim((string) $model->parent->name);

                if ($parentName === '') {
                    continue;
                }

                $groupKey = mb_strtoupper($parentName);

                if (! isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'id' => $model->parent->id,
                        'label' => $parentName,
                        'models' => [],
                    ];
                }

                $grouped[$groupKey]['models'][$model->id] = [
                    'id' => $model->id,
                    'name' => $modelName,
                ];
            } else {
                $groupKey = mb_strtoupper($modelName);

                if (! isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'id' => $model->id,
                        'label' => $modelName,
                        'models' => [],
                    ];
                }

                $grouped[$groupKey]['models'][$model->id] = [
                    'id' => $model->id,
                    'name' => $modelName,
                ];
            }
        }

        ksort($grouped, SORT_NATURAL);

        foreach ($grouped as &$group) {
            $group['models'] = collect($group['models'])
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->toArray();
        }

        unset($group);

        return [
            'modelGroups' => array_values($grouped),
            'productModelIds' => $models->pluck('id')->flip()->toArray(),
        ];
    }

    public function getHeading(): ?string
    {
        return __('admin.product_status_list');
    }
}
