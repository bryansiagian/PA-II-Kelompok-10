<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders_snapshot', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('status_name')->default('Pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_method')->default('snap');
            $table->decimal('total', 15, 2)->default(0);
            $table->string('regency')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps(); // created_at = waktu order dibuat di service utama
            $table->timestamp('synced_at')->nullable(); // kapan sync terakhir
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders_snapshot');
    }
};
