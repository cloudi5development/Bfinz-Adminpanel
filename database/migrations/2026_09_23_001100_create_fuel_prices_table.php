<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_prices', function (Blueprint $table) {
            $table->id();
            $table->enum('fuel', ['petrol', 'diesel', 'cng']);
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('price_paise');
            $table->integer('change_paise')->default(0);
            $table->date('price_date');
            $table->string('source', 60)->nullable();
            $table->timestamps();

            $table->unique(['fuel', 'city_id', 'price_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_prices');
    }
};
