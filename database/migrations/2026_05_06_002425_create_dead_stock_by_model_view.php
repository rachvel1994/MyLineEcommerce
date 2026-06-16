<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("DROP VIEW IF EXISTS dead_stock_by_model");

        DB::statement("
            CREATE VIEW dead_stock_by_model AS
                SELECT
                     p.model_id,

                    MAX(
                        CASE
                            WHEN p.status_id = 4
                            THEN p.created_at
                        END
                    ) AS last_sold_at,

                    CASE
                        WHEN MAX(
                            CASE
                                WHEN p.status_id = 4
                                THEN p.created_at
                            END
                        ) < NOW() - INTERVAL 10 DAY
                        THEN 1
                        ELSE 0
                    END AS dead_stock,

                    SUM(
                        CASE
                            WHEN p.status_id IN (1,2)
                            THEN 1 ELSE 0
                        END
                    ) AS real_stock

                FROM products p
                WHERE p.model_id IS NOT NULL
                GROUP BY p.model_id

            HAVING real_stock > 0;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS dead_stock_by_model");
    }
};
