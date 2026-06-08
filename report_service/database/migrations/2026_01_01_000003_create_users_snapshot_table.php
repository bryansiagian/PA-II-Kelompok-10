<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_snapshot', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // ID sama dengan service utama
            $table->string('name');
            $table->string('email')->unique();
            $table->tinyInteger('status')->default(0); // 0 = pending, 1 = active
            $table->integer('active')->default(1);
            $table->string('regency')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_snapshot');
    }
};
