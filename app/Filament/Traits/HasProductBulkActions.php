<?php

namespace App\Filament\Traits;

use App\Filament\Resources\Consignments\ConsignmentResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Forms\Components\PriceInput;
use App\Models\Consignment;
use App\Models\Product;
use App\Models\Service;
use App\Models\Status;
use App\Models\User;
use Exception;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

trait HasProductBulkActions
{
    protected static function bulkProductAbility(string $ability): string
    {
        return "{$ability}:Product";
    }

    /**
     * Central ability check for current user.
     */
    protected static function canAbilityBulk(string $ability): bool
    {
        return canAbility(static::bulkProductAbility($ability));
    }

    /**
     * Usage:
     * ->bulkActions(static::productBulkActions())
     */
    public static function productBulkActions(): array
    {
        return [
            static::bulkUpdateProductStatusAction(),
            static::bulkAddToConsignmentAction(),
            static::bulkAddToServiceAction(),
            DeleteBulkAction::make()->visible(canAbility('Delete:Product')),
        ];
    }

    protected static function bulkUpdateProductStatusAction(): BulkAction
    {
        return BulkAction::make('bulkUpdateProductStatus')
            ->label(__('admin.update_product_status'))
            ->icon(Heroicon::ArrowPath)
            ->color('info')
            ->visible(fn() => static::canAbilityBulk('ViewProductStatusBulkUpdate'))
            ->schema([
                Select::make('status_id')
                    ->label(__('admin.status'))
                    ->options(fn() => Status::query()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required(),
            ])
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, array $data): void {
                $statusId = (int)($data['status_id'] ?? 0);
                if ($statusId <= 0) {
                    return;
                }

                $ids = resolveSelectedIdsFromRequest($records);

                DB::transaction(function () use ($ids, $statusId) {
                    Product::query()
                        ->whereIn('id', $ids)
                        ->update(['status_id' => $statusId]);
                });

                Notification::make()
                    ->title(__('admin.updated_successfully'))
                    ->success()
                    ->send();
            });
    }

    protected static function bulkAddToConsignmentAction(): BulkAction
    {
        return BulkAction::make('add_to_consignment')
            ->label(__('admin.add_to_consignment'))
            ->icon(Heroicon::ClipboardDocumentList)
            ->visible(fn() => static::canAbilityBulk('BulkAttachConsignmentProducts'))
            ->schema([
                Toggle::make('create_new')
                    ->label(__('admin.create_new_consignment'))
                    ->default(true)
                    ->live(),

                Toggle::make('create_new_user')
                    ->label(__('admin.create_new_user'))
                    ->default(false)
                    ->live()
                    ->visible(fn(Get $get) => $get('create_new') === true),

                Select::make('consignment_id')
                    ->label(__('admin.current_consignment'))
                    ->options(fn() => Consignment::query()
                        ->with('customer:id,name')
                        ->latest('id')
                        ->get()
                        ->mapWithKeys(fn($c) => [
                            $c->id => "#{$c->id} — " . ($c->customer?->name ?? '—'),
                        ])
                        ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => $get('create_new') === false)
                    ->required(fn($get) => $get('create_new') === false),

                Select::make('customer_id')
                    ->label(__('admin.client'))
                    ->options(fn() => User::query()
                        ->whereHas('roles', fn($q) => $q->where('id', 2))
                        ->get()
                        ->mapWithKeys(fn($u) => [
                            $u->id => trim($u->name . ' ' . ($u->surname ?? '')),
                        ])
                        ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => $get('create_new') === true && $get('create_new_user') === false)
                    ->required(fn($get) => $get('create_new') === true && $get('create_new_user') === false),

                TextInput::make('full_name')
                    ->label(__('admin.full_name'))
                    ->visible(fn($get) => $get('create_new') === true && $get('create_new_user') === true)
                    ->required(fn($get) => $get('create_new') === true && $get('create_new_user') === true),

                TextInput::make('mobile')
                    ->label(__('admin.mobile'))
                    ->visible(fn($get) => $get('create_new') === true && $get('create_new_user') === true)
                    ->required(fn($get) => $get('create_new') === true && $get('create_new_user') === true),

                PriceInput::make('advance_payment')
                    ->label(__('admin.advance_payment'))
                    ->default(0)
                    ->minValue(0),
            ])
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, array $data) {
                $ids = resolveSelectedIdsFromRequest($records);

                $products = Product::query()
                    ->whereIn('id', $ids)
                    ->get();

                try {
                    $inputPaid = max(0, (float) ($data['advance_payment'] ?? 0));

                    $consignment = null;
                    $customerId = null;

                    DB::transaction(function () use (
                        $data,
                        $products,
                        $inputPaid,
                        &$consignment,
                        &$customerId
                    ) {

                        if (($data['create_new'] ?? true) === false) {

                            $consignment = Consignment::query()
                                ->find($data['consignment_id'] ?? null);

                            if (! $consignment) {
                                throw new Exception(__('admin.consignment_not_found'));
                            }

                            $customerId = $consignment->customer_id;
                        }

                        if (($data['create_new'] ?? true) === true) {

                            if (($data['create_new_user'] ?? false) === true) {

                                $mobile = trim((string) ($data['mobile'] ?? ''));
                                $fullName = trim((string) ($data['full_name'] ?? ''));

                                $existingUser = User::query()
                                    ->where('mobile', $mobile)
                                    ->first();

                                if ($existingUser) {
                                    $customerId = $existingUser->id;
                                } else {

                                    $newUser = User::query()->create([
                                        'name' => $fullName,
                                        'mobile' => $mobile,
                                        'email' => $mobile . '@myline.ge',
                                        'password' => generateSecurePassword(),
                                    ]);

                                    $newUser->assignRole('კომპანია');

                                    $customerId = $newUser->id;
                                }

                            } else {

                                $customerId = (int) ($data['customer_id'] ?? 0);

                                if ($customerId <= 0) {
                                    throw new Exception(__('admin.client_not_found'));
                                }
                            }

                            $consignment = Consignment::query()->create([
                                'customer_id' => $customerId,
                                'created_by' => auth()->id(),
                                'advance_payment' => 0,
                                'subtotal' => 0,
                                'debt' => 0,
                                'is_paid' => false,
                            ]);
                        }

                        foreach ($products as $product) {

                            if (
                                $consignment->products()
                                    ->where('products.id', $product->id)
                                    ->exists()
                            ) {
                                continue;
                            }

                            $unitPrice = (float) ($product->retail_price ?? 0);

                            $consignment->products()->attach($product->id, [
                                'qty' => 1,
                                'unit_price' => $unitPrice,
                                'line_total' => round($unitPrice, 2),
                            ]);
                        }

                        if ($inputPaid > 0) {
                            $consignment->increment(
                                'advance_payment',
                                round($inputPaid, 2)
                            );
                        }

                        Product::query()
                            ->whereIn('id', $products->pluck('id'))
                            ->update([
                                'status_id' => 4,
                                'user_id' => $customerId,
                                'seller_id' => auth()->id(),
                                'created_at' => now(),
                            ]);

                        $consignment->recalculateTotals();
                        $consignment->refresh();
                    });

                    Notification::make()
                        ->title(__('admin.product_added_successfully'))
                        ->success()
                        ->send();

                    return redirect()->to(
                        ConsignmentResource::getUrl('edit', [
                            'record' => $consignment->id,
                        ])
                    );

                } catch (Throwable $e) {

                    report($e);

                    Notification::make()
                        ->title(__('admin.error'))
                        ->body(
                            config('app.debug')
                                ? $e->getMessage()
                                : __('admin.no_action')
                        )
                        ->danger()
                        ->send();

                    return null;
                }
            });
    }

    protected static function bulkAddToServiceAction(): BulkAction
    {
        return BulkAction::make('add_to_service')
            ->label(__('admin.add_to_service'))
            ->color('success')
            ->icon(Heroicon::Banknotes)
            ->visible(fn() => static::canAbilityBulk('BulkAttachServiceProducts'))
            ->schema([
                Toggle::make('create_new')
                    ->label(__('admin.create_new_service'))
                    ->default(true)
                    ->live(),

                Toggle::make('create_new_user')
                    ->label(__('admin.create_new_technic'))
                    ->default(false)
                    ->live()
                    ->visible(fn(Get $get) => $get('create_new') === true),

                Select::make('service_id')
                    ->label(__('admin.current_service'))
                    ->options(fn() => Service::query()
                        ->with('technic:id,name')
                        ->latest('id')
                        ->get()
                        ->mapWithKeys(fn($c) => [
                            $c->id => "#{$c->id} — " . ($c->technic?->name ?? '—'),
                        ])
                        ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => $get('create_new') === false)
                    ->required(fn($get) => $get('create_new') === false),

                Select::make('technic_id')
                    ->label(__('admin.technic'))
                    ->options(fn() => User::query()
                        ->whereHas('roles', fn($q) => $q->where('id', 4))
                        ->get()
                        ->mapWithKeys(fn($u) => [
                            $u->id => trim($u->name . ' ' . ($u->surname ?? '')),
                        ])
                        ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => $get('create_new') === true && $get('create_new_user') === false)
                    ->required(fn($get) => $get('create_new') === true && $get('create_new_user') === false),

                TextInput::make('full_name')
                    ->label(__('admin.full_name'))
                    ->visible(fn($get) => $get('create_new') === true && $get('create_new_user') === true)
                    ->required(fn($get) => $get('create_new') === true && $get('create_new_user') === true),

                TextInput::make('mobile')
                    ->label(__('admin.mobile'))
                    ->visible(fn($get) => $get('create_new') === true && $get('create_new_user') === true)
                    ->required(fn($get) => $get('create_new') === true && $get('create_new_user') === true),

                PriceInput::make('advance_payment')
                    ->label(__('admin.advance_payment'))
                    ->default(0)
                    ->minValue(0),
            ])
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, array $data) {
                $ids = resolveSelectedIdsFromRequest($records);

                $products = Product::query()
                    ->whereIn('id', $ids)
                    ->get();

                try {
                    $inputPaid = max(0, (float)($data['advance_payment'] ?? 0));
                    $service = null;

                    DB::transaction(function () use ($data, $products, $inputPaid, &$service) {

                        if (($data['create_new'] ?? true) === false) {
                            $service = Service::query()->find($data['service_id'] ?? null);
                            if (!$service) {
                                throw new Exception(__('admin.service_not_found'));
                            }
                        }
                        if (($data['create_new'] ?? true) === true) {

                            if (($data['create_new_user'] ?? false) === true) {
                                $mobile = (string)($data['mobile'] ?? '');
                                $fullName = (string)($data['full_name'] ?? '');

                                $newUser = User::query()
                                    ->create([
                                        'name' => $fullName,
                                        'mobile' => $mobile,
                                        'email' => $mobile . '@myline.ge',
                                        'password' => generateSecurePassword(),
                                    ]);

                                $newUser->assignRole('ტექნიკოსი');

                                $technicId = $newUser->id;
                            } else {
                                $technicId = (int)($data['technic_id'] ?? 0);
                                if ($technicId <= 0) {
                                    throw new Exception(__('admin.technic_not_found'));
                                }
                            }

                            $service = Service::query()
                                ->create([
                                    'technic_id' => $technicId,
                                    'created_by' => auth()->id(),
                                    'advance_payment' => 0,
                                    'subtotal' => 0,
                                    'debt' => 0,
                                    'is_paid' => false,
                                ]);
                        }

                        foreach ($products as $product) {
                            if ($service->products()->where('products.id', $product->id)->exists()) {
                                continue;
                            }

                            $unitPrice = (float)($product->retail_price ?? 0);

                            $service->products()->attach($product->id, [
                                'qty' => 1,
                                'unit_price' => $unitPrice,
                            ]);
                        }

                        if ($inputPaid > 0) {
                            $service->increment('advance_payment', $inputPaid);
                        }

                        Product::query()
                            ->whereIn('id', $products->pluck('id'))
                            ->update(['status_id' => 3]);
                    });

                    Notification::make()
                        ->title(__('admin.product_added_successfully'))
                        ->success()
                        ->send();

                    return redirect()->to(ServiceResource::getUrl('edit', [
                        'record' => $service->id,
                    ]));

                } catch (Throwable $e) {
                    Notification::make()
                        ->title(__('admin.error'))
                        ->body(config('app.debug') ? $e->getMessage() : __('admin.no_action'))
                        ->danger()
                        ->send();

                    return null;
                }
            });
    }
}
