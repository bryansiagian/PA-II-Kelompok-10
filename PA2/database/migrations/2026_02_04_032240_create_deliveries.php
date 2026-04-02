<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('product_order_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('courier_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('tracking_number')->unique();
            $table->foreignId('delivery_status_id')->constrained('delivery_status');
            $table->string('image')->nullable(); // Proof image
            $table->string('receiver_name')->nullable();
            $table->string('receiver_relation')->nullable();
            $table->text('delivery_note')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};