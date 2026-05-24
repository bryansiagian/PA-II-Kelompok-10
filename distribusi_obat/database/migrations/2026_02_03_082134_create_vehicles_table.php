<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['motorcycle', 'car']);
            $table->string('subtype'); // bebek, sport, matic, sedan, van, pickup
            $table->string('brand');   // Honda, Yamaha, Daihatsu, dll
            $table->string('plate_number')->unique();
            $table->string('color');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
