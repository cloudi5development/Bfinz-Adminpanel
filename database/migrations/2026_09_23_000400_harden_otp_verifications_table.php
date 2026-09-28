<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OTPs were stored in plain text (`otp` column) — spec requires them hashed
 * at rest. Renamed to `otp_hash` so the column name itself signals the
 * contract. Also logs the requesting IP, per spec's otp_requests model.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otp_verifications', function (Blueprint $table) {
            $table->renameColumn('otp', 'otp_hash');
            $table->string('ip', 45)->nullable()->after('otp_hash');
        });
    }

    public function down(): void
    {
        Schema::table('otp_verifications', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->renameColumn('otp_hash', 'otp');
        });
    }
};
