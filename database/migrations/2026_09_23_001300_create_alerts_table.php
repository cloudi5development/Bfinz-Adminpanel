<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['gold', 'silver', 'forex', 'fuel', 'fd', 'rd']);
            $table->string('asset', 20); // e.g. '24k', 'USD', 'petrol', 'bank:12:12m'
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('condition', ['above', 'below', 'any_change']);
            $table->decimal('target_value', 18, 4)->nullable();
            $table->boolean('is_active')->default(true);
            // Crossing state machine for above/below: armed=true means the
            // alert is waiting to fire; it fires once when the value crosses
            // to the trigger side, then stays disarmed until it crosses back.
            $table->boolean('armed')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
