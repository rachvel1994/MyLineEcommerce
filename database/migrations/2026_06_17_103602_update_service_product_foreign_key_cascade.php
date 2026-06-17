<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop old foreign key
        DB::statement("
            ALTER TABLE service_product
            DROP FOREIGN KEY service_product_product_id_foreign
        ");

        // 2. Add new foreign key with CASCADE
        DB::statement("
            ALTER TABLE service_product
            ADD CONSTRAINT service_product_product_id_foreign
            FOREIGN KEY (product_id)
            REFERENCES products(id)
            ON DELETE CASCADE
        ");
    }

    public function down(): void
    {
        // rollback: remove cascade version
        DB::statement("
            ALTER TABLE service_product
            DROP FOREIGN KEY service_product_product_id_foreign
        ");

        DB::statement("
            ALTER TABLE service_product
            ADD CONSTRAINT service_product_product_id_foreign
            FOREIGN KEY (product_id)
            REFERENCES products(id)
        ");
    }
};