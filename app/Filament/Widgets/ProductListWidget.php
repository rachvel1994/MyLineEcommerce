<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\ProductModel as DeviceModel;
use App\Models\Status;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class ProductListWidget extends Widget
{
    use HasWidgetShield;

    protected string $view = 'filament.widgets.product-list-widget';

    public ?array $modelGroups = [];

    public array $productModelIds = [];

    protected $listeners = ['refreshProductList' => '$refresh'];

    public function mount(): void
    {
        $this->loadProductData();
    }

    private function loadProductData(): void
    {
        $statusIds = Status::query()
            ->whereIn('name', ['საწყობშია', 'მაღაზიაშია'])
            ->pluck('id');

        $this->productModelIds = Product::query()
            ->whereNotNull('model_id')
            ->whereIn('status_id', $statusIds)
            ->distinct()
            ->pluck('model_id')
            ->flip()
            ->toArray();

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

                if (!isset($grouped[$groupKey])) {
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

                if (!isset($grouped[$groupKey])) {
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

        $this->modelGroups = array_values($grouped);
    }

    public function getHeading(): ?string
    {
        return __('admin.product_status_list');
    }
}
