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
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('size_id');
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0); // Default stock to 0
            $table->timestamps();

            // Foreign key constraint to the `products` table
            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('cascade');

            // Foreign key constraint to the `product_size_items` table
            $table->foreign('size_id')
                ->references('id')->on('product_size_items') // Note the table name pluralized here
                ->onDelete('cascade');  // Adjust onDelete behavior as needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sizes');
    }
};
