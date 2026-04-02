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
        Schema::create('product_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_order_status_id')->nullable()->constrained('product_order_status');
            $table->foreignId('product_order_type_id')->nullable(); // Rutin, Urgent
            $table->foreignId('product_order_delivery_id')->nullable(); // Delivery, Self Pickup
            $table->decimal('product_order_delivery_cost', 15, 2)->default(0);
            $table->decimal('product_order_discount', 15, 2)->default(0);
            $table->enum('required_vehicle', ['motorcycle', 'car'])->default('motorcycle');
            $table->text('notes')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_requests');
    }
};
