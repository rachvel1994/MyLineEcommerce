<?php

namespace App\Filament\Traits;

use App\Forms\Components\NumericInput;
use App\Forms\Components\PriceInput;
use App\Jobs\GenerateGuaranteePdfAndSendMailJob;
use App\Models\Accessory;
use App\Models\AccessoryOrders;
use App\Models\Delivery;
use App\Models\Guarantee;
use App\Models\HearAbout;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RepairInformation;
use App\Models\Status;
use App\Models\User;
use App\Services\ProductPaymentCashDrawerService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

trait HasProductHeaderActions
{
    /**
     * Ability naming strategy.
     * Example: CanViewPdf -> CanViewPdf:Product
     */
    protected static function productAbility(string $ability): string
    {
        return "{$ability}:Product";
    }

    /**
     * Central ability check for current user.
     */
    protected static function canAbility(string $ability): bool
    {
        return canAbility(static::productAbility($ability));
    }

    protected static function exportProducts(): Action
    {
        return Action::make('export')
            ->color('info')
            ->label(__('admin.excel_export'))
            ->icon(Heroicon::ArrowDownTray)
            ->visible(fn() => static::canAbility('CanDownloadExcel'))
            ->schema([
                DatePicker::make('from_date')
                    ->label(__('admin.from_date')),

                DatePicker::make('to_date')
                    ->label(__('admin.to_date')),

                Select::make('status_id')
                    ->label(__('admin.status'))
                    ->searchable()
                    ->options(Status::query()->orderBy('sort_order')->pluck('name', 'id')),

                Select::make('company_id')
                    ->label(__('admin.company'))
                    ->searchable()
                    ->options(companies()),
            ])
            ->action(function (array $data) {
                $query = http_build_query(array_filter($data, fn($value) => filled($value)));

                return redirect()->to(route('products.export') . ($query ? '?' . $query : ''));
            })
            ->openUrlInNewTab();
    }

