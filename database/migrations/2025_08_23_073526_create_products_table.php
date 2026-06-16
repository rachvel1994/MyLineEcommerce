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
        Schema::create('products', function (Blueprint $table) {

            $table->id();
            $table->string('sku')->unique();
            $table->string('order_id')->nullable()->index();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('retail_price', 12, 2)->nullable();
            $table->decimal('repair_price', 12, 2)->nullable();
            $table->text('comment')->nullable();
            $table->text('service_comment')->nullable();
            $table->json('images')->nullable();
            $table->tinyInteger('company_id')->default(1);
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('seller_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('condition_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('model_id')
                ->nullable()
                ->constrained('product_models')
                ->nullOnDelete();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('status_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('hear_about_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('delivery_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('guarantee_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('battery_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('color_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('storage_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->boolean('is_repaired')->default(false)->index();
            $table->boolean('is_consigned')->default(false)->index();
            $table->boolean('need_reset')->default(false);
            $table->boolean('show_repair_information')->default(false);
            $table->timestamps();

            $table->index(['model_id', 'sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
