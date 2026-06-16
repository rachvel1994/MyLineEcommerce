<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("DROP VIEW IF EXISTS low_stock_by_model");

        DB::statement("
            CREATE SQL SECURITY INVOKER VIEW low_stock_by_model AS
            SELECT
                products.model_id AS model_id,
                COUNT(products.id) AS total
            FROM products
            WHERE products.status_id != 4
              AND products.model_id IS NOT NULL
            GROUP BY products.model_id
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS low_stock_by_model");
    }
};