    protected static function createGuarantee(): Action
    {
        return Action::make('create_guarantee')
            ->color('success')
            ->icon(Heroicon::UserPlus)
            ->visible(fn() => static::canAbility('CanCreateGuarantee'))
            ->label(__('admin.create_guarantee'))
            ->schema(function () {
                $normalizeItems = function (array $items): array {
                    foreach ($items as $i => $row) {
                        $price = (float)($row['price'] ?? 0);
                        $qty = max(1, (int)($row['quantity'] ?? 1));

                        $items[$i]['price'] = $price;
                        $items[$i]['quantity'] = $qty;
                        $items[$i]['total_price'] = round($price * $qty, 2);
                    }

                    return $items;
                };

                $recalculateTotalsFromItems = function (Get $get, Set $set, array $items, string $prefix = '') use ($normalizeItems): float {
                    $items = $normalizeItems($items);

                    $basePrice = (float)($get($prefix . 'price') ?? 0);
                    $accTotal = round(collect($items)->sum(fn($row) => (float)($row['total_price'] ?? 0)), 2);
                    $grandTotal = round($basePrice + $accTotal, 2);

                    $set($prefix . 'items', $items);
                    $set($prefix . 'accessories_total', $accTotal);
                    $set($prefix . 'product_price', $basePrice);
                    $set($prefix . 'grand_total', $grandTotal);

                    return $grandTotal;
                };

                $scalePaymentsFromTotal = function (Set $set, Get $get, float $grandTotal, string $prefix = ''): void {
                    $payments = $get($prefix . 'payments') ?? [];

                    if (count($payments) === 0) {
                        return;
                    }

                    $firstKey = array_key_first($payments);

                    $otherSum = 0.0;
                    $hasCustomOthers = false;

                    foreach ($payments as $key => $payment) {
                        if ($key === $firstKey) {
                            continue;
                        }

                        $price = round((float)($payment['price'] ?? $payment['amount'] ?? 0), 2);

                        if ($price > 0) {
                            $hasCustomOthers = true;
                        }

                        $otherSum += $price;
                    }

                    $otherSum = round($otherSum, 2);

                    if ($hasCustomOthers) {
                        $remainder = max(0, round($grandTotal - $otherSum, 2));

                        foreach ($payments as $key => &$payment) {
                            $payment['price'] = $key === $firstKey
                                ? $remainder
                                : round((float)($payment['price'] ?? $payment['amount'] ?? 0), 2);

                            $payment['amount'] = (float)$payment['price'];
                        }
                        unset($payment);

                        $set($prefix . 'payments', $payments);

                        return;
                    }

                    foreach ($payments as $key => &$payment) {
                        $payment['price'] = $key === $firstKey ? round($grandTotal, 2) : 0.0;
                        $payment['amount'] = (float)$payment['price'];
                    }
                    unset($payment);

                    $set($prefix . 'payments', $payments);
                };

                $recalculateAll = function (Get $get, Set $set, ?array $itemsState = null, string $prefix = '') use (
                    $recalculateTotalsFromItems,
                    $scalePaymentsFromTotal
                ): void {
                    $items = $itemsState ?? ($get($prefix . 'items') ?? []);
                    $grandTotal = $recalculateTotalsFromItems($get, $set, $items, $prefix);

                    $scalePaymentsFromTotal($set, $get, $grandTotal, $prefix);
                };

                return [
                    Tabs::make('Guarantee Form')->tabs([
                        Tabs\Tab::make(__('admin.guarantee'))
                            ->schema([
                                Grid::make()->schema([
                                    TextInput::make('sku')
                                        ->label(__('admin.sku'))
                                        ->required()
                                        ->live(debounce: 500)
                                        ->afterStateUpdated(function (Set $set, $state, Get $get) use ($recalculateAll) {
                                            $sku = trim((string)$state);

                                            if ($sku === '') {
                                                return;
                                            }

                                            $product = Product::query()
                                                ->with([
                                                    'model:id,name',
                                                    'user:id,name,mobile,id_number,rating,comment',
                                                    'seller:id,name,mobile,id_number',
                                                    'information:id,name',
                                                ])
                                                ->select([
                                                    'id',
                                                    'sku',
                                                    'order_id',
                                                    'model_id',
                                                    'sale_price',
                                                    'guarantee_id',
                                                    'delivery_id',
                                                    'hear_about_id',
                                                    'need_reset',
                                                    'status_id',
                                                ])
                                                ->where('sku', $sku)
                                                ->where('status_id', '!=', 4)
                                                ->first();

                                            if (!$product) {
                                                Notification::make()
                                                    ->title(__('admin.product_not_found_or_sold'))
                                                    ->danger()
                                                    ->send();

                                                foreach ([
                                                             'order_id',
                                                             'product_name',
                                                             'price',
                                                             'guarantee_id',
                                                             'delivery_id',
                                                             'hear_about_id',
                                                             'need_reset',
                                                             'mobile',
                                                             'name',
                                                             'is_legal_entity',
                                                             'id_number',
                                                             'repair_information_id',
                                                             'items',
                                                             'payments',
                                                             'accessories_total',
                                                             'product_price',
                                                             'grand_total',
                                                             'user_id',
                                                             'seller_id',
                                                         ] as $field) {
                                                    $set(
                                                        $field,
                                                        in_array($field, ['items', 'payments', 'repair_information_id'], true)
                                                            ? []
                                                            : ($field === 'need_reset' ? false : null)
                                                    );
                                                }

                                                $set('price', 0);
                                                $set('is_legal_entity', 3);
                                                $set('accessories_total', 0);
                                                $set('product_price', 0);
                                                $set('grand_total', 0);

                                                return;
                                            }

                                            $set('order_id', $product->order_id);
                                            $set('product_name', $product->model?->name);
                                            $set('price', (float)($product->sale_price ?? 0));
                                            $set('guarantee_id', $product->guarantee_id);
                                            $set('delivery_id', $product->delivery_id);
                                            $set('hear_about_id', $product->hear_about_id);
                                            $set('need_reset', (bool)$product->need_reset);
                                            $set('repair_information_id', $product->information->pluck('id')->toArray());

                                            $set('items', []);
                                            $set('payments', [
                                                ['payment_id' => null, 'price' => 0.00],
                                            ]);

                                            $recalculateAll($get, $set, [], '');
                                        }),

                                    TextInput::make('mobile')
                                        ->required()
                                        ->label(__('admin.mobile'))
                                        ->live(debounce: 900)
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            $mobile = preg_replace('/\s+/', '', trim((string)$state));

                                            if ($mobile === '') {
                                                return;
                                            }

                                            $set('mobile', $mobile);

                                            $user = User::query()
                                                ->with('products')
                                                ->withCount('products')
                                                ->where('mobile', $mobile)
                                                ->first();

                                            if (!$user) {
                                                $set('user_id', null);

                                                return;
                                            }

                                            $set('user_id', $user->id);
                                            $set('name', $user->name ?? $get('name'));
                                            $set('id_number', $user->id_number);
                                            $set('rating', $user->rating);
                                            $set('comment', $user->comment);
                                            $set('paid_products_count', $user->products_count);


                                            $isCompany = method_exists($user, 'hasRole')
                                                ? $user->hasRole('კომპანია')
                                                : $user->roles()->where('name', 'კომპანია')->exists();

                                            $set('is_legal_entity', $isCompany ? 2 : 3);
                                        }),

                                    TextInput::make('product_name')
                                        ->label(__('admin.model'))
                                        ->readOnly(),

                                    TextInput::make('name')
                                        ->required()
                                        ->label(__('admin.full_name')),

                                    TextInput::make('order_id')
                                        ->required()
                                        ->label(__('admin.order_id')),

                                    TextInput::make('id_number')
                                        ->label(__('admin.id_number')),

                                    PriceInput::make('price')
                                        ->required()
                                        ->label(__('admin.price'))
                                        ->default(null)
                                        ->lazy()
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) use ($recalculateAll) {
                                            $set('price', (float)($state ?: 0));
                                            $recalculateAll($get, $set, null, '');
                                        }),

                                    Select::make('is_legal_entity')
                                        ->label(__('admin.is_legal_entity'))
                                        ->required()
                                        ->default(3)
                                        ->options([2 => 'კომპანია', 3 => 'მომხმარებელი'])
                                        ->reactive()
                                        ->searchable()
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            $userId = $get('user_id');

                                            if (!$userId) {
                                                return;
                                            }

                                            $user = User::query()->find($userId);

                                            if (!$user) {
                                                return;
                                            }

                                            $roleName = ((int)$state === 2) ? 'კომპანია' : 'მომხმარებელი';
                                            $user->assignRole([$roleName]);
                                        }),

                                    Grid::make(3)->schema([
                                        Select::make('guarantee_id')
                                            ->label(__('admin.guarantee'))
                                            ->required()
                                            ->searchable()
                                            ->options(toArray(Guarantee::class)),

                                        Select::make('rating')
                                            ->label(__('admin.rating_select'))
                                            ->searchable()
                                            ->preload()
                                            ->options([
                                                1 => '⭐',
                                                2 => '⭐⭐',
                                                3 => '⭐⭐⭐',
                                                4 => '⭐⭐⭐⭐',
                                                5 => '⭐⭐⭐⭐⭐',
                                            ]),

                                        TextInput::make('paid_products_count')
                                            ->readOnly()
                                            ->default(0)
                                            ->label(__('admin.products_count')),

                                    ])->columnSpanFull(),

                                    Checkbox::make('need_reset')
                                        ->label(__('admin.need_reset')),

                                    Textarea::make('comment')
                                        ->label(__('admin.user_comment')),

                                    Select::make('repair_information_id')
                                        ->label(__('admin.repair_information'))
                                        ->multiple()
                                        ->searchable()
                                        ->options(toArray(RepairInformation::class))
                                        ->preload()
                                        ->reactive()
                                        ->dehydrated(true),

                                    Select::make('hear_about_id')
                                        ->label(__('admin.hear_about'))
                                        ->options(toArray(HearAbout::class))
                                        ->searchable()
                                        ->required(),

                                    Select::make('delivery_id')
                                        ->label(__('admin.delivery'))
                                        ->options(toArray(Delivery::class))
                                        ->searchable()
                                        ->required(),

                                    Select::make('seller_id')
                                        ->searchable()
                                        ->required()
                                        ->label(__('admin.seller'))
                                        ->options(User::query()->whereHas('roles', function ($query) {
                                            return $query->whereIn('id', [1, 5, 6, 7]);
                                        })->pluck('name', 'id'))
                                        ->default(4),
                                ]),
                            ]),

