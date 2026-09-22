<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * The admin account used to sign in to the panel.
 *
 * Kept in a seeder rather than a migration so the password can be re-applied at
 * any time with `php artisan db:seed --class=AdminUserSeeder`. The plain value
 * below is only ever written through the model, whose "hashed" cast bcrypts it
 * on the way into the database — the users table never stores it in the clear.
 *
 * Change this password before the panel goes anywhere near production.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'      => 'Admin',
                'password'  => '12345678',   // hashed by the model cast
                'is_active' => true,
            ]
        );

        // is_super_admin is not mass assignable on purpose (nothing posted from a
        // form may promote an account), so the owner flag is set explicitly here.
        if (! $admin->is_super_admin) {
            $admin->forceFill(['is_super_admin' => true])->save();
        }
    }
}
