<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DROP VIEW IF EXISTS payment_monthly_report");

        DB::statement("
            CREATE VIEW payment_monthly_report AS
            SELECT
                payments.id AS payment_id,
                payments.name AS payment_name,

                SUM(
                    CASE
                        WHEN product_payments.created_at >= CURDATE()
                            AND product_payments.created_at < CURDATE() + INTERVAL 1 DAY
                        THEN product_payments.price ELSE 0
                    END
                ) AS today_total,

                SUM(
                    CASE
                        WHEN product_payments.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
                        THEN product_payments.price ELSE 0
                    END
                ) AS month_total

            FROM product_payments
            INNER JOIN payments ON payments.id = product_payments.payment_id
            INNER JOIN products ON products.id = product_payments.product_id

            WHERE products.status_id = 4

            GROUP BY payments.id, payments.name
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS payment_monthly_report");
    }
};
