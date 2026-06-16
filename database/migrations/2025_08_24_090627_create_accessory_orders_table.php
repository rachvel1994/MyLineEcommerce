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
        Schema::create('accessory_orders', function (Blueprint $table) {
            $table->id();
            $table->string('mobile')->nullable()->index();
            $table->string('order_id')->unique();
            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('delivery_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('buyer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('seller_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->index(['product_id', 'delivery_id', 'buyer_id', 'seller_id']);
        });

        Schema::create('accessory_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accessory_order_id')
                ->constrained('accessory_orders')
                ->cascadeOnDelete();
            $table->foreignId('accessory_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('total_price', 12, 2)->nullable();
            $table->boolean('is_gift')->default(false);
            $table->timestamps();
            $table->index(['accessory_order_id', 'accessory_id']);
        });

        Schema::create('accessory_order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accessory_order_id')
                ->nullable()
                ->constrained('accessory_orders')
                ->cascadeOnDelete();
            $table->foreignId('payment_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('order_id')->index();
            $table->decimal('amount', 12, 2)->nullable();
            $table->timestamps();
            $table->index(['accessory_order_id', 'payment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessory_order_payments');
        Schema::dropIfExists('accessory_order_items');
        Schema::dropIfExists('accessory_orders');
    }
};
