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
        Schema::create('product_size_items', function (Blueprint $table) {
            $table->id();
            $table->string('size');
            $table->string('status');
            $table->integer('position');
            $table->timestamp('date_time')->nullable(); // Use timestamp or datetime for date and time
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_size_items');
    }
};
