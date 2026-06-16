<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('advance_payment', 12, 2)->default(0);
            $table->decimal('debt', 12, 2)->default(0);
            $table->boolean('is_paid')->default(false)->index();
            $table->timestamps();
            $table->index(['customer_id', 'created_by', 'is_paid']);
        });

        Schema::create('consignment_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignment_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['consignment_id', 'product_id']);
            $table->index(['product_id']);
        });

        Schema::create('consignment_accessory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignment_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('accessory_id')
                ->constrained()
                ->restrictOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['consignment_id', 'accessory_id']);
            $table->index(['accessory_id']);
        });

        Schema::create('consignment_price_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignment_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('payment_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('debt', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
            $table->index(['consignment_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_price_changes');
        Schema::dropIfExists('consignment_accessory');
        Schema::dropIfExists('consignment_product');
        Schema::dropIfExists('consignments');
    }
};
