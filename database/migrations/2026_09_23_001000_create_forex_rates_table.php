<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forex_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base', 3);
            $table->string('quote', 3)->default('INR');
            $table->decimal('rate', 18, 6);
            $table->decimal('change', 18, 6)->default(0);
            $table->decimal('change_pct', 6, 3)->default(0);
            $table->date('rate_date');
            $table->timestamp('fetched_at');
            $table->string('source', 60);
            $table->timestamps();

            // Daily granularity, same idempotency reasoning as metal_rates.
            $table->unique(['base', 'quote', 'rate_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forex_rates');
    }
};