                        Tabs\Tab::make(__('admin.payment'))
                            ->schema([
                                Repeater::make('payments')
                                    ->label(__('admin.payment'))
                                    ->schema([
                                        Select::make('payment_id')
                                            ->label(__('admin.payment'))
                                            ->options(cache()->remember('payments.options', 3600, fn() => toArray(Payment::class)))
                                            ->searchable()
                                            ->required()
                                            ->reactive(),

                                        PriceInput::make('price')
                                            ->label(__('admin.price'))
                                            ->lazy()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) use ($scalePaymentsFromTotal) {
                                                $scalePaymentsFromTotal($set, $get, (float)($get('../../grand_total') ?? 0), '../../');
                                            }),
                                    ])
                                    ->defaultItems(1)
                                    ->columns(2)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) use ($scalePaymentsFromTotal) {
                                        $scalePaymentsFromTotal($set, $get, (float)($get('grand_total') ?? 0), '');
                                    })
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make(__('admin.accessory'))
                            ->schema([
                                Repeater::make('items')
                                    ->label(__('admin.accessory'))
                                    ->schema([
                                        Select::make('accessory_id')
                                            ->label(__('admin.accessory'))
                                            ->options(toArray(Accessory::class))
                                            ->required()
                                            ->reactive()
                                            ->searchable()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) use ($recalculateAll) {
                                                if ($state && ($accessory = Accessory::query()->select(['id', 'sale_price'])->find($state))) {
                                                    $set('price', (float)$accessory->sale_price);

                                                    $qty = max(1, (int)($get('quantity') ?: 1));
                                                    $set('quantity', $qty);
                                                    $set('total_price', round((float)$accessory->sale_price * $qty, 2));
                                                }

                                                $recalculateAll($get, $set, null, '../../');
                                            }),

                                        PriceInput::make('price')
                                            ->label(__('admin.price'))
                                            ->numeric()
                                            ->readOnly(fn(Get $get) => (bool)$get('is_gift'))
                                            ->lazy()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) use ($recalculateAll) {
                                                $set('price', (float)($state ?: 0));
                                                $recalculateAll($get, $set, null, '../../');
                                            }),

                                        NumericInput::make('quantity')
                                            ->label(__('admin.quantity'))
                                            ->default(1)
                                            ->minValue(1)
                                            ->lazy()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) use ($recalculateAll) {
                                                $set('quantity', max(1, (int)($state ?: 1)));
                                                $recalculateAll($get, $set, null, '../../');
                                            }),

                                        PriceInput::make('total_price')
                                            ->label(__('admin.total_price'))
                                            ->readOnly()
                                            ->numeric()
                                            ->dehydrated(false),

                                        Toggle::make('is_gift')
                                            ->default(false)
                                            ->label(__('admin.is_gift'))
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) use ($recalculateAll) {

                                                $quantity = max(1, (int)($get('quantity') ?? 1));
                                                $accessoryId = $get('accessory_id');

                                                if ($state) {
                                                    $set('price', 0);
                                                    $set('total_price', 0);
                                                } else {
                                                    if ($accessoryId) {
                                                        $accessory = Accessory::query()
                                                            ->select(['id', 'sale_price'])
                                                            ->find($accessoryId);

                                                        $price = (float)($accessory?->sale_price ?? 0);

                                                        $set('price', $price);
                                                        $set('total_price', round($price * $quantity, 2));
                                                    }
                                                }

                                                $recalculateAll($get, $set, null, '../../');
                                            }),
                                    ])
                                    ->defaultItems(0)
                                    ->columns(4)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) use ($recalculateAll) {
                                        $recalculateAll($get, $set, $state ?? [], '');
                                    })
                                    ->columnSpanFull(),
                            ]),
                    ]),

                    Section::make(__('admin.total_price'))->schema([
                        Grid::make(4)->schema([
                            PriceInput::make('accessories_total')
                                ->label(__('admin.accessories_total'))
                                ->readOnly()
                                ->dehydrated(false),

                            PriceInput::make('product_price')
                                ->label(__('admin.product_price'))
                                ->readOnly()
                                ->dehydrated(false),

                            PriceInput::make('grand_total')
                                ->label(__('admin.grand_total'))
                                ->readOnly()
                                ->dehydrated(false),

                            Select::make('lang')
                                ->label(__('admin.pdf_language'))
                                ->options([
                                    'ka' => 'ქართული',
                                    'en' => 'English',
                                    'ru' => 'Русский',
                                ])
                                ->default(app()->getLocale())
                                ->required()
                                ->native(false),
                        ]),
                    ]),
                ];
            })
            ->modalWidth('7xl')
            ->action(function (array $data, $livewire) {
                $product = Product::query()
                    ->with('model:id,name', 'information')
                    ->select([
                        'id',
                        'sku',
                        'order_id',
                        'user_id',
                        'model_id',
                        'sale_price',
                        'guarantee_id',
                        'status_id',
                        'need_reset',
                        'hear_about_id',
                        'delivery_id',
                        'created_at',
                    ])
                    ->where('status_id', '!=', 4)
                    ->where('sku', $data['sku'])
                    ->firstOrFail();

                $mobile = preg_replace('/\s+/', '', trim((string)($data['mobile'] ?? '')));
                $name = trim((string)($data['name'] ?? ''));
                $rating = $data['rating'];
                $comment = $data['comment'];

                if ($mobile === '') {
                    throw new RuntimeException('Mobile is required.');
                }

                $name = $name !== '' ? $name : $mobile;

                $basePrice = (float)($data['price'] ?? 0);
                $items = $data['items'] ?? [];
                $payments = $data['payments'] ?? [];

                $role = $data['is_legal_entity'];


                $result = DB::transaction(function () use (
                    $product,
                    $data,
                    $mobile,
                    $name,
                    $basePrice,
                    $items,
                    $payments,
                    $rating,
                    $comment,
                    $role,
                ) {
                    $userId = $data['user_id'] ?? null;
                    $sellerId = $data['seller_id'] ?? null;
                    $id = $data['id_number'] ?? null;

                    $user = $userId
                        ? User::query()->find($userId)
                        : User::query()->where('mobile', $mobile)->first();

                    if (!$user) {

                        $email = "{$mobile}@myline.ge";

                        if (User::query()->where('email', $email)->exists()) {
                            $email = "{$mobile}+" . now()->timestamp . "@myline.ge";
                        }

                        $user = User::query()->create([
                            'name' => $name,
                            'email' => $email,
                            'mobile' => $mobile,
                            'rating' => $rating,
                            'comment' => $comment,
                            'password' => generateSecurePassword(),
                            'id_number' => $id ?? null,
                        ]);

                    } else {
                        $user->update([
                            'name' => $name ?: $user->name,
                            'mobile' => $mobile,
                            'rating' => $rating,
                            'comment' => $comment,
                            'id_number' => $data['id_number'] ?? $user->id_number,
                        ]);
                    }

                    $userId = $user->id;

                    $roleName = ((int)$role === 2) ? 'კომპანია' : 'მომხმარებელი';
                    $user->assignRole([$roleName]);

                    $normalizedItems = collect($items)
                        ->filter(fn($item) => !empty($item['accessory_id']))
                        ->map(function ($item) use ($data) {

                            $isGift = (bool)($item['is_gift'] ?? false);

                            $qty = max(1, (int)($item['quantity'] ?? 1));

                            $price = $isGift
                                ? 0
                                : (float)($item['price'] ?? 0);

                            return [
                                'accessory_id' => $item['accessory_id'],
                                'accessory_order_id' => $data['order_id'],
                                'price' => $price,
                                'quantity' => $qty,
                                'total_price' => $isGift
                                    ? 0
                                    : round($price * $qty, 2),
                                'is_gift' => $isGift,
                            ];
                        })
                        ->values();

                    $accTotal = round($normalizedItems->sum('total_price'), 2);
                    $grandTotal = round($basePrice + $accTotal, 2);

                    if (count($payments) === 0) {
                        $payments = [['payment_id' => null, 'price' => $grandTotal]];
                    }

                    $firstKey = array_key_first($payments);
                    $otherSum = 0.0;

                    foreach ($payments as $key => $payment) {
                        if ($key === $firstKey) continue;

                        $otherSum += round((float)($payment['price'] ?? 0), 2);
                    }

                    $payments[$firstKey]['price'] = max(0, round($grandTotal - $otherSum, 2));

                    $normalizedPayments = collect($payments)
                        ->filter(fn($payment) => !empty($payment['payment_id']))
                        ->map(fn($payment) => [
                            'payment_id' => $payment['payment_id'],
                            'order_id' => $data['order_id'],
                            'amount' => (float)($payment['price'] ?? 0),
                            'price' => (float)($payment['price'] ?? 0),
                        ])
                        ->values();

                    $accessoryOrder = AccessoryOrders::query()->updateOrCreate(
                        ['order_id' => $data['order_id']],
                        [
                            'mobile' => $mobile,
                            'buyer_id' => $userId,
                            'seller_id' => $sellerId,
                            'product_id' => $product->id,
                            'delivery_id' => $data['delivery_id'] ?? null,
                        ]
                    );

                    $accessoryOrder->items()->delete();
                    if ($normalizedItems->isNotEmpty()) {
                        $accessoryOrder->items()->createMany($normalizedItems->all());
                    }

                    $accessoryOrder->payments()->delete();
                    if ($normalizedPayments->isNotEmpty()) {
                        $accessoryOrder->payments()->createMany(
                            $normalizedPayments->map(fn($payment) => [
                                'payment_id' => $payment['payment_id'],
                                'order_id' => $payment['order_id'],
                                'amount' => $payment['amount'],
                            ])->all()
                        );
                    }

                    $product->payments()->delete();
                    if ($normalizedPayments->isNotEmpty()) {
                        $product->payments()->createMany(
                            $normalizedPayments->map(fn($payment) => [
                                'payment_id' => $payment['payment_id'],
                                'order_id' => $payment['order_id'],
                                'price' => $payment['price'],
                            ])->all()
                        );
                    }

                    $product->update([
                        'order_id' => $data['order_id'],
                        'user_id' => $userId,
                        'sale_price' => $basePrice,
                        'guarantee_id' => $data['guarantee_id'] ?? null,
                        'status_id' => 4,
                        'seller_id' => $data['seller_id'],
                        'need_reset' => (bool)($data['need_reset'] ?? false),
                        'hear_about_id' => $data['hear_about_id'] ?? null,
                        'delivery_id' => $data['delivery_id'] ?? null,
                        'created_at' => now(),
                    ]);

                    $repairInformationIds = collect($data['repair_information_id'] ?? [])
                        ->filter()
                        ->map(fn($id) => (int)$id)
                        ->unique()
                        ->values()
                        ->all();

                    $product->information()->sync($repairInformationIds);

                    return [
                        'product_id' => $product->id,
                        'grand_total' => $grandTotal,
                    ];
                });

                app(ProductPaymentCashDrawerService::class)
                    ->syncProductPayments($result['product_id']);


                GenerateGuaranteePdfAndSendMailJob::dispatch($result['product_id']);

                $livewire->js("window.open('" . route('pdf.guarantee', [$result['product_id'], 'lang' => $data['lang']]) . "', '_blank')");

                Notification::make()
                    ->title(__('admin.guarantee_created_successfully'))
                    ->success()
                    ->send();
            });
    }

    /**
     * All actions in one place
     */
    protected static function productHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('admin.product_add')),
            static::createGuarantee(),
            static::exportProducts(),
        ];
    }
}
