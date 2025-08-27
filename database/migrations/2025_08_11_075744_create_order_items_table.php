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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // product snapshot + relations
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('size_id')
                ->constrained('product_size_items')
                ->cascadeOnDelete();

            $table->foreignId('product_size_id')
                ->constrained('product_sizes')
                ->cascadeOnDelete();

            $table->string('product_name'); // snapshot (so name change later se effect na ho)
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
