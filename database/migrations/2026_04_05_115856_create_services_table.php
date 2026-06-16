<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('technic_id')
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

            $table->index(['technic_id', 'created_by', 'is_paid']);
        });

        Schema::create('service_product', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->unsignedInteger('qty')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['service_id', 'product_id']);
            $table->index(['product_id']);
        });

        Schema::create('service_repair_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('old_status_id')
                ->nullable()
                ->constrained('statuses')
                ->nullOnDelete();

            $table->foreignId('new_status_id')
                ->nullable()
                ->constrained('statuses')
                ->nullOnDelete();

            $table->decimal('repair_price', 12, 2)->default(0);
            $table->decimal('price_delta', 12, 2)->default(0);
            $table->text('comment')->nullable();
            $table->boolean('is_paid')->default(false)->index();

            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['new_status_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_repair_histories');
        Schema::dropIfExists('service_product');
        Schema::dropIfExists('services');
    }
};
