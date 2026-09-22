<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 15)->index();
            $table->string('otp');
            // Explicit DATETIME (not the first TIMESTAMP column) so MySQL/MariaDB
            // never attaches an implicit "ON UPDATE current_timestamp()" clause,
            // which would silently reset this on every attempts increment.
            $table->dateTime('expires_at');
            $table->tinyInteger('attempts')->default(0);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
