<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metal_rates', function (Blueprint $table) {
            $table->id();
            $table->enum('metal', ['gold', 'silver']);
            $table->enum('purity', ['24k', '22k', '18k', '999']);
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('rate_per_gram_paise');
            $table->bigInteger('change_paise')->default(0);
            $table->decimal('change_pct', 6, 3)->default(0);
            $table->date('rate_date');
            $table->timestamp('fetched_at');
            $table->string('source', 60);
            $table->timestamps();

            // One row per (metal, purity, city, day) — a sync re-run for the
            // same day upserts this row rather than appending a new one, which
            // is what makes SyncMetalRates idempotent. Trend history therefore
            // has daily, not intraday, granularity (matches the 7d/1m/6m/1y
            // ranges the app actually asks for).
            $table->unique(['metal', 'purity', 'city_id', 'rate_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metal_rates');
    }
};
