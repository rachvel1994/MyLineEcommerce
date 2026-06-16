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

class ProductPageWidget extends Widget implements HasForms
{
    use InteractsWithForms, HasWidgetShield;

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
        $from = $this->filters['created_at']['created_from'] ?? now()->startOfMonth()->startOfDay();
        $to = $this->filters['created_at']['created_until'] ?? now()->endOfMonth()->endOfDay();

        $productQuery = Product::query();

        $stats = [];

        $statuses = Status::query()
            ->where('is_active', 1)
            ->where('show_in_product', 1)
            ->orderBy('id')
            ->get();

        foreach ($statuses as $status) {

            $count = (clone $productQuery)
                ->where('status_id', $status->id)
                ->when($status->id == 4, function ($q) use ($from, $to) {
                    $q->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
                        ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to));
                })
                ->count();

            $stats[] = [
                'key' => 'status_' . $status->id,
                'label' => $status->name,
                'value' => $count,
                'bg' => $status->color ?: 'bg-primary',
                'text' => 'text-white',
                'icon' => 'fa-solid fa-box-open',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Customers / Companies
        |--------------------------------------------------------------------------
        */

        $countCustomers = User::query()
            ->whereHas('roles', fn($q) => $q->where('id', 3))
            ->count();

        $countCompanies = User::query()
            ->whereHas('roles', fn($q) => $q->where('id', 2))
            ->count();

        /*
        |--------------------------------------------------------------------------
        | SOLD STATS (FIXED)
        |--------------------------------------------------------------------------
        */

        $soldStatusId = Status::query()
            ->where('name', 'გაყიდულია')
            ->value('id');

        $expense = Expense::query()
            ->whereBetween('created_at', [now()->startOfMonth()->startOfDay(), now()->endOfMonth()->endOfDay()])->sum('amount');

        $serviceExpense = ServiceRepairHistory::query()
            ->whereBetween('created_at', [now()->startOfMonth()->startOfDay(), now()->endOfMonth()->endOfDay()])->sum('repair_price');

        $soldQuery = $soldStatusId
            ? (clone $productQuery)->where('status_id', $soldStatusId)
            : (clone $productQuery)->whereRaw('1=0');

        $soldQuery->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to));

        $totalRevenue = (clone $soldQuery)->sum('sale_price');
        $totalCost = (clone $soldQuery)->sum('price');

        $netProfit = $totalRevenue - $totalCost - $expense - $serviceExpense;

        /*
        |--------------------------------------------------------------------------
        | Admin Only Stats
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->hasRole('ადმინისტრატორი')) {

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
