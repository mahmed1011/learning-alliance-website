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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_number', '4')->unique();   // e.g. LA-20250811-ABC123
            $table->string('cart_key')->index();        // guest identity from cookie

            // checkout fields
            $table->string('campus');
            $table->string('parent_name');
            $table->string('student_name');
            $table->string('class')->nullable();
            $table->string('section')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // money
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);

            // statuses
            $table->enum('status', ['pending', 'confirmed', 'processing','completed', 'cancelled'])
                ->default('pending')->index();

            $table->enum('payment_status', ['unpaid', 'paid', 'refunded', 'failed'])
                ->default('unpaid')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
