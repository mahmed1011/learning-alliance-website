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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            $table->string('session_id')->index(); // guest cart identity

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete(); // if product deleted, remove cart rows

            $table->foreignId('size_id')
                ->constrained('product_size_items')
                ->restrictOnDelete(); // size master delete ko rokna safer

            $table->foreignId('product_size_id')
                ->constrained('product_sizes')
                ->restrictOnDelete(); // variant row must exist

            $table->unsignedInteger('quantity');

            // optional cache; you can recompute at runtime too
            $table->decimal('subtotal', 12, 2);

            $table->timestamps();

            // per session, same product+size duplicate na bane
            $table->unique(['session_id', 'product_id', 'size_id'], 'cart_session_product_size_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
