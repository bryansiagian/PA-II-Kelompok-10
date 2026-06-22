<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->enum('tier', ['same_village', 'same_district', 'same_regency', 'other_regency']);
            $table->string('regency_id')->nullable();   // diisi hanya kalau tier = other_regency
            $table->string('regency_name')->nullable(); // nama kabupaten untuk display
            $table->unsignedBigInteger('rate')->default(0);
            $table->timestamps();

            $table->unique(['tier', 'regency_id']); // satu tarif per tier+kabupaten
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
