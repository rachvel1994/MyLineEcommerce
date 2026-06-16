<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, array<string, list<string>>>
     */
    private const INDEXES = [
        'products' => [
            'products_created_at_index' => ['created_at'],
            'products_status_created_index' => ['status_id', 'created_at'],
            'products_model_status_created_index' => ['model_id', 'status_id', 'created_at'],
        ],
        'product_payments' => [
            'product_payments_payment_created_index' => ['payment_id', 'created_at'],
        ],
        'consignment_price_changes' => [
            'consignment_price_changes_payment_created_index' => ['payment_id', 'created_at'],
        ],
        'cash_drawers' => [
            'cash_drawers_closed_id_index' => ['closed_at', 'id'],
        ],
        'cash_movements' => [
            'cash_movements_drawer_created_index' => ['cash_drawer_id', 'created_at'],
            'cash_movements_cash_sync_index' => [
                'direction',
                'payment_id',
                'product_id',
                'accessory_order_payment_id',
            ],
        ],
        'expenses' => [
            'expenses_created_at_index' => ['created_at'],
        ],
        'service_repair_histories' => [
            'service_repair_histories_created_at_index' => ['created_at'],
        ],
        'accessory_orders' => [
            'accessory_orders_seller_created_index' => ['seller_id', 'created_at'],
        ],
        'users' => [
            'users_mobile_index' => ['mobile'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $tableName => $indexes) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $missingIndexes = array_filter(
                $indexes,
                static fn (array $columns, string $indexName): bool => ! Schema::hasIndex($tableName, $indexName),
                ARRAY_FILTER_USE_BOTH
            );

            if ($missingIndexes === []) {
                continue;
            }

            Schema::table($tableName, static function (Blueprint $table) use ($missingIndexes): void {
                foreach ($missingIndexes as $indexName => $columns) {
                    $table->index($columns, $indexName);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::INDEXES, true) as $tableName => $indexes) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $existingIndexes = array_filter(
                $indexes,
                static fn (array $columns, string $indexName): bool => Schema::hasIndex($tableName, $indexName),
                ARRAY_FILTER_USE_BOTH
            );

            if ($existingIndexes === []) {
                continue;
            }

            Schema::table($tableName, static function (Blueprint $table) use ($existingIndexes): void {
                foreach (array_keys($existingIndexes) as $indexName) {
                    $table->dropIndex($indexName);
                }
            });
        }
    }
};
