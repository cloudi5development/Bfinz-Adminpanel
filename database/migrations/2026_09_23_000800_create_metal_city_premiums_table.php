<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metal_city_premiums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->enum('metal', ['gold', 'silver']);
            $table->integer('premium_paise')->default(0);
            $table->timestamps();

            $table->unique(['city_id', 'metal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metal_city_premiums');
    }
};
