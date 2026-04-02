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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Menggunakan UUID sesuai DBML (Varchar)
            $table->string('product_code')->nullable();
            $table->foreignId('product_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('image')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('unit');
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->integer('active')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
