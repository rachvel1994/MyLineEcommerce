<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Product;
use App\Models\ServiceRepairHistory;
use App\Models\Status;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class ProductPageWidget extends Widget implements HasForms
{
    use HasWidgetShield, InteractsWithForms;

    protected string $view = 'filament.widgets.product-page-widget';

    protected int|string|array $columnSpan = 'full';

    public ?array $filters = [];

    protected static bool $isLazy = true;

    public function getHeading(): string
    {
        return __('admin.product_overview');
    }

    public function label(): string
    {
        return __('admin.overview');
    }

    public function resetFilters(): void
    {
        $this->filters = [];
        $this->form->fill($this->filters);
        $this->dispatch('$refresh');
    }

    protected function getFormStatePath(): ?string
    {
        return 'filters';
    }

    public function getStats(): array
    {
        $from = filled($this->filters['created_at']['created_from'] ?? null)
            ? Carbon::parse($this->filters['created_at']['created_from'])->startOfDay()
            : now()->startOfMonth()->startOfDay();
        $to = filled($this->filters['created_at']['created_until'] ?? null)
            ? Carbon::parse($this->filters['created_at']['created_until'])->endOfDay()
            : now()->endOfMonth()->endOfDay();

        $stats = [];

        $statuses = Status::query()
            ->where('is_active', 1)
            ->where('show_in_product', 1)
            ->orderBy('id')
            ->get();

        $statusIds = $statuses->pluck('id')->all();
        $soldStatusId = $statuses->firstWhere('name', 'გაყიდულია')?->id ?? 4;
        $statusCounts = $statusIds === []
            ? collect()
            : Product::query()
                ->selectRaw('status_id, COUNT(*) as aggregate')
                ->whereIn('status_id', $statusIds)
                ->where(function ($query) use ($soldStatusId, $from, $to) {
                    $query
                        ->where('status_id', '!=', $soldStatusId)
                        ->orWhere(function ($query) use ($soldStatusId, $from, $to) {
                            $query
                                ->where('status_id', $soldStatusId)
                                ->whereBetween('created_at', [$from, $to]);
                        });
                })
                ->groupBy('status_id')
                ->pluck('aggregate', 'status_id');

        foreach ($statuses as $status) {
            $stats[] = [
                'key' => 'status_'.$status->id,
                'label' => $status->name,
                'value' => (int) $statusCounts->get($status->id, 0),
                'bg' => $status->color ?: 'bg-primary',
                'text' => 'text-white',
                'icon' => 'fa-solid fa-box-open',
            ];
        }

        if (auth()->user()?->hasRole('ადმინისტრატორი')) {
            $countCustomers = User::query()
                ->whereHas('roles', fn ($q) => $q->where('id', 3))
                ->count();

            $countCompanies = User::query()
                ->whereHas('roles', fn ($q) => $q->where('id', 2))
                ->count();

            $expense = Expense::query()
                ->whereBetween('created_at', [now()->startOfMonth()->startOfDay(), now()->endOfMonth()->endOfDay()])
                ->sum('amount');

            $serviceExpense = ServiceRepairHistory::query()
                ->whereBetween('created_at', [now()->startOfMonth()->startOfDay(), now()->endOfMonth()->endOfDay()])
                ->sum('repair_price');

            $soldQuery = Product::query()
                ->where('status_id', $soldStatusId)
                ->whereBetween('created_at', [$from, $to]);

            $totalRevenue = (clone $soldQuery)->sum('sale_price');
            $totalCost = (clone $soldQuery)->sum('price');
            $netProfit = $totalRevenue - $totalCost - $expense - $serviceExpense;

            $stats[] = [
                'key' => 'customers',
                'label' => __('admin.active_users'),
                'value' => $countCustomers,
                'bg' => '#ff5722',
                'text' => 'text-dark',
                'icon' => 'fa-solid fa-users',
            ];

            $stats[] = [
                'key' => 'companies',
                'label' => __('admin.b2b_clients'),
                'value' => $countCompanies,
                'bg' => '#2196f3',
                'text' => 'text-white',
                'icon' => 'fa-solid fa-briefcase',
            ];

            $stats[] = [
                'key' => 'revenue',
                'label' => __('admin.income'),
                'value' => money($totalRevenue),
                'bg' => '#4caf50',
                'text' => 'text-white',
                'icon' => 'fa-solid fa-sack-dollar',
            ];

            $stats[] = [
                'key' => 'profit',
                'label' => __('admin.profit'),
                'value' => money($netProfit),
                'bg' => '#d4af37',
                'text' => 'text-white',
                'icon' => 'fa-solid fa-chart-line',
            ];
        }

        return $stats;
    }
}
