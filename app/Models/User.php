<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\AdminModules;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * `is_super_admin` is deliberately absent: the account that owns the panel is
     * chosen by migration only, so no form post can promote itself.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'modules',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_super_admin' => 'boolean',
            'modules' => 'array',
        ];
    }

    /**
     * May this account open the given admin module?
     *
     * The main admin holds everything by definition. Everyone else is limited to
     * the keys ticked on their user form, and can never reach the modules
     * reserved for the owner of the panel (see AdminModules::SUPER_ADMIN_ONLY).
     */
    public function canAccessModule(string $module): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        if (AdminModules::isSuperAdminOnly($module)) {
            return false;
        }

        return in_array($module, $this->modules ?? [], true);
    }
}
