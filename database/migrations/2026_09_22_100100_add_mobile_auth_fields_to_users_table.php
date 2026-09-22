<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile', 15)->nullable()->unique()->after('email');
            $table->dateTime('mobile_verified_at')->nullable()->after('mobile');
            $table->boolean('is_mobile_verified')->default(false)->after('mobile_verified_at');
            $table->dateTime('last_active_at')->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['mobile', 'mobile_verified_at', 'is_mobile_verified', 'last_active_at']);
        });
    }
};
