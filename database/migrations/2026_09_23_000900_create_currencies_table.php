<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('name');
            $table->string('country')->nullable();
            $table->string('flag', 10)->nullable();
            $table->enum('group', ['popular', 'asia', 'middle_east', 'europe', 'americas', 'other']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
