<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Mobile OTP accounts have no email/password at signup, so the admin-only
 * assumption that every user row has both no longer holds.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `users` CHANGE `email` `email` VARCHAR(191) NULL');
        DB::statement('ALTER TABLE `users` CHANGE `password` `password` VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `users` CHANGE `email` `email` VARCHAR(191) NOT NULL');
        DB::statement('ALTER TABLE `users` CHANGE `password` `password` VARCHAR(255) NOT NULL');
    }
};
