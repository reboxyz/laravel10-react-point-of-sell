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
        // Note! Pivot Table
        Schema::create('product_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');

            /*
            $table->foreignId('order_id')->references('id')->on('orders')->onDelete('no action');;
            $table->foreignId('product_id')->references('id')->on('products')->onDelete('no action');;
            */

            $table->string('product_name');
            $table->double('product_price', 8, 2);
            $table->unsignedInteger('product_quantity');
            $table->float('product_discount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_orders');
    }
};
