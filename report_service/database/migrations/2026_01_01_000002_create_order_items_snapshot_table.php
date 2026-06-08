<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items_snapshot', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id'); // FK logis ke orders_snapshot.id
            $table->string('product_name');
            $table->string('product_id')->nullable(); // UUID produk di service utama
            $table->integer('quantity');
            $table->decimal('price_at_order', 15, 2)->default(0);
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items_snapshot');
    }
};
