<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products_snapshot', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID sama dengan service utama
            $table->string('product_code')->nullable();
            $table->string('name');
            $table->string('category_name')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->string('unit')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->integer('active')->default(1);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products_snapshot');
    }
};
