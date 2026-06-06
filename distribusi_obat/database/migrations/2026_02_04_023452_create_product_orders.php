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
            $table->string('regency')->nullable(); // Kabupaten/Kota
            $table->string('district')->nullable(); // Kecamatan
            $table->string('village')->nullable(); // Kelurahan/Desa
            $table->text('shipping_address')->nullable(); // Detail alamat (Jalan/No)
            $table->string('phone_order')->nullable();
            $table->enum('required_vehicle', ['motorcycle', 'car'])->default('motorcycle');
            $table->text('notes')->nullable();
            $table->date('estimated_delivery_start')->nullable();
            $table->date('estimated_delivery_end')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded', 'cash'])->default('unpaid');
            $table->enum('payment_method', ['snap', 'cash'])->default('snap');
            $table->string('payment_token')->nullable();
            $table->string('payment_ref')->nullable();
            $table->timestamp('paid_at')->nullable();
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
